<?php
// app/controllers/BiddingComplianceEventTypeController.php
class BiddingComplianceEventTypeController {
    private $model;
    public function __construct() {
        require_once APP_ROOT . '/app/models/BiddingComplianceEventType.php';
        $this->model = new BiddingComplianceEventType(db());
    }

    public function index() {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
        $eventTypes = $this->model->all();
        $errors = $_SESSION['bidding_event_type_errors'] ?? [];
        $success = $_SESSION['bidding_event_type_success'] ?? false;
        unset($_SESSION['bidding_event_type_errors'], $_SESSION['bidding_event_type_success']);
        require APP_ROOT . '/app/views/admin_settings/bidding_compliance_event_types.php';
    }

    public function create() {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
        $label = trim($_POST['label'] ?? '');
        $active = isset($_POST['active']);
        $errors = [];
        if ($label === '') {
            $errors[] = 'Event label is required.';
        }
        if (empty($errors)) {
            $this->model->create($label, $active);
        }
        $_SESSION['bidding_event_type_errors'] = $errors;
        $_SESSION['bidding_event_type_success'] = empty($errors);
        header('Location: /index.php?page=admin_bidding_compliance_event_types');
        exit;
    }

    public function update() {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
        $id = (int)($_POST['event_type_id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        $active = isset($_POST['active']);
        $errors = [];
        if ($id <= 0) {
            $errors[] = 'Invalid event type ID.';
        }
        if ($label === '') {
            $errors[] = 'Event label is required.';
        }
        if (empty($errors)) {
            $this->model->update($id, $label, $active);
        }
        $_SESSION['bidding_event_type_errors'] = $errors;
        $_SESSION['bidding_event_type_success'] = empty($errors);
        header('Location: /index.php?page=admin_bidding_compliance_event_types');
        exit;
    }

    public function delete() {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
        $id = (int)($_POST['event_type_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['bidding_event_type_errors'] = ['Invalid event type ID.'];
            header('Location: /index.php?page=admin_bidding_compliance_event_types');
            exit;
        }
        try {
            $this->model->delete($id);
            $_SESSION['bidding_event_type_success'] = true;
        } catch (\PDOException $e) {
            $_SESSION['bidding_event_type_errors'] = ['Delete failed. Please try again.'];
        }
        header('Location: /index.php?page=admin_bidding_compliance_event_types');
        exit;
    }
}
