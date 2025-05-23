<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user name
$userStmt = $conn->prepare("SELECT username FROM users WHERE id = :id");
$userStmt->bindParam(':id', $user_id);
$userStmt->execute();
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$username = $user ? $user['username'] : 'User';

// Fetch tasks
$stmt = $conn->prepare("
    SELECT * FROM tasks 
    WHERE user_id = :user_id 
    ORDER BY 
        is_completed ASC,
        FIELD(priority, 'High', 'Medium', 'Low'),
        due_date ASC
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group tasks by due_date
$groupedTasks = [];
foreach ($tasks as $task) {
    $dateKey = $task['due_date'] ? date('Y-m-d', strtotime($task['due_date'])) : 'No Due Date';
    if (!isset($groupedTasks[$dateKey])) {
        $groupedTasks[$dateKey] = [];
    }
    $groupedTasks[$dateKey][] = $task;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TODO List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-100 to-blue-100 p-4 sm:p-6">
    
    <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-2xl p-4 sm:p-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 text-center mb-2">Welcome, <?= htmlspecialchars($username) ?></h1>

        <h2 class="text-lg sm:text-xl text-gray-600 text-center mb-6">Your TODO List</h2>

         

      <div class="flex flex-wrap justify-between gap-2 mb-4">
    <a href="add_task.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Add Task</a>


    <div class="flex flex-col sm:flex-row gap-2">
        <a href="profile_settings.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">Profile Settings</a>
        <a href="logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Logout</a>
    </div>
</div>



        <?php if (!empty($groupedTasks)): ?>
            <?php foreach ($groupedTasks as $date => $tasksForDate): ?>
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-blue-800 mb-4 border-b pb-1">
                        <?= $date === 'No Due Date' ? 'No Due Date' : date('l, F j, Y', strtotime($date)) ?>
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto border-collapse mb-4">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="px-4 py-2 text-left text-gray-700">Title</th>
                                    <th class="px-4 py-2 text-left text-gray-700">Description</th>
                                    <th class="px-4 py-2 text-left text-gray-700">Priority</th>
                                    <th class="px-4 py-2 text-left text-gray-700">Time</th>
                                    <th class="px-4 py-2 text-left text-gray-700">Status</th>
                                    <th class="px-4 py-2 text-left text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasksForDate as $task): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-2"><?= htmlspecialchars($task['title']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($task['description'] ?? 'No description') ?></td>
                                        <td class="px-4 py-2">
                                            <span class="inline-block px-2 py-1 rounded text-sm
                                                <?= $task['priority'] === 'High' ? 'bg-red-100 text-red-800' : 
                                                    ($task['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') ?>">
                                                <?= htmlspecialchars($task['priority']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <?= $task['due_date'] ? htmlspecialchars(date('H:i', strtotime($task['due_date']))) : 'No time' ?>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="inline-block px-2 py-1 rounded text-sm
                                                <?= $task['is_completed'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                <?= $task['is_completed'] ? 'Completed' : 'Pending' ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 flex gap-2">
                                            <a href="mark_complete.php?id=<?= $task['id'] ?>&status=<?= $task['is_completed'] ? 0 : 1 ?>" 
                                               class="px-3 py-1 rounded text-sm <?= $task['is_completed'] ? 'bg-gray-500 hover:bg-gray-600' : 'bg-green-500 hover:bg-green-600' ?> text-white">
                                                <?= $task['is_completed'] ? 'Mark Pending' : 'Mark Completed' ?>
                                            </a>
                                            <a href="edit_task.php?id=<?= $task['id'] ?>" 
                                               class="px-3 py-1 bg-yellow-500 text-white rounded text-sm hover:bg-yellow-600">Edit</a>
                                            <a href="delete_task.php?id=<?= $task['id'] ?>" 
                                               class="px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-500">No tasks found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
