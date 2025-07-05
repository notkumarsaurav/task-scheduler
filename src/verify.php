<?php
require_once 'functions.php';

$email = $_GET['email'] ?? '';
$code = $_GET['code'] ?? '';

if (!empty($email) && !empty($code)) {
    if (verifySubscription($email, $code)) {
        $message = "Email $email has been successfully verified!";
    } else {
        $message = "Invalid verification link.";
    }
} else {
    $message = "Missing verification parameters.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h1>Email Verification</h1>
    <p><?= $message ?></p>
    <p><a href="index.php">Return to Task Scheduler</a></p>
</body>
</html>