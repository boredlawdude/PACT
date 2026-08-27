<?php
declare(strict_types=1);

class TasksController
{
    private PDO $db;

    /** A task with no update in this many days (and not done) is considered "stale". */
    public const STALE_DAYS = 5;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * My Tasks list (default) or All Tasks (system admins/superusers only).
     */
    public function index(): void
    {
        $personId  = current_person_id();
        $view      = ($_GET['view'] ?? 'mine') === 'all' && function_exists('is_system_admin') && is_system_admin()
            ? 'all'
            : 'mine';
        $statusFilter = $_GET['status'] ?? 'open';

        $where  = [];
        $params = [];

        if ($view === 'mine') {
            $where[]  = 't.assigned_to_person_id = ?';
            $params[] = $personId;
        }

        if ($statusFilter === 'open') {
            $where[] = "t.status != 'done'";
        } elseif ($statusFilter === 'done') {
            $where[] = "t.status = 'done'";
        }
        // 'all' status filter = no restriction

        $sql = "
            SELECT t.*,
                   ap.display_name AS assigned_to_name,
                   cp.display_name AS created_by_name,
                   c.contract_number, c.name AS contract_name,
                   (t.status != 'done' AND t.updated_at < (NOW() - INTERVAL " . self::STALE_DAYS . " DAY)) AS is_stale
            FROM tasks t
            JOIN people ap ON ap.person_id = t.assigned_to_person_id
            LEFT JOIN people cp ON cp.person_id = t.created_by_person_id
            LEFT JOIN contracts c ON c.contract_id = t.contract_id
        ";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= " ORDER BY (t.status = 'done'), t.due_date IS NULL, t.due_date ASC, t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $flashMessages = $_SESSION['flash_messages'] ?? [];
        $flashErrors   = $_SESSION['flash_errors']   ?? [];
        unset($_SESSION['flash_messages'], $_SESSION['flash_errors']);

        require APP_ROOT . '/app/views/tasks/index.php';
    }

    public function create(): void
    {
        $mode  = 'create';
        $task  = $_SESSION['old_task_form'] ?? [
            'contract_id' => (int)($_GET['contract_id'] ?? 0) ?: null,
        ];
        unset($_SESSION['old_task_form']);

        $errors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);

        $people = $this->getAssignablePeople();

        $linkedContract = null;
        if (!empty($task['contract_id'])) {
            $linkedContract = $this->findContract((int)$task['contract_id']);
        }

        require APP_ROOT . '/app/views/tasks/form.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        [$data, $errors] = $this->readAndValidate($_POST);

        if ($errors) {
            $_SESSION['flash_errors']  = $errors;
            $_SESSION['old_task_form'] = $_POST;
            $redirect = !empty($_POST['contract_id'])
                ? '/index.php?page=tasks_create&contract_id=' . (int)$_POST['contract_id']
                : '/index.php?page=tasks_create';
            header('Location: ' . $redirect);
            exit;
        }

        $createdBy = current_person_id() ?: null;

