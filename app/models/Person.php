<?php
declare(strict_types=1);

final class Person
{
    private ?bool $hasNextcloudColumns = null;

    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM people
            WHERE person_id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $params = [
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => $data['email'] !== '' ? $data['email'] : null,
            'officephone'   => $data['office_phone'] ?? ($data['officephone'] ?? ''),
            'cellphone'     => $data['cell_phone'] ?? ($data['cellphone'] ?? ''),
            'title'         => $data['title'],
            'department_id' => $data['department_id'] ?: null,
            'is_active'     => $data['is_active'],
            'is_town_employee' => $data['is_town_employee'],
            'company_id'    => $data['company_id'] ?: null,
        ];

        if ($this->hasNextcloudColumns()) {
            $params['nextcloud_username'] = trim((string)($data['nextcloud_username'] ?? ''));
            $nextcloudPassword = trim((string)($data['nextcloud_password'] ?? ''));
            $params['nextcloud_password'] = $nextcloudPassword !== '' ? $nextcloudPassword : null;
        }

        $columns = array_keys($params);
        $columnSql = implode(', ', $columns);
        $valueSql = implode(', ', array_map(static fn(string $col): string => ':' . $col, $columns));

        $stmt = $this->pdo->prepare(
            "INSERT INTO people ($columnSql) VALUES ($valueSql)"
        );

        $stmt->execute($params);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $params = [
            'id'            => $id,
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => $data['email'] !== '' ? $data['email'] : null,
            'officephone'   => $data['office_phone'] ?? ($data['officephone'] ?? ''),
            'cellphone'     => $data['cell_phone'] ?? ($data['cellphone'] ?? ''),
            'title'         => $data['title'],
            'department_id' => $data['department_id'] ?: null,
            'is_active'     => $data['is_active'],
            'is_town_employee' => $data['is_town_employee'],
        ];

        $updates = [
            'first_name = :first_name',
            'last_name = :last_name',
            'email = :email',
            'officephone = :officephone',
            'cellphone = :cellphone',
            'title = :title',
            'department_id = :department_id',
            'is_active = :is_active',
            'is_town_employee = :is_town_employee',
        ];

        if ($this->hasNextcloudColumns()) {
            $updates[] = 'nextcloud_username = :nextcloud_username';
            $params['nextcloud_username'] = trim((string)($data['nextcloud_username'] ?? ''));

            $clear = !empty($data['clear_nextcloud_password']);
            $newPassword = trim((string)($data['nextcloud_password'] ?? ''));
            if ($clear) {
                $updates[] = 'nextcloud_password = NULL';
            } elseif ($newPassword !== '') {
                $updates[] = 'nextcloud_password = :nextcloud_password';
                $params['nextcloud_password'] = $newPassword;
            }
        }

        $stmt = $this->pdo->prepare(
            'UPDATE people SET ' . implode(', ', $updates) . ' WHERE person_id = :id'
        );

        $stmt->execute($params);
    }

    public function allDepartments(): array
    {
        $stmt = $this->pdo->query("
            SELECT department_id, department_name, dept_initials
            FROM departments
            ORDER BY department_name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function hasNextcloudColumns(): bool
    {
        if ($this->hasNextcloudColumns !== null) {
            return $this->hasNextcloudColumns;
        }

        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM people LIKE 'nextcloud_username'");
            $hasUsername = (bool)$stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->query("SHOW COLUMNS FROM people LIKE 'nextcloud_password'");
            $hasPassword = (bool)$stmt->fetch(PDO::FETCH_ASSOC);

            $this->hasNextcloudColumns = $hasUsername && $hasPassword;
        } catch (Throwable $e) {
            $this->hasNextcloudColumns = false;
        }

        return $this->hasNextcloudColumns;
    }
}