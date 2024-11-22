<?php
// Include necessary files
require_once 'db_connection.php';
require_once 'sidebar_function.php';
require_once 'config.php';
requireLogin();
requireAccess(basename(__FILE__));

if (!isset($_SESSION['user_id']) || !isset($_SESSION['2fa_verified']) || !$_SESSION['2fa_verified']) {
    header("Location: login.php");
    exit();
}

// Get the database connection
$pdo = getDBConnection();

// Get the current user's ID and role
$userId = getCurrentUserId();
$userRole = getCurrentUserRole();

// Fetch the current user's name
$userSql = "SELECT name FROM users WHERE id = :userId";
$userStmt = $pdo->prepare($userSql);
$userStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$userStmt->execute();
$userData = $userStmt->fetch(PDO::FETCH_ASSOC);
$currentUserName = $userData['name'];

// Function to get attendance records for the current month
function getMonthlyAttendance($pdo, $userId) {
    $sql = "SELECT 
            DATE(clock_in) as date,
            TIME_FORMAT(clock_in, '%H:%i:%s') as clock_in_time,
            TIME_FORMAT(clock_out, '%H:%i:%s') as clock_out_time,
            TIMEDIFF(clock_out, clock_in) as work_duration,
            break_duration
            FROM attendance 
            WHERE user_id = :userId 
            AND MONTH(clock_in) = MONTH(CURRENT_DATE())
            AND YEAR(clock_in) = YEAR(CURRENT_DATE())
            ORDER BY clock_in DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to check if user is already clocked in
function isUserClockedIn($pdo, $userId) {
    $sql = "SELECT id FROM attendance 
            WHERE user_id = :userId 
            AND DATE(clock_in) = CURRENT_DATE() 
            AND clock_out IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

// Handle Clock In/Out actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'clock_in' && !isUserClockedIn($pdo, $userId)) {
            $sql = "INSERT INTO attendance (user_id, clock_in) VALUES (:userId, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
        } elseif ($_POST['action'] === 'clock_out') {
            $sql = "UPDATE attendance 
                    SET clock_out = NOW(), 
                        break_duration = :breakDuration 
                    WHERE user_id = :userId 
                    AND DATE(clock_in) = CURRENT_DATE() 
                    AND clock_out IS NULL";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':breakDuration', $_POST['break_duration'], PDO::PARAM_INT);
            $stmt->execute();
        }
    }
}

$monthlyAttendance = getMonthlyAttendance($pdo, $userId);
$isClockedIn = isUserClockedIn($pdo, $userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .timer {
            font-size: 48px;
            font-weight: bold;
            font-family: monospace;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php echo getSidebar(); ?>
        <div class="flex-1 overflow-auto">
            <div class="container mx-auto p-6">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold">Attendance Dashboard</h1>
                    <p class="text-gray-600 mt-2">Hi <?php echo htmlspecialchars($currentUserName); ?>, mark your attendance here</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Attendance Timer Card -->
                    <div class="dashboard-card p-6">
                        <div class="text-center">
                            <div id="currentTime" class="timer mb-4">00:00:00</div>
                            <div class="text-sm text-gray-500 mb-4"><?php echo date('d-M-Y'); ?></div>
                            <div class="flex justify-center gap-4">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="clock_in">
                                    <button type="submit" 
                                            class="<?php echo $isClockedIn ? 'bg-gray-400' : 'bg-blue-600 hover:bg-blue-700'; ?> text-white px-6 py-2 rounded-md"
                                            <?php echo $isClockedIn ? 'disabled' : ''; ?>>
                                        Clock In
                                    </button>
                                </form>
                                <form method="POST" class="inline" id="clockOutForm">
                                    <input type="hidden" name="action" value="clock_out">
                                    <input type="hidden" name="break_duration" id="breakDuration" value="0">
                                    <button type="submit" 
                                            class="<?php echo !$isClockedIn ? 'bg-gray-400' : 'bg-red-600 hover:bg-red-700'; ?> text-white px-6 py-2 rounded-md"
                                            <?php echo !$isClockedIn ? 'disabled' : ''; ?>>
                                        Clock Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Stats Card -->
                    <div class="dashboard-card p-6">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>

                <!-- Attendance History Table -->
                <div class="dashboard-card p-6">
                    <h2 class="text-xl font-semibold mb-4">Attendance History</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2 text-left">Date</th>
                                    <th class="p-2 text-left">Clock In</th>
                                    <th class="p-2 text-left">Clock Out</th>
                                    <th class="p-2 text-left">Work Duration</th>
                                    <th class="p-2 text-left">Break Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthlyAttendance as $record): ?>
                                    <tr class="border-b">
                                        <td class="p-2"><?php echo htmlspecialchars($record['date']); ?></td>
                                        <td class="p-2"><?php echo htmlspecialchars($record['clock_in_time']); ?></td>
                                        <td class="p-2"><?php echo $record['clock_out_time'] ? htmlspecialchars($record['clock_out_time']) : '-'; ?></td>
                                        <td class="p-2"><?php echo $record['work_duration'] ? htmlspecialchars($record['work_duration']) : '-'; ?></td>
                                        <td class="p-2"><?php echo $record['break_duration'] ? htmlspecialchars($record['break_duration']) . ' min' : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toTimeString().split(' ')[0];
            document.getElementById('currentTime').textContent = timeString;
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Initialize attendance chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceData = <?php echo json_encode($monthlyAttendance); ?>;
        
        // Process data for chart
        const dates = attendanceData.map(record => record.date);
        const workHours = attendanceData.map(record => {
            if (record.work_duration) {
                const [hours, minutes] = record.work_duration.split(':');
                return parseFloat(hours) + parseFloat(minutes)/60;
            }
            return 0;
        });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Work Hours',
                    data: workHours,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours'
                        }
                    }
                }
            }
        });

        // Break duration tracking
        let breakStartTime = null;
        let totalBreakDuration = 0;

        function startBreak() {
            if (!breakStartTime) {
                breakStartTime = new Date();
            }
        }

        function endBreak() {
            if (breakStartTime) {
                const breakEndTime = new Date();
                const breakDuration = Math.round((breakEndTime - breakStartTime) / (1000 * 60)); // Convert to minutes
                totalBreakDuration += breakDuration;
                breakStartTime = null;
                document.getElementById('breakDuration').value = totalBreakDuration;
            }
        }

        // Update break duration when clocking out
        document.getElementById('clockOutForm').addEventListener('submit', function() {
            endBreak();
        });
    </script>
</body>
</html>


<!-- https://vapt.auriseg.org/human-resource/login.log -->
<!-- https://auth-db1671.hstgr.io/index.php?route=/database/structure&db=information_schema -->