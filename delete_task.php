<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}
require 'db.php';

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = :id");
    $stmt->bindParam(':id', $task_id);
    $stmt->execute();
}

header("Location: index.php");
?>