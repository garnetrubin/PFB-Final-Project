<?php
include 'includes/db.php';
session_start();

$guest_username = "GuestUser";

$check = $conn->query("SELECT id FROM users WHERE username = '$guest_username'");

if ($check->num_rows == 0) {
    $password = password_hash('guest123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password, age) VALUES ('$guest_username', '$password', 25)");
    $guest_id = $conn->insert_id;
} else {
    $row = $check->fetch_assoc();
    $guest_id = $row['id'];
}

$conn->query("DELETE FROM health_logs WHERE user_id = $guest_id");

$_SESSION['user_id'] = $guest_id;
$_SESSION['role'] = 'guest';
$_SESSION['is_guest'] = true;

header("Location: dashboard.php");
exit();
?>