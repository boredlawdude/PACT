<?php
declare(strict_types=1);
require_once APP_ROOT . '/app/models/DevelopmentAgreementSubmission.php';
require_once APP_ROOT . '/app/models/DevelopmentAgreement.php';
require_once APP_ROOT . '/app/models/DevelopmentAgreementTract.php';
require_once APP_ROOT . '/app/models/Contract.php';

class DevelopmentAgreementSubmissionsController
{
    private PDO $db;
    private DevelopmentAgreementSubmission $model;

    public function __construct()
    {
        $this->db    = db();
        $this->model = new DevelopmentAgreementSubmission($this->db);
    }

    // ------------------------------------------------------------------ index
    public function index(): void
    {
        $submissions  = $this->model->all();
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        require APP_ROOT . '/app/views/development_agreement_submissions/index.php';
    }

    // ------------------------------------------------------------------ show
    public function show(): void
    {
        $id         = (int)($_GET['submission_id'] ?? 0);
        $submission = $this->model->find($id);
        if (!$submission) {
            http_response_code(404);
            echo 'Submission not found.';
            return;
        }
        $tracts       = json_decode((string)($submission['tracts_json'] ?? '[]'), true) ?? [];
        $flashErrors  = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);
        require APP_ROOT . '/app/views/development_agreement_submissions/show.php';
    }

    // ------------------------------------------------------------------ import (POST)
    public function import(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo 'Method not allowed.'; return;
        }

        $id         = (int)($_POST['submission_id'] ?? 0);
        $submission = $this->model->find($id);
        if (!$submission) {
            http_response_code(404); echo 'Submission not found.'; return;
        }
        if ($submission['status'] !== 'pending') {
            $_SESSION['flash_errors'] = ['This submission has already been ' . $submission['status'] . '.'];
            header('Location: /index.php?page=dev_agreement_submissions_show&submission_id=' . $id);
            exit;
        }

        $reviewedBy = (int)(($_SESSION['person']['person_id'] ?? 0));

        try {
            // 1 — Auto-create linked Contract
            $contractModel = new Contract($this->db);

            $contractTypeId = $this->getDevAgrContractTypeId();
            $name = trim((string)($submission['project_name'] ?? 'Development Agreement'));

            $contractData = [
                'name'               => $name,
                'contract_type_id'   => $contractTypeId,
                'contract_status_id' => 1,
                'governing_law'      => 'North Carolina',
                'currency'           => 'USD',
                'contract_number'    => '',
            ];
            $contractData['contract_number'] = $this->generateContractNumber($name);
            $contractId = $contractModel->create($contractData);

            // 2 — Create Development Agreement
            $devModel = new DevelopmentAgreement($this->db);
            $devData  = [
                'contract_id'                        => $contractId,
                'project_name'                       => $submission['project_name']                       ?? '',
                'project_description'                => $submission['project_description']                ?? '',
                'proposed_improvements'              => $submission['proposed_improvements']              ?? '',
                'current_zoning'                     => $submission['current_zoning']                     ?? '',
                'proposed_zoning'                    => $submission['proposed_zoning']                    ?? '',
                'comp_plan_designation'              => $submission['comp_plan_designation']              ?? '',
                'anticipated_start_date'             => $submission['anticipated_start_date']             ?? '',
                'anticipated_end_date'               => $submission['anticipated_end_date']               ?? '',
                'agreement_termination_date'         => $submission['agreement_termination_date']         ?? '',
                'planning_board_date'                => $submission['planning_board_date']                ?? '',
                'town_council_hearing_date'          => $submission['town_council_hearing_date']          ?? '',
                'property_owner_name'                => $submission['property_owner_name']                ?? '',
                'developer_entity_name'              => $submission['developer_entity_name']              ?? '',
                'developer_contact_name'             => $submission['developer_contact_name']             ?? '',
                'developer_address'                  => $submission['developer_address']                  ?? '',
                'developer_phone'                    => $submission['developer_phone']                    ?? '',
                'developer_email'                    => $submission['developer_email']                    ?? '',
                'developer_state_of_incorporation'   => $submission['developer_state_of_incorporation']   ?? '',
                'developer_entity_type'              => $submission['developer_entity_type']              ?? '',
                // attorney_id left blank — admin assigns after import
                'attorney_id'                        => '',
                // Legacy single-parcel fields (unused post-tracts)
                'property_address'                   => '',
                'property_pin'                       => '',
                'property_realestateid'              => '',
                'property_acerage'                   => '',
            ];
            $devAgreementId = $devModel->create($devData);

            // 3 — Import tracts
            $tracts     = json_decode((string)($submission['tracts_json'] ?? '[]'), true) ?? [];
            $tractModel = new DevelopmentAgreementTract($this->db);
            foreach ($tracts as $i => $t) {
                $pin     = trim((string)($t['property_pin']          ?? ''));
                $address = trim((string)($t['property_address']       ?? ''));
                $reid    = trim((string)($t['property_realestateid']  ?? ''));
                if ($pin === '' && $address === '' && $reid === '') continue;
                $tractModel->create($devAgreementId, [
                    'property_pin'          => $pin,
                    'property_address'      => $address,
                    'property_realestateid' => $reid,
                    'property_acerage'      => $t['property_acerage'] ?? '',
                    'owner_name'            => $t['owner_name']        ?? '',
                    'sort_order'            => $i,
                ]);
            }

            // 4 — Mark submission as imported
            $this->model->markImported($id, $devAgreementId, $reviewedBy);

            // 5 — Log contract creation
            $this->logContractHistory($contractId, 'contract_created', null, 'Draft',
                'Imported from developer intake submission #' . $id .
                ' — submitted by ' . ($submission['submitter_name'] ?? 'unknown'));

        } catch (Throwable $e) {
            $_SESSION['flash_errors'] = ['Import failed: ' . $e->getMessage()];
            header('Location: /index.php?page=dev_agreement_submissions_show&submission_id=' . $id);
            exit;
        }

        $_SESSION['flash_success'] = 'Submission imported. Please assign the Applicant and review all details.';
        header('Location: /index.php?page=contracts_show&contract_id=' . $contractId);
        exit;
    }

    // ------------------------------------------------------------------ reject (POST)
    public function reject(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo 'Method not allowed.'; return;
        }

        $id    = (int)($_POST['submission_id'] ?? 0);
        $notes = trim((string)($_POST['review_notes'] ?? ''));
        $reviewedBy = (int)(($_SESSION['person']['person_id'] ?? 0));

        $this->model->markRejected($id, $reviewedBy, $notes ?: null);

        $_SESSION['flash_success'] = 'Submission marked as rejected.';
        header('Location: /index.php?page=dev_agreement_submissions');
        exit;
    }

    // ------------------------------------------------------------------ helpers

    private function getDevAgrContractTypeId(): int
    {
        $stmt = $this->db->prepare(
            "SELECT contract_type_id FROM contract_types WHERE contract_type = 'Development Agreement' LIMIT 1"
        );
        $stmt->execute();
        $id = $stmt->fetchColumn();
        if (!$id) {
            $ins = $this->db->prepare(
                "INSERT INTO contract_types (contract_type, is_active) VALUES ('Development Agreement', 1)"
            );
            $ins->execute();
            $id = (int)$this->db->lastInsertId();
        }
        return (int)$id;
    }

    private function generateContractNumber(string $name): string
    {
        $year  = date('y');
        $words = preg_split('/\s+/', $name);
        $first  = isset($words[0]) ? strtoupper(substr($words[0], 0, 3)) : 'DA';
        $second = isset($words[1]) ? strtoupper(substr($words[1], 0, 3)) : '';
        $stmt   = $this->db->query("SELECT MAX(contract_id) FROM contracts");
        $seq    = (int)$stmt->fetchColumn() + 1;
        $parts  = [$year, 'DA', $first . ($second ? '_' . $second : ''), $seq];
        return implode('-', $parts);
    }

    private function logContractHistory(int $contractId, string $eventType, ?string $oldStatus, ?string $newStatus, ?string $notes): void
    {
        $changedBy = isset($_SESSION['person']['person_id']) ? (int)$_SESSION['person']['person_id'] : null;
        $stmt = $this->db->prepare(
            "INSERT INTO contract_status_history (contract_id, event_type, old_status, new_status, changed_by, changed_at, notes)
             VALUES (?, ?, ?, ?, ?, NOW(), ?)"
        );
        $stmt->execute([$contractId, $eventType, $oldStatus, $newStatus, $changedBy, $notes]);
    }
}
