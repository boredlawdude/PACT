<?php
declare(strict_types=1);

final class DepartmentsController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = db();
    }

    public function index(): void
    {
        $stmt = $this->pdo->query("
            SELECT
                d.department_id,
                d.department_code,
                d.department_name,
                d.dept_initials,
                d.is_active
            FROM departments d
            ORDER BY d.department_name
        ");

        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require APP_ROOT . '/app/views/departments/index.php';
    }

    public function create(): void
    {
        $department = $this->emptyDepartment();
        $people = $this->peopleOptions();
        $errors = [];

        require APP_ROOT . '/app/views/departments/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php?page=departments');
            exit;
        }

        $department = $this->collectFormData();
        $errors = $this->validate($department, false);

        if ($errors) {
            $people = $this->peopleOptions();
            require APP_ROOT . '/app/views/departments/create.php';
            return;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO departments (
                department_code,
                department_name,
                dept_initials,
                is_active,
                notes,
                department_head_id,
                assistant_town_manager_id,
                contract_admin_id
            ) VALUES (
                :department_code,
                :department_name,
                :dept_initials,
                :is_active,
                :notes,
                :department_head_id,
                :assistant_town_manager_id,
                :contract_admin_id
            )
        ");

        $stmt->execute([
            'department_code' => $department['department_code'],
            'department_name' => $department['department_name'],
            'dept_initials' => $department['dept_initials'],
            'is_active' => $department['is_active'],
            'notes' => $department['notes'],
            'department_head_id' => $department['department_head_id'] !== '' ? (int)$department['department_head_id'] : null,
            'assistant_town_manager_id' => $department['assistant_town_manager_id'] !== '' ? (int)$department['assistant_town_manager_id'] : null,
            'contract_admin_id' => $department['contract_admin_id'] !== '' ? (int)$department['contract_admin_id'] : null,
        ]);

        $id = (int)$this->pdo->lastInsertId();

        header('Location: /index.php?page=department_edit&id=' . $id . '&saved=1');
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $this->pdo->prepare("
            SELECT
                department_id,
                department_code,
                department_name,
                dept_initials,
                is_active,
                notes,
                created_at,
                updated_at,
                department_head_id,
                assistant_town_manager_id,
                contract_admin_id
            FROM departments
            WHERE department_id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);

        $department = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$department) {
            http_response_code(404);
            echo 'Department not found';
            return;
        }

        $people = $this->peopleOptions();
        $errors = [];

        require APP_ROOT . '/app/views/departments/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php?page=departments');
            exit;
        }

        $department = $this->collectFormData();
        $errors = $this->validate($department, true);

        if ($errors) {
            $people = $this->peopleOptions();
            require APP_ROOT . '/app/views/departments/edit.php';
            return;
        }

        $stmt = $this->pdo->prepare("
            UPDATE departments
            SET
                department_code = :department_code,
                department_name = :department_name,
                dept_initials = :dept_initials,
                is_active = :is_active,
                notes = :notes,
                department_head_id = :department_head_id,
                assistant_town_manager_id = :assistant_town_manager_id,
                contract_admin_id = :contract_admin_id
            WHERE department_id = :department_id
        ");

        $stmt->execute([
            'department_id' => (int)$department['department_id'],
            'department_code' => $department['department_code'],
            'department_name' => $department['department_name'],
            'dept_initials' => $department['dept_initials'],
            'is_active' => $department['is_active'],
            'notes' => $department['notes'],
            'department_head_id' => $department['department_head_id'] !== '' ? (int)$department['department_head_id'] : null,
            'assistant_town_manager_id' => $department['assistant_town_manager_id'] !== '' ? (int)$department['assistant_town_manager_id'] : null,
            'contract_admin_id' => $department['contract_admin_id'] !== '' ? (int)$department['contract_admin_id'] : null,
        ]);

        header('Location: /index.php?page=department_edit&id=' . (int)$department['department_id'] . '&saved=1');
        exit;
    }

    private function collectFormData(): array
    {
        return [
            'department_id' => (int)($_POST['department_id'] ?? 0),
            'department_code' => trim((string)($_POST['department_code'] ?? '')),
            'department_name' => trim((string)($_POST['department_name'] ?? '')),
            'dept_initials' => trim((string)($_POST['dept_initials'] ?? '')),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'notes' => trim((string)($_POST['notes'] ?? '')),
            'department_head_id' => trim((string)($_POST['department_head_id'] ?? '')),
            'assistant_town_manager_id' => trim((string)($_POST['assistant_town_manager_id'] ?? '')),
            'contract_admin_id' => trim((string)($_POST['contract_admin_id'] ?? '')),
            'created_at' => trim((string)($_POST['created_at'] ?? '')),
            'updated_at' => trim((string)($_POST['updated_at'] ?? '')),
        ];
    }

    private function validate(array $department, bool $isEdit): array
    {
        $errors = [];

        if ($department['department_code'] === '') {
            $errors[] = 'Department code is required.';
        }

        if ($department['department_name'] === '') {
            $errors[] = 'Department name is required.';
        }

        if ($department['dept_initials'] === '') {
            $errors[] = 'Department initials are required.';
        }

        if (mb_strlen($department['department_code']) > 50) {
            $errors[] = 'Department code must be 50 characters or fewer.';
        }

        if (mb_strlen($department['department_name']) > 255) {
            $errors[] = 'Department name must be 255 characters or fewer.';
        }

        if (mb_strlen($department['dept_initials']) > 10) {
            $errors[] = 'Department initials must be 10 characters or fewer.';
        }

        $dupStmt = $this->pdo->prepare("
            SELECT department_id
            FROM departments
            WHERE department_code = :department_code
              AND department_id <> :department_id
            LIMIT 1
        ");
        $dupStmt->execute([
            'department_code' => $department['department_code'],
            'department_id' => $isEdit ? (int)$department['department_id'] : 0,
        ]);

        if ($dupStmt->fetch()) {
            $errors[] = 'Department code must be unique.';
        }

        return $errors;
    }

    private function peopleOptions(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                person_id,
                CONCAT(first_name, ' ', last_name) AS full_name
            FROM people
            WHERE is_active = 1
            ORDER BY full_name
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function emptyDepartment(): array
    {
        return [
            'department_id' => 0,
            'department_code' => '',
            'department_name' => '',
            'dept_initials' => '',
            'is_active' => 1,
            'notes' => '',
            'created_at' => '',
            'updated_at' => '',
            'department_head_id' => '',
            'assistant_town_manager_id' => '',
            'contract_admin_id' => '',
        ];
    }
}