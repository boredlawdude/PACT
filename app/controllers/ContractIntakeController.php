<?php
declare(strict_types=1);
require_once APP_ROOT . '/app/models/ContractIntakeSubmission.php';

class ContractIntakeController
{
    private PDO $db;

    public function __construct()
    {
        require_login();
        $this->db = db();
    }

    /**
     * List all pending submissions (or all if ?status=all|imported|rejected)
     * GET /index.php?page=contract_intake_list
     */
    public function index(): void
    {
        if (!is_system_admin()) {
            http_response_code(403);
            exit('Access denied.');
        }

        $status = $_GET['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'imported', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $model = new ContractIntakeSubmission($this->db);

        if ($status === 'all') {
            // fetch all statuses
            $stmt = $this->db->query("
                SELECT s.*, ct.contract_type
                FROM   contract_intake_submissions s
                LEFT JOIN contract_types ct ON ct.contract_type_id = s.contract_type_id
                ORDER  BY s.created_at DESC
            ");
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $submissions = $model->findAll($status);
        }

        $pendingCount = $model->countPending();

        require APP_ROOT . '/app/views/contract_intake/index.php';
    }

    /**
     * Show single submission detail
     * GET /index.php?page=contract_intake_show&id=X
     */
    public function show(): void
    {
        if (!is_system_admin()) {
            http_response_code(403);
            exit('Access denied.');
        }

        $id    = (int)($_GET['id'] ?? 0);
        $model = new ContractIntakeSubmission($this->db);
        $submission = $model->find($id);

        if ($submission === false) {
            http_response_code(404);
            exit('Submission not found.');
        }

        require APP_ROOT . '/app/views/contract_intake/show.php';
    }

    /**
     * Redirect to contract create form pre-filled with submission data,
     * then mark the submission as imported.
     * POST /index.php?page=contract_intake_import
     */
    public function import(): void
    {
        if (!is_system_admin()) {
            http_response_code(403);
            exit('Access denied.');
        }

        $id    = (int)($_POST['submission_id'] ?? 0);
        $model = new ContractIntakeSubmission($this->db);
        $sub   = $model->find($id);

        if ($sub === false) {
            http_response_code(404);
            exit('Submission not found.');
        }

        // Map intake fields to contract form session pre-fill
        $_SESSION['old_contract_form'] = [
            'name'               => $sub['contract_name'],
            'description'        => $sub['contract_description'] ?? '',
            'contract_type_id'   => $sub['contract_type_id'],
            'total_contract_value' => $sub['estimated_value'],
            'start_date'         => $sub['start_date'],
            'end_date'           => $sub['end_date'],
            'po_number'          => $sub['po_number'],
            'account_number'     => $sub['account_number'],
            'contract_status_id' => 1,  // Draft
            'currency'           => 'USD',
            'governing_law'      => 'North Carolina',
            'owner_company_id'   => 3,
            'auto_renew'         => 0,
        ];
        // Remember which intake to mark as imported after save
        $_SESSION['intake_import_id'] = $id;

        header('Location: /index.php?page=contracts_create');
        exit;
    }

    /**
     * Reject a submission
     * POST /index.php?page=contract_intake_reject
     */
    public function reject(): void
    {
        if (!is_system_admin()) {
            http_response_code(403);
            exit('Access denied.');
        }

        $id     = (int)($_POST['submission_id'] ?? 0);
        $person = current_person();
        $model  = new ContractIntakeSubmission($this->db);
        $model->markRejected($id, (int)($person['person_id'] ?? 0));

        $_SESSION['flash_messages'] = ['Submission marked as rejected.'];
        header('Location: /index.php?page=contract_intake_list');
        exit;
    }
}
