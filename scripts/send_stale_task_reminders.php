<?php
declare(strict_types=1);

/**
 * Sends a reminder email for every open (not-done) task that hasn't been
 * updated in TasksController::STALE_DAYS days, then stamps last_reminder_sent_at
 * so the same task isn't re-emailed every day.
 *
 * Intended to run once a day via cron on the VPS, e.g.:
 *   0 7 * * * /usr/bin/php /var/www/contracts_app/scripts/send_stale_task_reminders.php >> /var/www/contracts_app/storage/logs/task_reminders.log 2>&1
 *
 * Run manually for a one-off test:
 *   php scripts/send_stale_task_reminders.php
 */

require_once __DIR__ . '/../includes/bootstrap.php'; // loads vendor/autoload.php (PHPMailer)
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controllers/TasksController.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = db();

// Only re-remind a given task once every STALE_DAYS, to avoid spamming daily.
$staleDays = TasksController::STALE_DAYS;

$stmt = $db->prepare("
    SELECT t.task_id, t.title, t.description, t.due_date, t.contract_id, t.updated_at,
           ap.email AS assignee_email, ap.display_name AS assignee_name,
           c.contract_number, c.name AS contract_name
    FROM tasks t
    JOIN people ap ON ap.person_id = t.assigned_to_person_id
    LEFT JOIN contracts c ON c.contract_id = t.contract_id
    WHERE t.status != 'done'
      AND t.updated_at < (NOW() - INTERVAL {$staleDays} DAY)
      AND ap.email IS NOT NULL AND ap.email != ''
      AND (t.last_reminder_sent_at IS NULL OR t.last_reminder_sent_at < (NOW() - INTERVAL {$staleDays} DAY))
");
$stmt->execute();
$staleTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($staleTasks)) {
    echo "[" . date('Y-m-d H:i:s') . "] No stale tasks to remind.\n";
    exit(0);
}

$host = $_SERVER['HTTP_HOST'] ?? ($_ENV['APP_URL_HOST'] ?? 'localhost');
$scheme = 'https';

$sent = 0;
$failed = 0;

foreach ($staleTasks as $task) {
    $taskUrl = "$scheme://$host/index.php?page=tasks_edit&task_id=" . (int)$task['task_id'];
    $contractLine = '';
    if (!empty($task['contract_id'])) {
        $contractLine = "\nLinked contract: " . ($task['contract_number'] ?? '') . ' — ' . ($task['contract_name'] ?? '');
    }

    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 15;

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($task['assignee_email'], $task['assignee_name']);

        $mail->isHTML(false);
        $mail->Subject = 'Reminder: task "' . $task['title'] . '" needs attention';
        $mail->Body =
            "Hi " . $task['assignee_name'] . ",\n\n" .
            "This task has had no activity in over {$staleDays} days:\n\n" .
            "  " . $task['title'] . "\n" .
            (!empty($task['due_date']) ? "  Due: " . date('m/d/Y', strtotime($task['due_date'])) . "\n" : '') .
            $contractLine . "\n\n" .
            "View / update it here:\n$taskUrl\n";

        $mail->send();
        $sent++;

        $db->prepare("UPDATE tasks SET last_reminder_sent_at = NOW() WHERE task_id = ?")
           ->execute([$task['task_id']]);

        echo "[" . date('Y-m-d H:i:s') . "] Reminder sent for task #{$task['task_id']} to {$task['assignee_email']}\n";
    } catch (Exception $e) {
        $failed++;
        error_log('Stale task reminder failed for task #' . $task['task_id'] . ': ' . $e->getMessage());
        echo "[" . date('Y-m-d H:i:s') . "] FAILED task #{$task['task_id']}: " . $e->getMessage() . "\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Done. Sent: $sent, Failed: $failed\n";
