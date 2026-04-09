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
        $stmt = $this->db->query("SELECT role_id, role_key, role_name, description, is_active FROM roles ORDER BY role_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $roleKey, string $roleName, string $description, int $isActive): bool
    {
        $stmt = $this->db->prepare("INSERT INTO roles (role_key, role_name, description, is_active) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$roleKey, $roleName, $description, $isActive]);
    }

    public function update(int $id, string $roleKey, string $roleName, string $description, int $isActive): bool
    {
        $stmt = $this->db->prepare("UPDATE roles SET role_key = ?, role_name = ?, description = ?, is_active = ? WHERE role_id = ?");
        return $stmt->execute([$roleKey, $roleName, $description, $isActive, $id]);
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
