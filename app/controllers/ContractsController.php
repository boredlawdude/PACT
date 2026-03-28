<?php
declare(strict_types=1);

require_once APP_ROOT . '/app/models/Contract.php';


class ContractsController
{
    private function getCounterpartyPrimaryContacts(): array {
        $stmt = $this->db->query("SELECT person_id, first_name, last_name FROM people WHERE (is_town_employee IS NULL OR is_town_employee = 0)  ORDER BY last_name, first_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function generateWordDocument(int $contractId): void
    {
        $this->generateDocument($contractId, 'docx');
    }

    public function generateHtmlDocument(int $contractId): void
    {
        $this->generateDocument($contractId, 'html');
    }

    private function generateDocument(int $contractId, string $format): void
    {
        // Placeholder: implement your document generation logic here
        // Example: fetch contract, merge with template, save file, etc.
        // For now, just output a message for debugging
        echo "Generating $format document for contract #$contractId";
        exit;

    }

    private Person $people;
    private PDO $db;
    private Contract $contracts;

    public function __construct()
    {
        $this->db = db();
        $this->contracts = new Contract($this->db);
        require_once APP_ROOT . '/app/models/Person.php';
        $this->people = new Person($this->db);
    }

    private function getDepartments(): array
    {
        return $this->people->allDepartments();
    }

    public function index(): void
    {
        $contracts = $this->contracts->search([]);
        $departments = $this->getDepartments();
        $responsiblePeople = $this->getResponsiblePeople();
        require APP_ROOT . '/app/views/contracts/index.php';
    }

    public function search(): void{
    

        $filters = [
            'q' => $_GET['q'] ?? null,
            'status' => $_GET['status'] ?? null,
            'department_id' => $_GET['department_id'] ?? null,
            'owner_primary_contact_id' => $_GET['owner_primary_contact_id'] ?? null,
            'end_date_from' => $_GET['end_date_from'] ?? null,
            'end_date_to' => $_GET['end_date_to'] ?? null,
            'company_id' => isset($_GET['company_id']) ? (int)$_GET['company_id'] : null,
        ];
        $contracts = $this->contracts->search($filters);
        $departments = $this->getDepartments();
        $responsiblePeople = $this->getResponsiblePeople();
        require APP_ROOT . '/app/views/contracts/index.php';
    }

    public function show(): void
    {
        $id = (int)($_GET['contract_id'] ?? 0);
        $stmt = $this->db->prepare("SELECT c.* FROM contracts c WHERE c.contract_id = :id");
        $stmt->execute(['id' => $id]);
        $contract = $stmt->fetch(PDO::FETCH_ASSOC);
        $docsStmt = $this->db->prepare("SELECT * FROM contract_documents WHERE contract_id = :id ORDER BY created_at DESC, contract_document_id DESC");
        $docsStmt->execute(['id' => $id]);
        $documents = $docsStmt->fetchAll(PDO::FETCH_ASSOC);
        require APP_ROOT . '/app/views/contracts/show.php';
    }

    public function create(): void
    {
        $mode = 'create';
        $flashErrors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);

        $contract = $_SESSION['old_contract_form'] ?? [
            'status' => 'draft',
            'currency' => 'USD',
            'governing_law' => 'North Carolina',
            'owner_company_id' => 3,
            'auto_renew' => 0,
        ];
        unset($_SESSION['old_contract_form']);

        $departments = $this->getDepartments();
        $companies = $this->getCompanies();
        $types = $this->getContractTypes();

        $ownerPeople = [];
        if (!empty($contract['owner_company_id'])) {
            $ownerPeople = $this->getPeopleByCompany((int)$contract['owner_company_id']);
        }

        $counterpartyPeople = $this->getCounterpartyPrimaryContacts();

        require APP_ROOT . '/app/views/contracts/edit.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        $data = $this->collectFormData($_POST);
        $errors = $this->validate($data);

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['old_contract_form'] = $data;
            header('Location: /index.php?page=contracts_create');
            exit;
        }

        try {
            $contractId = $this->contracts->create($data);
        } catch (Throwable $e) {
            $_SESSION['flash_errors'] = ['Unable to create contract: ' . $e->getMessage()];
            $_SESSION['old_contract_form'] = $data;
            header('Location: /index.php?page=contracts_create');
            exit;
        }

        unset($_SESSION['old_contract_form']);
        header('Location: /index.php?page=contracts_show&contract_id=' . $contractId);
        exit;
    }

    public function edit(int $contractId): void
    {
        $contract = $this->contracts->find($contractId);
        if (!$contract) {
            http_response_code(404);
            echo 'Contract not found.';
            return;
        }
        $mode = 'edit';
        $flashErrors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);
        $old = $_SESSION['old_contract_form'] ?? null;
        unset($_SESSION['old_contract_form']);
        if (is_array($old) && $old) {
            $contract = array_merge($contract, $old);
        }
        $departments = $this->getDepartments();
        $companies = $this->getCompanies();
        $types = $this->getContractTypes();
        $ownerPeople = [];
        if (!empty($contract['owner_company_id'])) {
            $ownerPeople = $this->getPeopleByCompany((int)$contract['owner_company_id']);
        }
        $counterpartyPeople = $this->getCounterpartyPrimaryContacts();
        require APP_ROOT . '/app/views/contracts/edit.php';
    }

    public function update(int $contractId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }
        $contract = $this->contracts->find($contractId);
        if (!$contract) {
            http_response_code(404);
            echo 'Contract not found.';
            return;
        }
        $data = $this->collectFormData($_POST);
        $errors = $this->validate($data);
        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['old_contract_form'] = $data;
            header('Location: /index.php?page=contracts_edit&contract_id=' . $contractId);
            exit;
        }
        try {
            $this->contracts->update($contractId, $data);
        } catch (Throwable $e) {
            $_SESSION['flash_errors'] = ['Unable to update contract: ' . $e->getMessage()];
            $_SESSION['old_contract_form'] = $data;
            header('Location: /index.php?page=contracts_edit&contract_id=' . $contractId);
            exit;
        }
        unset($_SESSION['old_contract_form']);
        header('Location: /index.php?page=contracts_show&contract_id=' . $contractId);
        exit;
    }

    public function destroy(int $contractId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }
        $contract = $this->contracts->find($contractId);
        if (!$contract) {
            http_response_code(404);
            echo 'Contract not found.';
            return;
        }
        try {
            $this->contracts->delete($contractId);
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Unable to delete contract: ' . $e->getMessage();
            return;
        }
        header('Location: /index.php?page=contracts');
        exit;
    }

    // --- Helper stubs (implement as needed or connect to models) ---
    private function getResponsiblePeople(): array { return []; }

    private function getCompanies(): array {
        $stmt = $this->db->query("SELECT company_id, name FROM companies WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getContractTypes(): array {
        $stmt = $this->db->query("SELECT contract_type_id, contract_type FROM contract_types WHERE is_active = 1 ORDER BY contract_type ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPeopleByCompany(int $companyId): array {
        $stmt = $this->db->prepare("SELECT person_id, first_name, last_name FROM people WHERE company_id = ? AND is_active = 1 ORDER BY last_name, first_name");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function collectFormData(array $input): array { return $input; }
    private function validate(array $data): array { return []; }
}