<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";
if (isset($_POST['import'])) {
    $filename = "dummy_data.csv";
    $user_id = $_SESSION['user_id'];

    if (($handle = fopen($filename, "r")) !== FALSE) {
        fgetcsv($handle);
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $date = $data[0];
            $sleep = $data[1];
            $water = $data[2];
            $mood = $data[3];

            $stmt = $conn->prepare("INSERT INTO health_logs (user_id, log_date, sleep_hours, water_liters, mood) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isdds", $user_id, $date, $sleep, $water, $mood);
            $stmt->execute();
            $count++;
        }
        fclose($handle);
        $message = "Success! Imported $count rows of dummy data.";
    } else {
        $message = "Error: Could not open $filename. Make sure it is in the folder.";
    }
}

if (isset($_POST['delete_all'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("DELETE FROM health_logs WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $message = "All your health logs have been deleted.";
    } else {
        $message = "Error deleting data.";
    }
}

include 'includes/header.php';
?>

<div class="card" style="max-width: 500px; margin: 40px auto; text-align: center;">
    <h2>CSV Tools</h2>
    <p>Manage your health data for testing purposes.</p>
    
    <?php if ($message): ?>
        <p style="color: #2c3e50; font-weight: bold; background: #ecf0f1; padding: 10px; border-radius: 4px;"><?php echo $message; ?></p>
        <a href="dashboard.php" style="display: block; margin-top: 10px;">Go back to Dashboard</a>
        <br>
    <?php endif; ?>

    <form method="POST" style="margin-bottom: 20px;">
        <button type="submit" name="import" style="background: #e67e22; width: 100%;">Import Dummy Data (CSV)</button>
    </form>

    <form method="POST" onsubmit="return confirm('Are you sure? This will delete ALL your history.');">
        <button type="submit" name="delete_all" style="background: #c0392b; width: 100%;">Clear All My Data</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>