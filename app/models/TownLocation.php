<?php
// app/models/TownLocation.php
class TownLocation {
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function all(): array {
        $stmt = $this->db->query("SELECT * FROM town_locations ORDER BY is_active DESC, location_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO town_locations
                (location_name, address_line1, address_line2, city, state_region, postal_code, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['location_name'],
            $data['address_line1'] ?: null,
            $data['address_line2'] ?: null,
            $data['city'] ?: null,
            $data['state_region'] ?: null,
            $data['postal_code'] ?: null,
            !empty($data['is_active']) ? 1 : 0,
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE town_locations SET
                location_name = ?, address_line1 = ?, address_line2 = ?,
                city = ?, state_region = ?, postal_code = ?, is_active = ?
            WHERE location_id = ?
        ");
        return $stmt->execute([
            $data['location_name'],
            $data['address_line1'] ?: null,
            $data['address_line2'] ?: null,
            $data['city'] ?: null,
            $data['state_region'] ?: null,
            $data['postal_code'] ?: null,
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM town_locations WHERE location_id = ?");
        return $stmt->execute([$id]);
    }
}
