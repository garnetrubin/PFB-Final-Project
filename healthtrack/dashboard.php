<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$age_query = $conn->query("SELECT age FROM users WHERE id = $user_id");
$user_data = $age_query->fetch_assoc();
$user_age = $user_data['age'];

$water_goal = ($user_age < 18) ? 2.0 : 2.5;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
$latest_log = null; 

while($row = $result->fetch_assoc()) {
    $dates[] = $row['log_date'];
    $sleep_data[] = $row['sleep_hours'];
    $logs[] = $row;
    $latest_log = $row; 
}
$json_dates = json_encode($dates);
$json_sleep = json_encode($sleep_data);

include 'includes/header.php';
?>

<h2>My Health Dashboard</h2>

<?php if ($latest_log): ?>
    <div class="card" style="border-left: 5px solid #27ae60;">
        <h3>💡 Health Insights (Based on Age: <?php echo $user_age; ?>)</h3>
        
        <?php if ($latest_log['sleep_hours'] < 7): ?>
            <div style="background: #fadbd8; padding: 10px; margin-bottom: 10px; border-radius: 5px; color: #c0392b;">
                <strong>⚠️ Low Sleep Detected (<?php echo $latest_log['sleep_hours']; ?> hrs):</strong> 
                You are sleeping less than the recommended 7 hours.
                <br><em>Consequences:</em> Risk of hair fall, reduced focus, and weakened immune system.
                <br><strong>Advice:</strong> Try to sleep 30 mins earlier tonight. Avoid screens before bed.
            </div>

        <?php elseif ($latest_log['sleep_hours'] > 9): ?>
            <div style="background: #f9e79f; padding: 10px; margin-bottom: 10px; border-radius: 5px; color: #d35400;">
                <strong>⚠️ Excessive Sleep Detected (<?php echo $latest_log['sleep_hours']; ?> hrs):</strong> 
                Sleeping more than 9 hours can disrupt your body clock.
                <br><em>Consequences:</em> Headaches, muscle aches, memory problems, and daytime fatigue.
                <br><strong>Advice:</strong> Stick to a consistent wake-up time, even on weekends.
            </div>

        <?php else: ?>
            <div style="background: #d5f5e3; padding: 10px; margin-bottom: 10px; border-radius: 5px; color: #1e8449;">
                <strong>✅ Great Sleep:</strong> You are in the healthy range (7-9 hours). This improves memory and mood.
            </div>
        <?php endif; ?>

        <?php if ($latest_log['water_liters'] < $water_goal): ?>
            <div style="background: #f9e79f; padding: 10px; border-radius: 5px; color: #d35400;">
                <strong>⚠️ Low Hydration (<?php echo $latest_log['water_liters']; ?> L):</strong> 
                For your age (<?php echo $user_age; ?>), you should target <?php echo $water_goal; ?> Liters.
                <br><em>Consequences:</em> Headaches, dry skin, and kidney strain.
                <br><strong>Advice:</strong> Keep a water bottle on your desk. Drink a glass right now!
            </div>

        <?php elseif ($latest_log['water_liters'] > 4.0): ?>
            <div style="background: #fadbd8; padding: 10px; border-radius: 5px; color: #c0392b;">
                <strong>⚠️ Possible Overhydration (<?php echo $latest_log['water_liters']; ?> L):</strong> 
                You are drinking significantly more than the daily recommendation.
                <br><em>Consequences:</em> Kidney overload, electrolyte imbalance, and low sodium levels.
                <br><strong>Advice:</strong> Drink only when you feel thirsty to avoid stressing your kidneys.
            </div>

        <?php else: ?>
            <div style="background: #d5f5e3; padding: 10px; border-radius: 5px; color: #1e8449;">
                <strong>✅ Well Hydrated:</strong> Excellent job meeting your water goal!
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
                    <button onclick="confirmDelete(<?php echo $row['id']; ?>)" style="background: #e74c3c; padding: 5px 10px; font-size: 0.8rem;">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">No logs found. Add one above!</td>
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