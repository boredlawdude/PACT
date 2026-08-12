<?php
// app/models/ProcurementMethod.php
class ProcurementMethod {
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function all(): array {
        $stmt = $this->db->query("SELECT * FROM procurement_methods ORDER BY sort_order ASC, procurement_method_id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function active(): array {
        $stmt = $this->db->query("SELECT * FROM procurement_methods WHERE active = 1 ORDER BY sort_order ASC, procurement_method_id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM procurement_methods WHERE procurement_method_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(string $shortDesc, ?string $longDesc): bool {
        $stmt = $this->db->prepare("INSERT INTO procurement_methods (short_desc, long_desc) VALUES (?, ?)");
        return $stmt->execute([$shortDesc, $longDesc]);
    }

    public function update(int $id, string $shortDesc, ?string $longDesc): bool {
        $stmt = $this->db->prepare("UPDATE procurement_methods SET short_desc = ?, long_desc = ? WHERE procurement_method_id = ?");
        return $stmt->execute([$shortDesc, $longDesc, $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM procurement_methods WHERE procurement_method_id = ?");
        return $stmt->execute([$id]);
    }
}
