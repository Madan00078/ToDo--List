<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    // Fetch user's current hashed password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current, $user['password'])) {
        $errors[] = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $errors[] = "New password must be at least 6 characters.";
    } elseif ($new !== $confirm) {
        $errors[] = "New passwords do not match.";
    } else {
        // Update password
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = :pass WHERE id = :id");
        $update->bindParam(':pass', $hashed);
        $update->bindParam(':id', $user_id);
        $update->execute();
        $success = "Password changed successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-100 to-blue-100 p-4 flex justify-center items-center">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <h2 class="text-xl font-bold mb-4 text-center text-blue-700">Change Password</h2>

        <?php if (!empty($errors)): ?>
            <div class="mb-4 text-red-600">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($success): ?>
            <p class="mb-4 text-green-600"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST">
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
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Update Password</button>
            <a href="index.php" class="block text-center text-blue-500 mt-4 hover:underline">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
