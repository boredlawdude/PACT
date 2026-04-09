<?php
declare(strict_types=1);

require_once APP_ROOT . '/app/models/Contract.php';
require_once APP_ROOT . '/app/models/ContractStatus.php';

class DashboardController
{
    private PDO $db;
    private Contract $contractModel;
    private ContractStatus $statusModel;

    public function __construct()
    {
        $this->db = db();
        $this->contractModel = new Contract($this->db);
        $this->statusModel = new ContractStatus($this->db);
    }

    public function index(): void
    {
        // Current user info
        $person = current_person();

        // Look up the description for the user's primary role
        $roleDescription = null;
        if (!empty($person['role'])) {
            $stmt = $this->db->prepare(
                "SELECT description FROM roles WHERE role_key = ? AND is_active = 1 LIMIT 1"
            );
            $stmt->execute([$person['role']]);
            $roleDescription = $stmt->fetchColumn() ?: null;
        }

        // All statuses for radio filter
        $statuses = $this->statusModel->all();

        // All contracts (unfiltered); JS handles client-side filtering
        $contracts = $this->contractModel->search([]);

        require APP_ROOT . '/app/views/dashboard/index.php';
    }
}
