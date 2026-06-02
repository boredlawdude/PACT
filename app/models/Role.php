<?php
declare(strict_types=1);

class Role
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT role_id, role_key, role_name, description, is_active, approval_key FROM roles ORDER BY role_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns roles that have an approval_key set, keyed by approval_key => role_name.
     * Used to populate the "Required Approval" dropdown on the Approval Rules page.
     */
    public function allApprovalTypes(): array
    {
        $stmt = $this->db->query(
            "SELECT approval_key, role_name FROM roles
              WHERE approval_key IS NOT NULL AND approval_key != '' AND is_active = 1
              ORDER BY role_name ASC"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['approval_key']] = $row['role_name'];
        }
        return $result;
    }

    public function create(string $roleKey, string $roleName, string $description, int $isActive, string $approvalKey = ''): bool
    {
        $approvalKey = trim($approvalKey) !== '' ? strtolower(trim($approvalKey)) : null;
        $stmt = $this->db->prepare("INSERT INTO roles (role_key, role_name, description, is_active, approval_key) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$roleKey, $roleName, $description, $isActive, $approvalKey]);
    }

    public function update(int $id, string $roleKey, string $roleName, string $description, int $isActive, string $approvalKey = ''): bool
    {
        $approvalKey = trim($approvalKey) !== '' ? strtolower(trim($approvalKey)) : null;
        $stmt = $this->db->prepare("UPDATE roles SET role_key = ?, role_name = ?, description = ?, is_active = ?, approval_key = ? WHERE role_id = ?");
        return $stmt->execute([$roleKey, $roleName, $description, $isActive, $approvalKey, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM roles WHERE role_id = ?");
        return $stmt->execute([$id]);
    }

    public function isInUse(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM person_roles WHERE role_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
