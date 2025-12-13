<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_guest = isset($_SESSION['is_guest']) && $_SESSION['is_guest'];

$age_query = $conn->query("SELECT age FROM users WHERE id = $user_id");
$user_data = $age_query->fetch_assoc();
$user_age = $user_data['age'];
$water_goal = ($user_age < 18) ? 2.0 : 2.5;

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$is_guest) {
    $date = $_POST['date'];
    $sleep = $_POST['sleep'];
    $water = $_POST['water'];
    $mood = $_POST['mood'];

    $stmt = $conn->prepare("INSERT INTO health_logs (user_id, log_date, sleep_hours, water_liters, mood) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdds", $user_id, $date, $sleep, $water, $mood);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php"); 
        exit();
    }
}

$sql = "SELECT * FROM health_logs WHERE user_id = $user_id ORDER BY log_date ASC";
$result = $conn->query($sql);

$dates = [];
$sleep_data = [];
$logs = []; 

while($row = $result->fetch_assoc()) {
    $dates[] = $row['log_date'];
    $sleep_data[] = $row['sleep_hours'];
    $logs[] = $row;
}
$json_dates = json_encode($dates);
$json_sleep = json_encode($sleep_data);

$avg_query = $conn->query("SELECT AVG(sleep_hours) as avg_sleep, AVG(water_liters) as avg_water, COUNT(*) as total_days FROM health_logs WHERE user_id = $user_id");
$stats = $avg_query->fetch_assoc();

$avg_sleep = round($stats['avg_sleep'], 1);
$avg_water = round($stats['avg_water'], 1);
$total_days = $stats['total_days'];
$has_data = $total_days > 0;

include 'includes/header.php';
?>

<h2>My Health Dashboard <?php if($is_guest) echo "(Guest Mode)"; ?></h2>

<?php if ($is_guest): ?>
    <div style="background: #e8f8f5; border: 1px solid #27ae60; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
        <strong>👋 Welcome Guest!</strong><br>
        You are in read-only mode. <a href="import.php" style="color: #d35400; font-weight: bold;">Try the CSV Import Tool</a> to see the average calculations in action!
    </div>
<?php endif; ?>

<?php if ($has_data && !$is_guest): ?>
    <div class="card" style="border-left: 5px solid #2980b9;">
        <h3>📊 Monthly Habit Analysis (Based on <?php echo $total_days; ?> days)</h3>
        
        <p style="margin-bottom: 20px; color: #7f8c8d;">
            <em>These insights are based on your <strong>average</strong> habits, not just today.</em>
        </p>

        <?php if ($avg_sleep < 7): ?>
            <div style="background: #fadbd8; padding: 10px; margin-bottom: 10px; border-radius: 5px; color: #c0392b;">
                <strong>⚠️ Chronic Sleep Deprivation (Avg: <?php echo $avg_sleep; ?> hrs):</strong> 
                On average, you are not meeting the 7-hour goal.
                <br><em>Risk:</em> Long-term impact on heart health and cognitive decline.
                <br><strong>Advice:</strong> Your sleep debt is accumulating. Try to add 1 extra hour of sleep every weekend to recover.
            </div>

        <?php elseif ($avg_sleep > 9): ?>
            <div style="background: #f9e79f; padding: 10px; margin-bottom: 10px; border-radius: 5px; color: #d35400;">
                <strong>⚠️ Oversleeping Trend (Avg: <?php echo $avg_sleep; ?> hrs):</strong> 
                Your average shows a tendency to oversleep.
                <br><em>Risk:</em> Associated with lethargy and metabolic issues.
                <br><strong>Advice:</strong> Set a strict alarm clock for the same time every day.
            </div>

        <?php else: ?>
            <div style="background: #d5f5e3; padding: 10px; margin-bottom: 10px; border-radius: 5px; color: #1e8449;">
                <strong>✅ Healthy Sleep Pattern (Avg: <?php echo $avg_sleep; ?> hrs):</strong> 
                Your sleep habits are consistent and healthy! Keep it up.
            </div>
        <?php endif; ?>

        <?php if ($avg_water < $water_goal): ?>
            <div style="background: #f9e79f; padding: 10px; border-radius: 5px; color: #d35400;">
                <strong>⚠️ Generally Dehydrated (Avg: <?php echo $avg_water; ?> L):</strong> 
                Your average intake is below the goal of <?php echo $water_goal; ?> L.
                <br><em>Risk:</em> Chronic dehydration can lead to kidney stones and poor skin health.
                <br><strong>Advice:</strong> Drink a glass of water before every meal to boost your average.
            </div>

        <?php elseif ($avg_water > 4.0): ?>
            <div style="background: #fadbd8; padding: 10px; border-radius: 5px; color: #c0392b;">
                <strong>⚠️ Excessive Hydration (Avg: <?php echo $avg_water; ?> L):</strong> 
                Your average intake is remarkably high.
                <br><em>Risk:</em> Unnecessary strain on kidneys.
                <br><strong>Advice:</strong> Ensure you are balancing this with electrolyte intake.
            </div>

        <?php else: ?>
            <div style="background: #d5f5e3; padding: 10px; border-radius: 5px; color: #1e8449;">
                <strong>✅ Hydration Master (Avg: <?php echo $avg_water; ?> L):</strong> 
                You consistently meet your hydration needs!
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card">
    <h3>Sleep Trends</h3>
    <div class="chart-box">
        <canvas id="healthChart"></canvas>
    </div>
</div>

<?php if (!$is_guest): ?>
<div class="card">
    <h3>Add Today's Log</h3>
    <form method="POST">
        <label>Date</label>
        <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div>
                <label>Sleep (Hours)</label>
                <input type="number" step="0.1" name="sleep" required placeholder="e.g. 7.5">
            </div>
            <div>
                <label>Water (Liters)</label>
                <input type="number" step="0.1" name="water" required placeholder="e.g. 2.0">
            </div>
        </div>

        <label>Mood</label>
        <select name="mood">
            <option value="Happy">Happy</option>
            <option value="Neutral">Neutral</option>
            <option value="Stressed">Stressed</option>
            <option value="Tired">Tired</option>
        </select>

        <button type="submit">Save Log</button>
    </form>
</div>
<?php endif; ?>

<h3>History</h3>
<div style="overflow-x: auto;">
    <table>
        <tr>
            <th>Date</th>
            <th>Sleep</th>
            <th>Water</th>
            <th>Mood</th>
            <th>Action</th>
        </tr>
        <?php if (count($logs) > 0): ?>
            <?php foreach(array_reverse($logs) as $row): ?>
            <tr>
                <td><?php echo date('M d, Y', strtotime($row['log_date'])); ?></td>
                <td><?php echo $row['sleep_hours']; ?> hrs</td>
                <td><?php echo $row['water_liters']; ?> L</td>
                <td><?php echo $row['mood']; ?></td>
                <td>
                    <?php if (!$is_guest): ?>
                    <button onclick="confirmDelete(<?php echo $row['id']; ?>)" style="background: #e74c3c; padding: 5px 10px; font-size: 0.8rem;">Delete</button>
                    <?php else: ?>
                    <span style="color: #95a5a6; font-size: 0.8rem;">Read-only</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">No logs found. <?php if($is_guest) echo "Try importing CSV!"; else echo "Add one above!"; ?></td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<script>
    const ctx = document.getElementById('healthChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo $json_dates; ?>, 
            datasets: [{
                label: 'Sleep (Hours)',
                data: <?php echo $json_sleep; ?>,
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.2)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>