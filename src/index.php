<?php
require_once 'functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task-name'])) {
        $taskName = $_POST['task-name'];
        if (!empty($taskName)) {
            addTask($taskName);
        }
    } elseif (isset($_POST['email'])) {
        $email = $_POST['email'];
        if (!empty($email)) {
            subscribeEmail($email);
            $subscriptionMessage = "A verification email has been sent to $email";
        }
    }
}

// Handle task actions via GET (for simplicity)
if (isset($_GET['action'])) {
    $taskId = $_GET['id'] ?? '';
    switch ($_GET['action']) {
        case 'complete':
            markTaskAsCompleted($taskId, true);
            break;
        case 'incomplete':
            markTaskAsCompleted($taskId, false);
            break;
        case 'delete':
            deleteTask($taskId);
            break;
    }
    header("Location: index.php");
    exit();
}

$tasks = getAllTasks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Scheduler</title>
    <style>
        .task-item.completed { text-decoration: line-through; opacity: 0.7; }
        .tasks-list { list-style: none; padding: 0; }
        .task-item { margin: 5px 0; padding: 5px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Task Scheduler</h1>
    
    <!-- Task Form -->
    <form method="post">
        <input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
        <button type="submit" id="add-task">Add Task</button>
    </form>
    
    <!-- Task List -->
   <ul class="tasks-list">
    <?php foreach ($tasks as $task): ?>
        <?php
            $isCompleted = isset($task['completed']) && $task['completed'];
            $taskId = isset($task['id']) ? (int)$task['id'] : 0;
            $taskName = isset($task['name']) ? htmlspecialchars($task['name']) : '';
        ?>
        <li class="task-item <?= $isCompleted ? 'completed' : '' ?>">
            <input type="checkbox" class="task-status"
                <?= $isCompleted ? 'checked' : '' ?>
                onclick="window.location.href='index.php?action=<?= $isCompleted ? 'incomplete' : 'complete' ?>&id=<?= $taskId ?>'">
            <?= $taskName ?>
            <button class="delete-task" onclick="window.location.href='index.php?action=delete&id=<?= $taskId ?>'">Delete</button>
        </li>
    <?php endforeach; ?>
</ul>

    
    <!-- Email Subscription Form -->
    <h2>Email Reminders</h2>
    <?php if (isset($subscriptionMessage)): ?>
        <p><?= $subscriptionMessage ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" required placeholder="Enter your email">
        <button id="submit-email">Subscribe</button>
    </form>
</body>
</html>