<?php
// app/models/BiddingComplianceEventType.php
class BiddingComplianceEventType {
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function all(): array {
        $stmt = $this->db->query("SELECT * FROM bidding_compliance_event_types ORDER BY sort_order ASC, event_type_id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function active(): array {
        $stmt = $this->db->query("SELECT * FROM bidding_compliance_event_types WHERE active = 1 ORDER BY sort_order ASC, event_type_id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM bidding_compliance_event_types WHERE event_type_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(string $label, bool $active = true): bool {
        $stmt = $this->db->prepare("INSERT INTO bidding_compliance_event_types (label, active) VALUES (?, ?)");
        return $stmt->execute([$label, $active ? 1 : 0]);
    }

    public function update(int $id, string $label, bool $active): bool {
        $stmt = $this->db->prepare("UPDATE bidding_compliance_event_types SET label = ?, active = ? WHERE event_type_id = ?");
        return $stmt->execute([$label, $active ? 1 : 0, $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM bidding_compliance_event_types WHERE event_type_id = ?");
        return $stmt->execute([$id]);
    }
}
