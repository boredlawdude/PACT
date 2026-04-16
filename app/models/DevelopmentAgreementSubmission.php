<?php
declare(strict_types=1);

class DevelopmentAgreementSubmission
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT s.*
            FROM development_agreement_submissions s
            ORDER BY
                FIELD(s.status, 'pending', 'imported', 'rejected'),
                s.submitted_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countPending(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM development_agreement_submissions WHERE status = 'pending'"
        );
        return (int)$stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM development_agreement_submissions WHERE submission_id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $n = fn($v) => (trim((string)$v) !== '') ? trim((string)$v) : null;
        $d = fn($v) => ($v !== '' && $v !== null) ? $v : null;

        $stmt = $this->db->prepare("
            INSERT INTO development_agreement_submissions
                (submitter_name, submitter_email, submitter_phone, submitter_company,
                 project_name, project_description, proposed_improvements,
                 current_zoning, proposed_zoning, comp_plan_designation,
                 anticipated_start_date, anticipated_end_date,
                 agreement_termination_date, planning_board_date, town_council_hearing_date,
                 tracts_json, status)
            VALUES
                (:submitter_name, :submitter_email, :submitter_phone, :submitter_company,
                 :project_name, :project_description, :proposed_improvements,
                 :current_zoning, :proposed_zoning, :comp_plan_designation,
                 :anticipated_start_date, :anticipated_end_date,
                 :agreement_termination_date, :planning_board_date, :town_council_hearing_date,
                 :tracts_json, 'pending')
        ");
        $stmt->execute([
            ':submitter_name'             => $n($data['submitter_name']             ?? null),
            ':submitter_email'            => $n($data['submitter_email']            ?? null),
            ':submitter_phone'            => $n($data['submitter_phone']            ?? null),
            ':submitter_company'          => $n($data['submitter_company']          ?? null),
            ':project_name'               => trim((string)($data['project_name']    ?? '')),
            ':project_description'        => $n($data['project_description']        ?? null),
            ':proposed_improvements'      => $n($data['proposed_improvements']      ?? null),
            ':current_zoning'             => $n($data['current_zoning']             ?? null),
            ':proposed_zoning'            => $n($data['proposed_zoning']            ?? null),
            ':comp_plan_designation'      => $n($data['comp_plan_designation']      ?? null),
            ':anticipated_start_date'     => $d($data['anticipated_start_date']     ?? null),
            ':anticipated_end_date'       => $d($data['anticipated_end_date']       ?? null),
            ':agreement_termination_date' => $d($data['agreement_termination_date'] ?? null),
            ':planning_board_date'        => $d($data['planning_board_date']        ?? null),
            ':town_council_hearing_date'  => $d($data['town_council_hearing_date']  ?? null),
            ':tracts_json'                => $data['tracts_json'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function markImported(int $id, int $devAgreementId, int $reviewedBy): void
    {
        $stmt = $this->db->prepare("
            UPDATE development_agreement_submissions
            SET status = 'imported',
                imported_dev_agreement_id = :dev_agreement_id,
                reviewed_by  = :reviewed_by,
                reviewed_at  = NOW()
            WHERE submission_id = :id
        ");
        $stmt->execute([
            ':dev_agreement_id' => $devAgreementId,
            ':reviewed_by'      => $reviewedBy,
            ':id'               => $id,
        ]);
    }

    public function markRejected(int $id, int $reviewedBy, ?string $notes = null): void
    {
        $stmt = $this->db->prepare("
            UPDATE development_agreement_submissions
            SET status       = 'rejected',
                review_notes = :notes,
                reviewed_by  = :reviewed_by,
                reviewed_at  = NOW()
            WHERE submission_id = :id
        ");
        $stmt->execute([
            ':notes'       => $notes,
            ':reviewed_by' => $reviewedBy,
            ':id'          => $id,
        ]);
    }
}
