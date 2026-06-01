<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ContractMilestonesController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * POST: store a new milestone for a contract.
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        $contractId      = (int)($_POST['contract_id']      ?? 0);
        $milestoneTypeId = (int)($_POST['milestone_type_id'] ?? 0);
        $milestoneDate   = trim((string)($_POST['milestone_date'] ?? ''));
        $notes           = trim((string)($_POST['notes'] ?? ''));

        $errors = [];
        if ($contractId <= 0) {
            $errors[] = 'Invalid contract.';
        }
        if ($milestoneTypeId <= 0) {
            $errors[] = 'Please select a milestone type.';
        }
        if ($milestoneDate === '') {
            $errors[] = 'Milestone date is required.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $milestoneDate)) {
            $errors[] = 'Invalid date format.';
        }

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
            header('Location: /index.php?page=contracts_show&contract_id=' . $contractId . '#milestones');
            exit;
        }

        // Validate contract exists
        $stmt = $this->db->prepare("SELECT contract_id FROM contracts WHERE contract_id = ? LIMIT 1");
        $stmt->execute([$contractId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo 'Contract not found.';
            return;
        }

        // Validate milestone type exists
        $stmt = $this->db->prepare("SELECT milestone_type_id FROM contract_milestone_types WHERE milestone_type_id = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$milestoneTypeId]);
        if (!$stmt->fetch()) {
            $_SESSION['flash_errors'] = ['Invalid milestone type selected.'];
            header('Location: /index.php?page=contracts_show&contract_id=' . $contractId . '#milestones');
            exit;
        }

        $createdBy = $_SESSION['person_id'] ?? null;
        if ($createdBy !== null) {
            $createdBy = (int)$createdBy;
        }

        $stmt = $this->db->prepare("
            INSERT INTO contract_milestones (contract_id, milestone_type_id, milestone_date, notes, created_by_person_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $contractId,
            $milestoneTypeId,
            $milestoneDate,
            $notes !== '' ? $notes : null,
            $createdBy,
        ]);

        header('Location: /index.php?page=contracts_show&contract_id=' . $contractId . '#milestones');
        exit;
    }

    /**
     * POST: delete a milestone.
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        $milestoneId = (int)($_POST['milestone_id'] ?? 0);
        if ($milestoneId <= 0) {
            http_response_code(400);
            echo 'Missing milestone id.';
            return;
        }

        $stmt = $this->db->prepare("SELECT contract_id FROM contract_milestones WHERE milestone_id = ? LIMIT 1");
        $stmt->execute([$milestoneId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            http_response_code(404);
            echo 'Milestone not found.';
            return;
        }

        $contractId = (int)$row['contract_id'];

        $stmt = $this->db->prepare("DELETE FROM contract_milestones WHERE milestone_id = ?");
        $stmt->execute([$milestoneId]);

        header('Location: /index.php?page=contracts_show&contract_id=' . $contractId . '#milestones');
        exit;
    }
}
