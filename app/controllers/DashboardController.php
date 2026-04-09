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

        // Look up all roles (with descriptions) for this user
        $userRoles = [];
        if (!empty($person['roles'])) {
            $placeholders = implode(',', array_fill(0, count($person['roles']), '?'));
            $stmt = $this->db->prepare(
                "SELECT role_key, role_name, description FROM roles
                  WHERE role_key IN ($placeholders) AND is_active = 1
                  ORDER BY role_name ASC"
            );
            $stmt->execute(array_values($person['roles']));
            $userRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // All statuses for radio filter
        $statuses = $this->statusModel->all();

        // All contracts (unfiltered); JS handles client-side filtering
        $contracts = $this->contractModel->search([]);

        require APP_ROOT . '/app/views/dashboard/index.php';
    }
}
