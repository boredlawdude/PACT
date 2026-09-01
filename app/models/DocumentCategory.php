<?php
// app/models/DocumentCategory.php
declare(strict_types=1);

class DocumentCategory
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM document_categories ORDER BY sort_order ASC, category_id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function active(): array
    {
        $stmt = $this->db->query("SELECT * FROM document_categories WHERE is_active = 1 ORDER BY sort_order ASC, category_id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM document_categories WHERE category_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByKey(string $key): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM document_categories WHERE category_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(string $label): bool
    {
        $key = $this->generateUniqueKey($label);
        $maxSort = (int)$this->db->query("SELECT COALESCE(MAX(sort_order), 0) FROM document_categories")->fetchColumn();
        $stmt = $this->db->prepare(
            "INSERT INTO document_categories (category_key, label, is_system, is_active, sort_order)
             VALUES (?, ?, 0, 1, ?)"
        );
        return $stmt->execute([$key, $label, $maxSort + 10]);
    }

    public function update(int $id, string $label, bool $isActive, int $sortOrder): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE document_categories SET label = ?, is_active = ?, sort_order = ? WHERE category_id = ?"
        );
        return $stmt->execute([$label, $isActive ? 1 : 0, $sortOrder, $id]);
    }

    /**
     * @throws RuntimeException if the category is a protected built-in one.
     */
    public function delete(int $id): bool
    {
        $row = $this->find($id);
        if (!$row) {
            return false;
        }
        if (!empty($row['is_system'])) {
            throw new RuntimeException('This is a built-in category and cannot be deleted. You can deactivate it instead so it no longer appears in the dropdown.');
        }
        $stmt = $this->db->prepare("DELETE FROM document_categories WHERE category_id = ?");
        return $stmt->execute([$id]);
    }

    private function generateUniqueKey(string $label): string
    {
        $base = strtolower(trim($label));
        $base = preg_replace('/[^a-z0-9]+/', '_', $base) ?? '';
        $base = trim($base, '_');
        if ($base === '') {
            $base = 'category';
        }
        $key = $base;
        $i = 2;
        while ($this->findByKey($key) !== null) {
            $key = $base . '_' . $i;
            $i++;
        }
        return $key;
    }
}
