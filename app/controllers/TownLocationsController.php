<?php
// app/controllers/TownLocationsController.php
class TownLocationsController {
    private $model;
    public function __construct() {
        require_once APP_ROOT . '/app/models/TownLocation.php';
        $this->model = new TownLocation(db());
    }

    private function requireAdmin(): void {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
    }

    public function index(): void {
        $this->requireAdmin();
        $locations = $this->model->all();
        $errors = $_SESSION['town_location_errors'] ?? [];
        $success = $_SESSION['town_location_success'] ?? false;
        unset($_SESSION['town_location_errors'], $_SESSION['town_location_success']);
        require APP_ROOT . '/app/views/admin_settings/town_locations.php';
    }

    private function collect(): array {
        return [
            'location_name' => trim($_POST['location_name'] ?? ''),
            'address_line1' => trim($_POST['address_line1'] ?? ''),
            'address_line2' => trim($_POST['address_line2'] ?? ''),
            'city'          => trim($_POST['city'] ?? ''),
            'state_region'  => trim($_POST['state_region'] ?? ''),
            'postal_code'   => trim($_POST['postal_code'] ?? ''),
            'is_active'     => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    public function create(): void {
        $this->requireAdmin();
        $data = $this->collect();
        $errors = [];
        if ($data['location_name'] === '') {
            $errors[] = 'Location name is required.';
        }
        if (empty($errors)) {
            $this->model->create($data);
        }
        $_SESSION['town_location_errors'] = $errors;
        $_SESSION['town_location_success'] = empty($errors);
        header('Location: /index.php?page=admin_town_locations');
        exit;
    }

    public function update(): void {
        $this->requireAdmin();
        $id = (int)($_POST['location_id'] ?? 0);
        $data = $this->collect();
        $errors = [];
        if ($id <= 0) {
            $errors[] = 'Invalid location ID.';
        }
        if ($data['location_name'] === '') {
            $errors[] = 'Location name is required.';
        }
        if (empty($errors)) {
            $this->model->update($id, $data);
        }
        $_SESSION['town_location_errors'] = $errors;
        $_SESSION['town_location_success'] = empty($errors);
        header('Location: /index.php?page=admin_town_locations');
        exit;
    }

    public function delete(): void {
        $this->requireAdmin();
        $id = (int)($_POST['location_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['town_location_errors'] = ['Invalid location ID.'];
            header('Location: /index.php?page=admin_town_locations');
            exit;
        }
        try {
            $this->model->delete($id);
            $_SESSION['town_location_success'] = true;
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), '1451') || str_contains($e->getMessage(), 'foreign key')) {
                $_SESSION['town_location_errors'] = ['Cannot delete: this location is in use by one or more contracts.'];
            } else {
                $_SESSION['town_location_errors'] = ['Delete failed. Please try again.'];
            }
        }
        header('Location: /index.php?page=admin_town_locations');
        exit;
    }
}
