<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

$stmt = $conn->prepare("SELECT username FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$current_name = $user ? $user['username'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['new_name']);

    if (empty($new_name)) {
        $error = "Name cannot be empty.";
    } elseif (strlen($new_name) < 2) {
        $error = "Name must be at least 2 characters long.";
    } else {
        $update = $conn->prepare("UPDATE users SET username = :name WHERE id = :id");
        $update->bindParam(':name', $new_name);
        $update->bindParam(':id', $user_id);
        $update->execute();
        $success = "Name updated successfully.";
        $current_name = $new_name;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Name</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-100 to-blue-100 p-4 flex justify-center items-center">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <h2 class="text-xl font-bold mb-4 text-center text-blue-700">Change User Name</h2>

        <?php if ($error): ?>
            <p class="mb-4 text-red-600"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p class="mb-4 text-green-600"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block mb-1 text-gray-700">Current Name</label>
                <input type="text" value="<?= htmlspecialchars($current_name) ?>" disabled class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block mb-1 text-gray-700">New User Name</label>
                <input type="text" name="new_name" required class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Update Name</button>
            <a href="index.php" class="block text-center text-blue-500 mt-4 hover:underline">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
