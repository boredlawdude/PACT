<?php
declare(strict_types=1);

class ChangeOrder
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function allForContract(int $contractId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM change_orders
            WHERE contract_id = :contract_id
            ORDER BY change_order_number ASC, change_order_id ASC
        ");
        $stmt->execute(['contract_id' => $contractId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $changeOrderId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM change_orders
            WHERE change_order_id = :change_order_id
            LIMIT 1
        ");
        $stmt->execute(['change_order_id' => $changeOrderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(int $contractId, array $data): int
    {
        $params = $this->normalize($data);
        $params['contract_id'] = $contractId;
        $stmt = $this->db->prepare("
            INSERT INTO change_orders
                (contract_id, change_order_number, co_justification, co_amount, approval_date)
            VALUES
                (:contract_id, :change_order_number, :co_justification, :co_amount, :approval_date)
        ");
        $stmt->execute($params);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $changeOrderId, array $data): void
    {
        $params = $this->normalize($data);
        $params['change_order_id'] = $changeOrderId;
        $stmt = $this->db->prepare("
            UPDATE change_orders SET
                change_order_number = :change_order_number,
                co_justification    = :co_justification,
                co_amount           = :co_amount,
                approval_date       = :approval_date
            WHERE change_order_id = :change_order_id
        ");
        $stmt->execute($params);
    }

    public function delete(int $changeOrderId): void
    {
        $stmt = $this->db->prepare("DELETE FROM change_orders WHERE change_order_id = ?");
        $stmt->execute([$changeOrderId]);
    }

    private function normalize(array $data): array
    {
        $amount = $data['co_amount'] ?? null;
        if ($amount !== null && $amount !== '') {
            $amount = str_replace(['$', ',', ' '], '', (string)$amount);
            $amount = is_numeric($amount) ? $amount : null;
        } else {
            $amount = null;
        }

        return [
            'change_order_number' => trim((string)($data['change_order_number'] ?? '')),
            'co_justification'    => ($data['co_justification'] !== null && $data['co_justification'] !== '')
                                        ? trim((string)$data['co_justification']) : null,
            'co_amount'           => $amount,
            'approval_date'       => ($data['approval_date'] !== null && $data['approval_date'] !== '')
                                        ? $data['approval_date'] : null,
        ];
    }
}
