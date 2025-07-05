<?php
require_once 'functions.php';

$email = $_GET['email'] ?? '';

if (!empty($email)) {
    if (unsubscribeEmail($email)) {
        $message = "Email $email has been unsubscribed from task reminders.";
    } else {
        $message = "Email $email was not found in our subscribers list.";
    }
} else {
    $message = "Missing email parameter.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe</title>
</head>
<body>
    <h1>Unsubscribe</h1>
    <p><?= $message ?></p>
    <p><a href="index.php">Return to Task Scheduler</a></p>
</body>
</html>
