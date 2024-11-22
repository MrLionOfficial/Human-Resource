<?php
// Include necessary files
require_once 'db_connection.php';
require_once 'sidebar_function.php';
require_once 'config.php';
requireLogin();
requireAccess(basename(__FILE__));

// Get the database connection
$pdo = getDBConnection();

// Get current user's ID
$userId = getCurrentUserId();

// Get current month and year (default to current if not specified)
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Function to get leave statistics
function getLeaveStatistics($pdo, $userId) {
    $sql = "SELECT 
            leave_type,
            COUNT(*) as count,
            SUM(duration) as total_days
            FROM leaves 
            WHERE user_id = :userId 
            AND YEAR(start_date) = YEAR(CURRENT_DATE)
            GROUP BY leave_type";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get calendar events
function getCalendarEvents($pdo, $userId, $month, $year) {
    $sql = "SELECT 
            l.*,
            CASE 
                WHEN l.leave_type = 'annual' THEN 'My Leave'
                WHEN l.leave_type = 'sick' THEN 'My Leave'
                WHEN l.status = 'pending' THEN 'My Leave Request'
                ELSE l.leave_type 
            END as event_type
            FROM leaves l
            WHERE (user_id = :userId OR leave_type = 'holiday')
            AND MONTH(start_date) = :month 
            AND YEAR(start_date) = :year";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    
    $events = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $startDate = strtotime($row['start_date']);
        $endDate = strtotime($row['end_date']);
        
        // Add event for each day in the date range
        for ($date = $startDate; $date <= $endDate; $date = strtotime('+1 day', $date)) {
            $day = date('j', $date);
            if (!isset($events[$day])) {
                $events[$day] = [];
            }
            $events[$day][] = $row;
        }
    }
    return $events;
}

$leaveStats = getLeaveStatistics($pdo, $userId);
$calendarEvents = getCalendarEvents($pdo, $userId, $month, $year);

// Calculate days in month and first day
$daysInMonth = date('t', strtotime("$year-$month-01"));
$firstDayOfMonth = date('w', strtotime("$year-$month-01"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .calendar-header {
            background-color: #4DA3FF;
            color: white;
        }
        .calendar-cell {
            aspect-ratio: 1;
            min-height: 100px;
        }
        .event-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .event-indicator {
            position: absolute;
            bottom: 4px;
            left: 4px;
            display: flex;
            gap: 2px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto p-6">
        <!-- Calendar Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Calendar</h1>
            <div class="flex items-center gap-4">
                <a href="?month=<?php echo $month-1 ?>&year=<?php echo $year ?>" class="text-gray-600 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="text-xl font-semibold">
                    <?php echo date('F Y', strtotime("$year-$month-01")); ?>
                </span>
                <a href="?month=<?php echo $month+1 ?>&year=<?php echo $year ?>" class="text-gray-600 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Calendar Grid -->
            <div class="flex-1 bg-white rounded-lg shadow">
                <!-- Days Header -->
                <div class="grid grid-cols-7 calendar-header rounded-t-lg">
                    <div class="p-4 text-center">Sunday</div>
                    <div class="p-4 text-center">Monday</div>
                    <div class="p-4 text-center">Tuesday</div>
                    <div class="p-4 text-center">Wednesday</div>
                    <div class="p-4 text-center">Thursday</div>
                    <div class="p-4 text-center">Friday</div>
                    <div class="p-4 text-center">Saturday</div>
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-7">
                    <?php
                    // Previous month's days
                    for ($i = 0; $i < $firstDayOfMonth; $i++) {
                        echo '<div class="calendar-cell border p-2 relative bg-gray-50">';
                        echo '<span class="text-gray-400 text-sm">' . date('j', strtotime('-' . ($firstDayOfMonth - $i) . ' days', strtotime("$year-$month-01"))) . '</span>';
                        echo '</div>';
                    }

                    // Current month's days
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        echo '<div class="calendar-cell border p-2 relative">';
                        echo '<span class="' . (date('Y-m-d') === "$year-$month-" . sprintf("%02d", $day) ? 'font-bold text-blue-600' : '') . '">' . $day . '</span>';
                        
                        // Display events for this day
                        if (isset($calendarEvents[$day])) {
                            echo '<div class="event-indicator">';
                            foreach ($calendarEvents[$day] as $event) {
                                $color = match($event['event_type']) {
                                    'My Leave' => 'bg-blue-500',
                                    'My Leave Request' => 'bg-yellow-500',
                                    'Team Leave' => 'bg-purple-500',
                                    'Holiday' => 'bg-red-500',
                                    'Week Off' => 'bg-green-500',
                                    default => 'bg-gray-500'
                                };
                                echo "<div class='event-dot $color'></div>";
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                    }

                    // Next month's days
                    $remainingCells = 42 - ($daysInMonth + $firstDayOfMonth); // 42 = 6 rows × 7 days
                    for ($i = 1; $i <= $remainingCells; $i++) {
                        echo '<div class="calendar-cell border p-2 relative bg-gray-50">';
                        echo '<span class="text-gray-400 text-sm">' . $i . '</span>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Filter Sidebar -->
            <div class="w-64 bg-white p-4 rounded-lg shadow">
                <h2 class="font-semibold mb-4">Filter Events</h2>
                <div class="space-y-3">
                    <?php
                    $eventTypes = [
                        'My Leave' => 0,
                        'My Leave Request' => 0,
                        'Notify' => 0,
                        'Team Leave' => 0,
                        'Holiday' => 0,
                        'Week Off' => 0
                    ];

                    // Update counts from leave statistics
                    foreach ($leaveStats as $stat) {
                        if (isset($eventTypes[$stat['leave_type']])) {
                            $eventTypes[$stat['leave_type']] = $stat['total_days'];
                        }
                    }

                    foreach ($eventTypes as $type => $days) {
                        ?>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       class="form-checkbox text-blue-600" 
                                       name="event_filter[]" 
                                       value="<?php echo htmlspecialchars($type); ?>" 
                                       checked>
                                <span class="ml-2 text-sm"><?php echo htmlspecialchars($type); ?></span>
                            </label>
                            <span class="text-sm text-gray-500"><?php echo $days; ?> Day(s)</span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Event filter functionality
        document.querySelectorAll('input[name="event_filter[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const eventType = this.value;
                const isChecked = this.checked;
                
                document.querySelectorAll('.event-dot').forEach(dot => {
                    if (dot.dataset.eventType === eventType) {
                        dot.style.display = isChecked ? 'block' : 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>