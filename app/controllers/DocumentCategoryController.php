<?php
// app/controllers/DocumentCategoryController.php
declare(strict_types=1);

class DocumentCategoryController
{
    private DocumentCategory $model;

    public function __construct()
    {
        require_once APP_ROOT . '/app/models/DocumentCategory.php';
        $this->model = new DocumentCategory(db());
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
        $categories = $this->model->all();
        $errors = $_SESSION['document_category_errors'] ?? [];
        $success = $_SESSION['document_category_success'] ?? false;
        unset($_SESSION['document_category_errors'], $_SESSION['document_category_success']);
        require APP_ROOT . '/app/views/admin_settings/document_categories.php';
    }

    public function create(): void
    {
        $this->requireAdmin();
        $label = trim($_POST['label'] ?? '');
        $errors = [];
        if ($label === '') {
            $errors[] = 'Label is required.';
        }
        if (empty($errors)) {
            $this->model->create($label);
        }
        $_SESSION['document_category_errors'] = $errors;
        $_SESSION['document_category_success'] = empty($errors);
        header('Location: /index.php?page=admin_document_categories');
        exit;
    }

    public function update(): void
    {
        $this->requireAdmin();
        $id = (int)($_POST['category_id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        $isActive = !empty($_POST['is_active']);
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $errors = [];
        if ($id <= 0) {
            $errors[] = 'Invalid category ID.';
        }
        if ($label === '') {
            $errors[] = 'Label is required.';
        }
        if (empty($errors)) {
            $this->model->update($id, $label, $isActive, $sortOrder);
        }
        $_SESSION['document_category_errors'] = $errors;
        $_SESSION['document_category_success'] = empty($errors);
        header('Location: /index.php?page=admin_document_categories');
        exit;
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $id = (int)($_POST['category_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['document_category_errors'] = ['Invalid category ID.'];
            header('Location: /index.php?page=admin_document_categories');
            exit;
        }
        try {
            $this->model->delete($id);
            $_SESSION['document_category_success'] = true;
        } catch (\RuntimeException $e) {
            $_SESSION['document_category_errors'] = [$e->getMessage()];
        } catch (\PDOException $e) {
            $_SESSION['document_category_errors'] = ['Delete failed. Please try again.'];
        }
        header('Location: /index.php?page=admin_document_categories');
        exit;
    }
}
