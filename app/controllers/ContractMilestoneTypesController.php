<?php
declare(strict_types=1);

class ContractMilestoneTypesController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function index(): void
    {
        $stmt = $this->db->query("
            SELECT * FROM contract_milestone_types
            WHERE is_active = 1
            ORDER BY sort_order ASC, name ASC
        ");
        $milestoneTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $flashMessages = $_SESSION['flash_messages'] ?? [];
        $flashErrors   = $_SESSION['flash_errors']   ?? [];
        unset($_SESSION['flash_messages'], $_SESSION['flash_errors']);

        require APP_ROOT . '/app/views/contract_milestone_types/index.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            return;
        }

        $name       = trim((string)($_POST['name'] ?? ''));
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);

        $errors = [];
        if ($name === '') {
            $errors[] = 'Milestone type name is required.';
        } elseif (strlen($name) > 150) {
            $errors[] = 'Milestone type name must be 150 characters or fewer.';
        }

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
            header('Location: /index.php?page=admin_milestone_types');
            exit;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO contract_milestone_types (name, sort_order)
                VALUES (?, ?)
            ");
            $stmt->execute([$name, $sortOrder]);
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $_SESSION['flash_errors'] = ["A milestone type named \"$name\" already exists."];
            } else {
                $_SESSION['flash_errors'] = ['Failed to create milestone type: ' . $e->getMessage()];
            }
            header('Location: /index.php?page=admin_milestone_types');
            exit;
        }

        $_SESSION['flash_messages'] = ["Milestone type \"$name\" created."];
        header('Location: /index.php?page=admin_milestone_types');
        exit;
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            return;
        }

        $id        = (int)($_POST['milestone_type_id'] ?? 0);
        $name      = trim((string)($_POST['name'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        $errors = [];
        if ($id <= 0) {
            $errors[] = 'Invalid milestone type.';
        }
        if ($name === '') {
            $errors[] = 'Name is required.';
        }

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
            header('Location: /index.php?page=admin_milestone_types');
            exit;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE contract_milestone_types SET name = ?, sort_order = ? WHERE milestone_type_id = ?
            ");
            $stmt->execute([$name, $sortOrder, $id]);
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $_SESSION['flash_errors'] = ["A milestone type named \"$name\" already exists."];
            } else {
                $_SESSION['flash_errors'] = ['Failed to update milestone type: ' . $e->getMessage()];
            }
            header('Location: /index.php?page=admin_milestone_types');
            exit;
        }

        $_SESSION['flash_messages'] = ['Milestone type updated.'];
        header('Location: /index.php?page=admin_milestone_types');
        exit;
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            return;
        }

        $id = (int)($_POST['milestone_type_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_errors'] = ['Invalid milestone type.'];
            header('Location: /index.php?page=admin_milestone_types');
            exit;
        }

        // Check if in use
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM contract_milestones WHERE milestone_type_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            $_SESSION['flash_errors'] = ['Cannot delete: this milestone type is in use by one or more contracts.'];
            header('Location: /index.php?page=admin_milestone_types');
            exit;
        }

        $stmt = $this->db->prepare("UPDATE contract_milestone_types SET is_active = 0 WHERE milestone_type_id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_messages'] = ['Milestone type removed.'];
        header('Location: /index.php?page=admin_milestone_types');
        exit;
    }
}
