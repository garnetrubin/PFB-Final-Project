<?php
include 'includes/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $age = intval($_POST['age']); 
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
    if ($check->num_rows > 0) {
        $error = "Username already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, age) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $username, $password, $age);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Error registering user.";
        }
    }
}
include 'includes/header.php';
?>

<div class="card" style="max-width: 400px; margin: 0 auto;">
    <img src="asset/HealthTrack.png
    " alt="HealthTrack Logo" class="app-logo-small" width="100" style="display: block; margin: 0 auto 20px auto;">
    
    <h2 style="text-align: center;">Create Account</h2>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required placeholder="Choose a username">
        
        <label>Age</label>
        <input type="number" name="age" required placeholder="e.g. 21" min="12" max="100">

        <label>Password</label>
        <input type="password" name="password" required placeholder="Choose a password">
        
        <button type="submit">Sign Up</button>
    </form>
    <p style="text-align: center; margin-top: 15px;">
        Already have an account? <a href="index.php" style="color: #27ae60;">Login here</a>
    </p>
</div>
