<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php'; 

define('TASKS_FILE', __DIR__.'/tasks.txt');
define('SUBSCRIBERS_FILE', __DIR__.'/subscribers.txt');
define('PENDING_SUBS_FILE', __DIR__.'/pending_subscriptions.txt');

function resolvePath($filename) {
    
    if (preg_match('/^[A-Za-z]:\\\\|^\//', $filename)) {
        return $filename; 
    }
    return __DIR__ . '/' . $filename; 
}

function getFileData($filename) {
    $fullPath = resolvePath($filename);
    if (!file_exists($fullPath)) {
        file_put_contents($fullPath, '[]');
        return [];
    }
    $content = file_get_contents($fullPath);
    return json_decode($content, true) ?? [];
}

function saveFileData($filename, $data) {
    $fullPath = resolvePath($filename);
    file_put_contents($fullPath, json_encode($data, JSON_PRETTY_PRINT));
}



function addTask($task_name) {
    $tasks = getFileData(TASKS_FILE);
    
    // Check for duplicates
    foreach ($tasks as $task) {
        if (strtolower($task['name']) === strtolower($task_name)) {
            return false;
        }
    }
    
    $newTask = [
        'id' => uniqid(),
        'name' => htmlspecialchars(trim($task_name)),
        'completed' => false
    ];
    
    $tasks[] = $newTask;
    saveFileData(TASKS_FILE, $tasks);
    return true;
}

function getAllTasks() {
    return getFileData(TASKS_FILE);
}

function markTaskAsCompleted($task_id, $is_completed) {
    $tasks = getFileData(TASKS_FILE);
    
    foreach ($tasks as &$task) {
        if ((int)$task['id'] === (int)$task_id) {
            $task['completed'] = (bool)$is_completed;
            saveFileData(TASKS_FILE, $tasks);
            return true;
           
        }
    }
    
    
    return false;
}

function deleteTask($task_id)
 {
    $tasks = getFileData(TASKS_FILE);
    
    $updatedTasks = [];

    foreach ($tasks as $task) {
        if (isset($task['id']) && (int)$task['id'] !== (int)$task_id) {
            $updatedTasks[] = $task;
        }
    }

    saveFileData(TASKS_FILE, array_values($updatedTasks));
}



function generateVerificationCode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function subscribeEmail($email) {
    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    if (!$email) return false;
    
    $subscribers = getFileData(SUBSCRIBERS_FILE);
    if (in_array($email, $subscribers)) return true;
    
    $pendingSubs = getFileData(PENDING_SUBS_FILE);
    if (isset($pendingSubs[$email])) return false;
    
    $code = generateVerificationCode();
    $pendingSubs[$email] = [
        'code' => $code,
        'timestamp' => time()
    ];
    
    saveFileData(PENDING_SUBS_FILE, $pendingSubs);
    
    // Send verification email
   $baseURL = "http://" . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
$verificationLink = $baseURL . "/verify.php?email=" . urlencode($email) . "&code=$code";

    $subject = "Verify subscription to Task Planner";
    $message = '<p>Click the link below to verify your subscription to Task Planner:</p>';
    $message .= '<p><a id="verification-link" href="' . $verificationLink . '">Verify Subscription</a></p>';
    
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return sendEmail($email, $subject, $message);
}

function verifySubscription($email, $code) {
    $pendingSubs = getFileData(PENDING_SUBS_FILE);
    
    if (!isset($pendingSubs[$email]) || $pendingSubs[$email]['code'] !== $code) {
        return false;
    }
    
    $subscribers = getFileData(SUBSCRIBERS_FILE);
    if (!in_array($email, $subscribers)) {
        $subscribers[] = $email;
        saveFileData(SUBSCRIBERS_FILE, $subscribers);
    }
    
    unset($pendingSubs[$email]);
    saveFileData(PENDING_SUBS_FILE, $pendingSubs);
    
    return true;
}

function unsubscribeEmail($email) {
    $subscribers = getFileData(SUBSCRIBERS_FILE);
    $key = array_search($email, $subscribers);
    
    if ($key !== false) {
        unset($subscribers[$key]);
        saveFileData(SUBSCRIBERS_FILE, array_values($subscribers));
        return true;
    }
    
    return false;
}

function sendTaskReminders() {
    $subscribers = getFileData(SUBSCRIBERS_FILE);
    $tasks = getFileData(TASKS_FILE);
    
    $pendingTasks = array_filter($tasks, function($task) {
        return !$task['completed'];
    });
    
    if (empty($pendingTasks)) return;
    
    foreach ($subscribers as $email) {
        sendTaskEmail($email, $pendingTasks);
    }
}

function sendTaskEmail($email, $pending_tasks) {
    $subject = "Task Planner - Pending Tasks Reminder";
    
    $message = '<h2>Pending Tasks Reminder</h2>';
    $message .= '<p>Here are the current pending tasks:</p><ul>';
    
    foreach ($pending_tasks as $task) {
        $message .= '<li>' . htmlspecialchars($task['name']) . '</li>';
    }
    
    $message .= '</ul>';
    
    $unsubscribeLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/unsubscribe.php?email=" . urlencode($email);
    $message .= '<p><a id="unsubscribe-link" href="' . $unsubscribeLink . '">Unsubscribe from notifications</a></p>';
    
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return sendEmail($email, $subject, $message);
}
function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'pagescratcher@gmail.com'; 
        $mail->Password   = 'dhll lgzx ddzc epbm';    
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('pagescratcher@gmail.com', 'Task Planner');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

?>