<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

$errors = [];
$task = null;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$task_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $task_id, 'user_id' => $user_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$task) {
        $errors[] = 'Invalid task ID or you do not have permission to edit this task.';
    }
} catch (PDOException $e) {
    $errors[] = 'Failed to fetch task. Please try again later.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_task'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $submitted_task_id = $_POST['task_id'] ?? '';

    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    if (!in_array($priority, ['High', 'Medium', 'Low'])) {
        $errors[] = 'Invalid priority selected';
    }
    if (empty($due_date) || !strtotime($due_date)) {
        $errors[] = 'Valid due date and time is required';
    }
    if ($submitted_task_id !== $task_id || !is_numeric($submitted_task_id)) {
        $errors[] = 'Invalid task ID';
    }

    // If no errors and task exists, update the task in the database
    if (empty($errors) && $task) {
        try {
            $stmt = $conn->prepare("
                UPDATE tasks 
                SET title = :title, description = :description, priority = :priority, due_date = :due_date 
                WHERE id = :id AND user_id = :user_id
            ");
            $stmt->execute([
                'title' => $title,
                'description' => $description ?: null,
                'priority' => $priority,
                'due_date' => $due_date,
                'id' => $task_id,
                'user_id' => $user_id
            ]);
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to update task. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Edit Task</h1>
        <div class="mb-3">
            <a href="index.php" class="btn btn-secondary">Back to Tasks</a>
        </div>
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php if ($task): ?>
        <form method="POST">
            <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id']) ?>">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label for="priority" class="form-label">Priority</label>
                <select class="form-select" id="priority" name="priority">
                    <option value="High" <?= $task['priority'] === 'High' ? 'selected' : '' ?>>High</option>
                    <option value="Medium" <?= $task['priority'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="Low" <?= $task['priority'] === 'Low' ? 'selected' : '' ?>>Low</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date & Time</label>
                <input type="datetime-local" class="form-control" id="due_date" name="due_date" 
                       value="<?= $task['due_date'] ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($task['due_date']))) : '' ?>" required>
            </div>
            <button type="submit" name="edit_task" class="btn btn-primary">Update Task</button>
        </form>
        <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Task not found or invalid task ID.
        </div>
        <?php endif; ?>
    </div>
</body>
</html>