        $stmt = $this->db->prepare("
            INSERT INTO tasks (title, description, assigned_to_person_id, created_by_person_id, contract_id, due_date)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['assigned_to_person_id'],
            $createdBy,
            $data['contract_id'],
            $data['due_date'],
        ]);

        $_SESSION['flash_messages'] = ['Task assigned.'];

        if (!empty($data['contract_id'])) {
            header('Location: /index.php?page=contracts_show&contract_id=' . $data['contract_id'] . '#tasks');
        } else {
            header('Location: /index.php?page=tasks');
        }
        exit;
    }

    public function edit(int $taskId): void
    {
        $task = $this->findTask($taskId);
        if (!$task) {
            http_response_code(404);
            echo 'Task not found.';
            return;
        }

        $mode   = 'edit';
        $errors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_errors']);

        $old = $_SESSION['old_task_form'] ?? null;
        unset($_SESSION['old_task_form']);
        if ($old) {
            $task = array_merge($task, $old);
        }

        $people = $this->getAssignablePeople();

        $linkedContract = null;
        if (!empty($task['contract_id'])) {
            $linkedContract = $this->findContract((int)$task['contract_id']);
        }

        require APP_ROOT . '/app/views/tasks/form.php';
    }

    public function update(int $taskId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        $existing = $this->findTask($taskId);
        if (!$existing) {
            http_response_code(404);
            echo 'Task not found.';
            return;
        }

        [$data, $errors] = $this->readAndValidate($_POST);

        if ($errors) {
            $_SESSION['flash_errors']  = $errors;
            $_SESSION['old_task_form'] = $_POST;
            header('Location: /index.php?page=tasks_edit&task_id=' . $taskId);
            exit;
        }

        $stmt = $this->db->prepare("
            UPDATE tasks
            SET title = ?, description = ?, assigned_to_person_id = ?, contract_id = ?, due_date = ?
            WHERE task_id = ?
        ");
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['assigned_to_person_id'],
            $data['contract_id'],
            $data['due_date'],
            $taskId,
        ]);

        $_SESSION['flash_messages'] = ['Task updated.'];
        header('Location: /index.php?page=tasks_edit&task_id=' . $taskId);
        exit;
    }

    /**
     * POST: mark a task complete or reopen it (quick action from the list).
     */
    public function setStatus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        $taskId    = (int)($_POST['task_id'] ?? 0);
        $newStatus = (string)($_POST['status'] ?? '');

        if ($taskId <= 0 || !in_array($newStatus, ['open', 'in_progress', 'done'], true)) {
            http_response_code(400);
            echo 'Invalid request.';
            return;
        }

        $existing = $this->findTask($taskId);
        if (!$existing) {
            http_response_code(404);
            echo 'Task not found.';
            return;
        }

        if ($newStatus === 'done') {
            $stmt = $this->db->prepare("UPDATE tasks SET status = 'done', completed_at = NOW() WHERE task_id = ?");
            $stmt->execute([$taskId]);
        } else {
            $stmt = $this->db->prepare("UPDATE tasks SET status = ?, completed_at = NULL WHERE task_id = ?");
            $stmt->execute([$newStatus, $taskId]);
        }

        $redirectBack = $_POST['redirect'] ?? '';
        if ($redirectBack !== '' && str_starts_with($redirectBack, '/')) {
            header('Location: ' . $redirectBack);
        } else {
            header('Location: /index.php?page=tasks');
        }
        exit;
    }

    public function delete(): void
    {
        require_login();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        $taskId = (int)($_POST['task_id'] ?? 0);
        $task   = $this->findTask($taskId);
        if (!$task) {
            http_response_code(404);
            echo 'Task not found.';
            return;
        }

        $ownerId = $task['created_by_person_id'] ?? null;
        if (!can_delete_record($ownerId !== null ? (int)$ownerId : null)) {
            http_response_code(403);
            echo 'Forbidden.';
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM tasks WHERE task_id = ?");
        $stmt->execute([$taskId]);

        $_SESSION['flash_messages'] = ['Task deleted.'];

        $redirectBack = $_POST['redirect'] ?? '';
        if ($redirectBack !== '' && str_starts_with($redirectBack, '/')) {
            header('Location: ' . $redirectBack);
        } else {
            header('Location: /index.php?page=tasks');
        }
        exit;
    }

    /**
     * Count of open tasks assigned to the current user (used for the nav badge).
     */
    public static function countMyOpenTasks(int $personId): int
    {
        if ($personId <= 0) {
            return 0;
        }
        $stmt = db()->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to_person_id = ? AND status != 'done'");
        $stmt->execute([$personId]);
        return (int)$stmt->fetchColumn();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function readAndValidate(array $post): array
    {
        $title      = trim((string)($post['title'] ?? ''));
        $description = trim((string)($post['description'] ?? ''));
        $assignedTo = (int)($post['assigned_to_person_id'] ?? 0);
        $contractId = (int)($post['contract_id'] ?? 0) ?: null;
        $dueDate    = trim((string)($post['due_date'] ?? ''));

        $errors = [];
        if ($title === '') {
            $errors[] = 'Title is required.';
        } elseif (strlen($title) > 255) {
            $errors[] = 'Title must be 255 characters or fewer.';
        }
        if ($assignedTo <= 0) {
            $errors[] = 'Please select who this task is assigned to.';
        } else {
            $stmt = $this->db->prepare("SELECT person_id FROM people WHERE person_id = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$assignedTo]);
            if (!$stmt->fetch()) {
                $errors[] = 'Selected assignee is not a valid active user.';
            }
        }
        if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            $errors[] = 'Invalid due date format.';
        }
        if ($contractId !== null) {
            $stmt = $this->db->prepare("SELECT contract_id FROM contracts WHERE contract_id = ? LIMIT 1");
            $stmt->execute([$contractId]);
            if (!$stmt->fetch()) {
                $errors[] = 'Linked contract not found.';
            }
        }

        return [
            [
                'title' => $title,
                'description' => $description !== '' ? $description : null,
                'assigned_to_person_id' => $assignedTo ?: null,
                'contract_id' => $contractId,
                'due_date' => $dueDate !== '' ? $dueDate : null,
            ],
            $errors,
        ];
    }

    private function findTask(int $taskId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE task_id = ? LIMIT 1");
        $stmt->execute([$taskId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function findContract(int $contractId): ?array
    {
        $stmt = $this->db->prepare("SELECT contract_id, contract_number, name FROM contracts WHERE contract_id = ? LIMIT 1");
        $stmt->execute([$contractId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getAssignablePeople(): array
    {
        $stmt = $this->db->query("
            SELECT person_id, display_name
            FROM people
            WHERE is_active = 1 AND can_login = 1
            ORDER BY display_name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Open tasks assigned to a specific person (used by the dashboard's
     * "My Pending Approvals and Tasks" box).
     */
    public function getMyOpenTasks(int $personId): array
    {
        if ($personId <= 0) {
            return [];
        }
        $stmt = $this->db->prepare("
            SELECT t.task_id, t.title, t.due_date, t.contract_id, t.status,
                   c.contract_number,
                   (t.status != 'done' AND t.updated_at < (NOW() - INTERVAL " . self::STALE_DAYS . " DAY)) AS is_stale
            FROM tasks t
            LEFT JOIN contracts c ON c.contract_id = t.contract_id
            WHERE t.assigned_to_person_id = ? AND t.status != 'done'
            ORDER BY t.due_date IS NULL, t.due_date ASC, t.created_at DESC
        ");
        $stmt->execute([$personId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Related tasks for a contract's show page (used by ContractsController::show()).
     */
    public function getTasksForContract(int $contractId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, ap.display_name AS assigned_to_name,
                   (t.status != 'done' AND t.updated_at < (NOW() - INTERVAL " . self::STALE_DAYS . " DAY)) AS is_stale
            FROM tasks t
            JOIN people ap ON ap.person_id = t.assigned_to_person_id
            WHERE t.contract_id = ?
            ORDER BY (t.status = 'done'), t.due_date IS NULL, t.due_date ASC, t.created_at DESC
        ");
        $stmt->execute([$contractId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
