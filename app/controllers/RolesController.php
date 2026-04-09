<?php
declare(strict_types=1);

class RolesController
{
    private Role $model;

    public function __construct()
    {
        require_once APP_ROOT . '/app/models/Role.php';
        $this->model = new Role(db());
    }

    private function requireAdmin(): void
    {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
    }

    public function index(): void
    {
        $this->requireAdmin();
        $roles  = $this->model->all();
        $errors  = $_SESSION['roles_errors']  ?? [];
        $success = $_SESSION['roles_success'] ?? false;
        unset($_SESSION['roles_errors'], $_SESSION['roles_success']);
        require APP_ROOT . '/app/views/admin_settings/roles.php';
    }

    public function create(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed.');
        }

        $roleKey     = strtoupper(trim($_POST['role_key']     ?? ''));
        $roleName    = trim($_POST['role_name']    ?? '');
        $description = trim($_POST['description']  ?? '');
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        $errors = $this->validate($roleKey, $roleName);

        if (empty($errors)) {
            $this->model->create($roleKey, $roleName, $description, $isActive);
            $_SESSION['roles_success'] = true;
        } else {
            $_SESSION['roles_errors'] = $errors;
        }

        header('Location: /index.php?page=admin_roles');
        exit;
    }

    public function update(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed.');
        }

        $id          = (int)($_POST['role_id']       ?? 0);
        $roleKey     = strtoupper(trim($_POST['role_key']     ?? ''));
        $roleName    = trim($_POST['role_name']    ?? '');
        $description = trim($_POST['description']  ?? '');
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if ($id <= 0) {
            $errors[] = 'Invalid role ID.';
        }
        $errors = array_merge($errors, $this->validate($roleKey, $roleName));

        if (empty($errors)) {
            $this->model->update($id, $roleKey, $roleName, $description, $isActive);
            $_SESSION['roles_success'] = true;
        } else {
            $_SESSION['roles_errors'] = $errors;
        }

        header('Location: /index.php?page=admin_roles');
        exit;
    }

    public function delete(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed.');
        }

        $id = (int)($_POST['role_id'] ?? 0);
        $errors = [];

        if ($id <= 0) {
            $errors[] = 'Invalid role ID.';
        } elseif ($this->model->isInUse($id)) {
            $errors[] = 'Cannot delete a role that is currently assigned to users.';
        } else {
            $this->model->delete($id);
            $_SESSION['roles_success'] = true;
        }

        if (!empty($errors)) {
            $_SESSION['roles_errors'] = $errors;
        }

        header('Location: /index.php?page=admin_roles');
        exit;
    }

    private function validate(string $roleKey, string $roleName): array
    {
        $errors = [];
        if ($roleKey === '') {
            $errors[] = 'Role key is required.';
        } elseif (!preg_match('/^[A-Z0-9_]+$/', $roleKey)) {
            $errors[] = 'Role key must contain only uppercase letters, numbers, and underscores.';
        }
        if ($roleName === '') {
            $errors[] = 'Role name is required.';
        }
        return $errors;
    }
}
