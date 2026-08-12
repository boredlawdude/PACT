<?php
// app/controllers/ProcurementMethodController.php
class ProcurementMethodController {
    private $model;
    public function __construct() {
        require_once APP_ROOT . '/app/models/ProcurementMethod.php';
        $this->model = new ProcurementMethod(db());
    }

    public function index() {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
        $methods = $this->model->all();
        $errors = $_SESSION['procurement_method_errors'] ?? [];
        $success = $_SESSION['procurement_method_success'] ?? false;
        unset($_SESSION['procurement_method_errors'], $_SESSION['procurement_method_success']);
        require APP_ROOT . '/app/views/admin_settings/procurement_methods.php';
    }

    public function create() {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
        $shortDesc = trim($_POST['short_desc'] ?? '');
        $longDesc = trim($_POST['long_desc'] ?? '');
        $errors = [];
        if ($shortDesc === '') {
            $errors[] = 'Short description is required.';
        }
        if (empty($errors)) {
            $this->model->create($shortDesc, $longDesc !== '' ? $longDesc : null);
        }
        $_SESSION['procurement_method_errors'] = $errors;
        $_SESSION['procurement_method_success'] = empty($errors);
        header('Location: /index.php?page=admin_procurement_methods');
        exit;
    }

    public function update() {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
        $id = (int)($_POST['procurement_method_id'] ?? 0);
        $shortDesc = trim($_POST['short_desc'] ?? '');
        $longDesc = trim($_POST['long_desc'] ?? '');
        $errors = [];
        if ($id <= 0) {
            $errors[] = 'Invalid procurement method ID.';
        }
        if ($shortDesc === '') {
            $errors[] = 'Short description is required.';
        }
        if (empty($errors)) {
            $this->model->update($id, $shortDesc, $longDesc !== '' ? $longDesc : null);
        }
        $_SESSION['procurement_method_errors'] = $errors;
        $_SESSION['procurement_method_success'] = empty($errors);
        header('Location: /index.php?page=admin_procurement_methods');
        exit;
    }

    public function delete() {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
        $id = (int)($_POST['procurement_method_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['procurement_method_errors'] = ['Invalid procurement method ID.'];
            header('Location: /index.php?page=admin_procurement_methods');
            exit;
        }
        try {
            $this->model->delete($id);
            $_SESSION['procurement_method_success'] = true;
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), '1451') || str_contains($e->getMessage(), 'foreign key')) {
                $_SESSION['procurement_method_errors'] = ['Cannot delete: this procurement method is in use by one or more contracts.'];
            } else {
                $_SESSION['procurement_method_errors'] = ['Delete failed. Please try again.'];
            }
        }
        header('Location: /index.php?page=admin_procurement_methods');
        exit;
    }
}
