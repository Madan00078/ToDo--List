<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}
require 'db.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $task_id = $_GET['id'];
    $status = $_GET['status'];
    $stmt = $conn->prepare("UPDATE tasks SET is_completed = :status WHERE id = :id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $task_id);
    $stmt->execute();
}

header("Location: index.php");
?>
