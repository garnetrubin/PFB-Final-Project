<?php
include 'includes/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            header("Location: dashboard.php");
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
include 'includes/header.php';
?>

<div class="landing-grid">
    
    <div class="landing-info">
<img src="asset/HealthTrack.png" alt="HealthTrack Logo" class="app-logo" width="300">
        
        <h1>Welcome to HealthTrack</h1>
        <p class="tagline">Empowering your journey to a healthier lifestyle.</p>
        
        <div class="info-section">
            <h3>About Us</h3>
            <p>HealthTrack is a web application designed to help you monitor your daily habits. We believe that small changes in sleep, hydration, and mood tracking can lead to massive improvements in overall life quality.</p>
        </div>

        <div class="info-section">
            <h3>Our Mission: SDG 3</h3>
            <p>We are proudly aligned with the United Nations <strong>Sustainable Development Goal 3: Good Health and Well-being</strong>.</p>
            <ul>
                <li><strong>Mental Health:</strong> Track your mood patterns.</li>
                <li><strong>Physical Health:</strong> Monitor hydration and sleep.</li>
                <li><strong>Awareness:</strong> Visual data helps you make informed decisions.</li>
            </ul>
        </div>
    </div>

    <div class="login-box">
        <div class="card">           
            <h2>Login</h2>
            <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
            
            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" required>
                
                <label>Password</label>
                <input type="password" name="password" required>
                
                <button type="submit">Login</button>
            </form>
            
            <p style="margin-top: 15px; font-size: 0.9rem;">
                New here? <a href="register.php" style="color: #27ae60; font-weight: bold;">Create an account</a>
            </p>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>