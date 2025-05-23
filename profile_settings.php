<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$user_id = $_SESSION['user_id'];
$errors = [];
$success_name = '';
$success_password = '';

// Fetch current user info
$stmt = $conn->prepare("SELECT username, password FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$current_username = $user['username'] ?? '';

// Handle username update
if (isset($_POST['update_name'])) {
    $new_username = trim($_POST['new_username']);

    if (empty($new_username)) {
        $errors[] = "Username cannot be empty.";
    } elseif (strlen($new_username) < 2) {
        $errors[] = "Username must be at least 2 characters long.";
    } else {
        $update = $conn->prepare("UPDATE users SET username = :username WHERE id = :id");
        $update->bindParam(':username', $new_username);
        $update->bindParam(':id', $user_id);
        $update->execute();
        $success_name = "Username updated successfully.";
        $current_username = $new_username;
    }
}

// Handle password update
if (isset($_POST['update_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $errors[] = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $errors[] = "New password must be at least 6 characters.";
    } elseif ($new !== $confirm) {
        $errors[] = "New passwords do not match.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
        $update->bindParam(':password', $hashed);
        $update->bindParam(':id', $user_id);
        $update->execute();
        $success_password = "Password changed successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleForm(formType) {
            if (formType === 'username') {
                document.getElementById('usernameForm').style.display = 'block';
                document.getElementById('passwordForm').style.display = 'none';
            } else {
                document.getElementById('usernameForm').style.display = 'none';
                document.getElementById('passwordForm').style.display = 'block';
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-100 to-blue-100 p-4 flex justify-center items-center">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg">
        <h2 class="text-xl font-bold mb-4 text-center text-blue-700">Profile Settings</h2>

        <?php if (!empty($errors)): ?>
            <div class="mb-4 text-red-600">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success_name): ?>
            <p class="mb-4 text-green-600"><?= htmlspecialchars($success_name) ?></p>
        <?php endif; ?>

        <?php if ($success_password): ?>
            <p class="mb-4 text-green-600"><?= htmlspecialchars($success_password) ?></p>
        <?php endif; ?>

        <!-- Buttons to choose form -->
        <div class="mb-6 text-center">
            <button onclick="toggleForm('username')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition mb-2 w-full">Change Username</button>
            <button onclick="toggleForm('password')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition w-full">Change Password</button>
        </div>

        <!-- Change Username Form (Initially Hidden) -->
        <form method="POST" id="usernameForm" style="display: none;" class="mb-6">
            <h3 class="font-semibold text-gray-700 mb-2">Change Username</h3>
            <div class="mb-4">
                <label class="block mb-1 text-gray-700">Current Username</label>
                <input type="text" value="<?= htmlspecialchars($current_username) ?>" disabled class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block mb-1 text-gray-700">New Username</label>
                <input type="text" name="new_username" required class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
            <button type="submit" name="update_name" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Update Username</button>
        </form>

        <!-- Change Password Form (Initially Hidden) -->
        <form method="POST" id="passwordForm" style="display: none;">
            <h3 class="font-semibold text-gray-700 mb-2">Change Password</h3>
            <div class="mb-4">
                <label class="block mb-1 text-gray-700">Current Password</label>
                <input type="password" name="current_password" required class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block mb-1 text-gray-700">New Password</label>
                <input type="password" name="new_password" required class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block mb-1 text-gray-700">Confirm New Password</label>
                <input type="password" name="confirm_password" required class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
            <button type="submit" name="update_password" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded">Update Password</button>
        </form>

        <a href="index.php" class="block text-center text-blue-500 mt-6 hover:underline">← Back to Dashboard</a>
    </div>
</body>
</html>
