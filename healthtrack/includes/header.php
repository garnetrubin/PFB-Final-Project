<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthTrack - SDG 3</title>
    
    <link rel="stylesheet" href="style.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <script src="script.js" defer></script>
</head>
<body>
    <nav>
        <div class="logo" style="display: flex; align-items: center;">
            <img src="asset/HealthTrack1.png" alt="Logo" style="height: 40px; margin-right: 10px; border-radius: 5px;">
            HealthTrack
        </div>

        <div class="links">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="index.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container">