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

        // ── Pending-execution count ───────────────────────────────────────
        // "Pending execution" = status name is NOT one of the terminal/late-stage
        // statuses AND end_date IS NULL
        $excludedStatuses = [
            'town council', 'town council review',
            'out for signature',
            'executed', 'contract executed',
            'work started',
        ];
        $exPlaceholders = implode(',', array_fill(0, count($excludedStatuses), '?'));
        $pendingStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM contracts c
              LEFT JOIN contract_statuses cs ON cs.contract_status_id = c.contract_status_id
              WHERE (c.end_date IS NULL OR YEAR(c.end_date) = 0)
                AND LOWER(COALESCE(cs.contract_status_name,'')) NOT IN ($exPlaceholders)"
        );
        $pendingStmt->execute($excludedStatuses);
        $pendingCount = (int)$pendingStmt->fetchColumn();

        // ── Stale-draft count + IDs ───────────────────────────────────────
        $staleStmt = $this->db->prepare(
            "SELECT c.contract_id FROM contracts c
              LEFT JOIN contract_statuses cs ON cs.contract_status_id = c.contract_status_id
              WHERE (
                  LOWER(cs.contract_status_name) LIKE 'draft%'
               OR LOWER(cs.contract_status_name) LIKE 'negotiat%'
              )
              AND c.created_at <= DATE_SUB(NOW(), INTERVAL 5 DAY)"
        );
        $staleStmt->execute();
        $staleIds = array_flip($staleStmt->fetchAll(PDO::FETCH_COLUMN));
        $staleCount = count($staleIds);

        // ── Review-phase count ────────────────────────────────────────────
        $reviewStatuses = ['procurement review', 'legal review', 'dept review', 'manager review'];
        $rvPlaceholders = implode(',', array_fill(0, count($reviewStatuses), '?'));
        $reviewStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM contracts c
              LEFT JOIN contract_statuses cs ON cs.contract_status_id = c.contract_status_id
              WHERE LOWER(COALESCE(cs.contract_status_name,'')) IN ($rvPlaceholders)"
        );
        $reviewStmt->execute($reviewStatuses);
        $reviewCount = (int)$reviewStmt->fetchColumn();

        // ── Town Council count ────────────────────────────────────────────
        $tcStatuses = ['town council', 'town council review'];
        $tcPlaceholders = implode(',', array_fill(0, count($tcStatuses), '?'));
        $tcStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM contracts c
              LEFT JOIN contract_statuses cs ON cs.contract_status_id = c.contract_status_id
              WHERE LOWER(COALESCE(cs.contract_status_name,'')) IN ($tcPlaceholders)"
        );
        $tcStmt->execute($tcStatuses);
        $townCouncilCount = (int)$tcStmt->fetchColumn();

        // ── Out for signature count ───────────────────────────────────────
        $sigStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM contracts c
              LEFT JOIN contract_statuses cs ON cs.contract_status_id = c.contract_status_id
              WHERE LOWER(COALESCE(cs.contract_status_name,'')) = 'out for signature'"
        );
        $sigStmt->execute();
        $outForSignatureCount = (int)$sigStmt->fetchColumn();

        // All statuses for radio filter
        $statuses = $this->statusModel->all();

        // ── Pending approvals by role ─────────────────────────────────────
        // For each approval type the current user's roles map to, count
        // contracts where that approval date is still NULL.
        require_once APP_ROOT . '/app/controllers/ApprovalRulesController.php';
        $approvalRoleMap = ApprovalRulesController::APPROVAL_ROLE_MAP;  // key => role_key|null
        $approvalLabels  = ApprovalRulesController::APPROVAL_LABELS;
        $myPendingApprovals = [];  // [['key','label','count'], ...]

        foreach ($approvalRoleMap as $approvalKey => $requiredRoleKey) {
            // Only surface if user actually holds this role (or it has no role requirement)
            $holds = ($requiredRoleKey === null)
                || (function_exists('person_has_role_key') && person_has_role_key($requiredRoleKey));
            if (!$holds) continue;

            // Map approval key -> column name
            $colMap = [
                'manager'      => 'manager_approval_date',
                'purchasing'   => 'purchasing_approval_date',
                'legal'        => 'legal_approval_date',
                'risk_manager' => 'risk_manager_approval_date',
                'council'      => 'council_approval_date',
            ];
            $col = $colMap[$approvalKey];

            // Count contracts where this approval date is null
            // (only count contracts that have at least one active approval rule requiring this type)
            $countStmt = $this->db->prepare(
                "SELECT COUNT(*) FROM contracts WHERE `$col` IS NULL"
            );
            $countStmt->execute();
            $total = (int)$countStmt->fetchColumn();

            if ($total > 0) {
                $myPendingApprovals[] = [
                    'key'   => $approvalKey,
                    'label' => $approvalLabels[$approvalKey] ?? $approvalKey,
                    'count' => $total,
                ];
            }
        }

        // All contracts (unfiltered); JS handles client-side filtering
        $contracts = $this->contractModel->search([]);

        require APP_ROOT . '/app/views/dashboard/index.php'; // $staleCount, $staleIds, $pendingCount, $reviewCount, $townCouncilCount, $outForSignatureCount passed via scope
    }
}
