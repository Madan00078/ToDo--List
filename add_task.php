<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

$errors = [];
$title = $description = $priority = $due_date = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    if (!in_array($priority, ['High', 'Medium', 'Low'])) {
        $errors[] = 'Invalid priority selected';
    }
    if (empty($due_date) || !strtotime($due_date)) {
        $errors[] = 'Valid due date and time is required';
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO tasks (user_id, title, description, priority, due_date)
                VALUES (:user_id, :title, :description, :priority, :due_date)
            ");
            $stmt->execute([
                'user_id' => $user_id,
                'title' => $title,
                'description' => $description ?: null,
                'priority' => $priority,
                'due_date' => $due_date
            ]);
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to add task. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Add Task</h1>
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($description) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="priority" class="form-label">Priority</label>
                <select class="form-select" id="priority" name="priority">
                    <option value="High" <?= $priority === 'High' ? 'selected' : '' ?>>High</option>
                    <option value="Medium" <?= $priority === 'Medium' || empty($priority) ? 'selected' : '' ?>>Medium</option>
                    <option value="Low" <?= $priority === 'Low' ? 'selected' : '' ?>>Low</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date & Time</label>
                <input type="datetime-local" class="form-control" id="due_date" name="due_date" value="<?= htmlspecialchars($due_date) ?>" required>
            </div>
            <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
        </form>
    </div>
</body>
</html>
