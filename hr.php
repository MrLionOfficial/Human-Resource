<?php
// File to store employee time data
$dataFile = 'employee_times.txt';

// Function to save employee time data
function saveEmployeeTime($name, $date, $checkInTime, $checkOutTime) {
    global $dataFile;
    $data = "$name|$date|$checkInTime|$checkOutTime\n";
    file_put_contents($dataFile, $data, FILE_APPEND);
}

// Function to get all employee time data
function getEmployeeTimes() {
    global $dataFile;
    if (file_exists($dataFile)) {
        return file($dataFile, FILE_IGNORE_NEW_LINES);
    }
    return [];
}

// Function to generate calendar
function generateCalendar($year, $month) {
    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $daysInMonth = date('t', $firstDay);
    $dayOfWeek = date('w', $firstDay);

    $calendar = "<table class='calendar'>";
    $calendar .= "<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>";

    $currentDay = 1;
    $calendar .= "<tr>";

    for ($i = 0; $i < $dayOfWeek; $i++) {
        $calendar .= "<td></td>";
    }

    while ($currentDay <= $daysInMonth) {
        if ($dayOfWeek == 7) {
            $dayOfWeek = 0;
            $calendar .= "</tr><tr>";
        }

        $today = (date('Y') == $year && date('m') == $month && date('d') == $currentDay) ? "class='today'" : "";
        $calendar .= "<td $today>$currentDay</td>";

        $currentDay++;
        $dayOfWeek++;
    }

    while ($dayOfWeek < 7) {
        $calendar .= "<td></td>";
        $dayOfWeek++;
    }

    $calendar .= "</tr></table>";

    return $calendar;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $date = $_POST['date'] ?? '';
    $checkInTime = $_POST['check_in_time'] ?? '';
    $checkOutTime = $_POST['check_out_time'] ?? '';

    if ($name && $date && $checkInTime && $checkOutTime) {
        saveEmployeeTime($name, $date, $checkInTime, $checkOutTime);
        $message = "Employee time recorded successfully!";
    } else {
        $message = "Please fill in all fields.";
    }
}

// Get all recorded times
$employeeTimes = getEmployeeTimes();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auriseg</title>
    <style>
        /* Basic reset */
        body, h1, h2, p { margin: 0; padding: 0; }

        /* Container with flexbox layout */
        .container {
            display: flex;
            height: 100vh;
        }

        /* Side navigation styles */
        .side-nav {
            width: 250px;
            background-color: #333;
            color: white;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            box-shadow: 2px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .side-nav a {
            padding: 12px 16px;
            text-decoration: none;
            color: white;
            display: block;
            border-bottom: 1px solid #444;
        }
        .side-nav h2 {
            font-size: 22px;
            color: #fff;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .hidden { display: none; }

        .side-nav a {
            padding: 12px 16px;
            text-decoration: none;
            color: white;
            display: block;
            border-bottom: 1px solid #444;
            font-size: 16px;
            font-weight: normal;
            transition: background-color 0.3s ease;
        }

        .side-nav a:hover {
            background-color: #4CAF50;
        }

        .side-nav a i {
            margin-right: 10px;
        }

        .side-nav a:hover i {
            color: #fff;
        }

        /* Main content area */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }

        /* Header styles */
        h1, h2 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        /* Form styles */
        form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="date"], input[type="time"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Table styles */
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        /* Message styles */
        .message {
            background-color: #e7f3fe;
            border-left: 6px solid #2196F3;
            margin-bottom: 15px;
            padding: 10px;
            color: #0c5460;
        }

        /* Calendar styles */
        .calendar {
            width: 100%;
            border-collapse: collapse;
        }
        .calendar th, .calendar td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .calendar th {
            background-color: #4CAF50;
            color: white;
        }
        .calendar .today {
            background-color: #e7f3fe;
        }
        .filter-panel {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .filter-panel h3 {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Side Navigation -->
        <div class="side-nav">
            <h2 style="text-align: center; color:#ffffff;">Auriseg</h2>

            <a href="#overview" onclick="showSection('overview')">Overview</a>
            <a href="#MyLeave" onclick="showSection('MyLeave')">My Leave</a>
            <a href="#MyAttence" onclick="showSection('MyAttence')">My Attence</a>
            <a href="#MyCompensation" onclick="showSection('MyCompensation')">My Compensation</a>
            <a href="#MyExit"  onclick="showSection('MyExit')">My Exit</a>
            <a href="#Alerts"  onclick="showSection('Alerts')">Alerts</a>
            <a href="#MyCalendar" onclick="showSection('MyCalendar')">My Calendar</a>
            <a href="#People" onclick="showSection('People')">People</a>
            <a href="#form" onclick="showSection('form')">Record Time</a>
            <a href="#recorded-times" onclick="showSection('recorded-times')">View Recorded Times</a>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <?php if (isset($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <div id="overview">
                <h2>Dashboard Overview</h2>
                <p>Welcome to the Auriseg Dashboard. Here you can manage your attendance, leave, and other features.</p>
            </div>

            <div id="MyAttence" class="hidden">
                <h2>My Attendance</h2>
                <p>View and manage your attendance records here.</p>
            </div>

            <!-- Form Section -->
            <div id="form" class="hidden">
                <h2>Record Time</h2>
                <form method="post">
                    <label for="name">Employee Name:</label>
                    <input type="text" id="name" name="name" required>

                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>

                    <label for="check_in_time">Check-in Time:</label>
                    <input type="time" id="check_in_time" name="check_in_time" required>

                    <label for="check_out_time">Check-out Time:</label>
                    <input type="time" id="check_out_time" name="check_out_time" required>

                    <input type="submit" value="Record">
                </form>
            </div>

            <!-- Recorded Times Section -->
            <div id="recorded-times" class="hidden">
                <h2>Recorded Times</h2>
                <table>
                    <tr>
                        <th>Employee Name</th>
                        <th>Date</th>
                        <th>Check-in Time</th>
                        <th>Check-out Time</th>
                    </tr>
                    <?php foreach ($employeeTimes as $time): ?>
                        <?php $data = explode('|', $time); ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data[0]); ?></td>
                            <td><?php echo htmlspecialchars($data[1]); ?></td>
                            <td><?php echo htmlspecialchars($data[2]); ?></td>
                            <td><?php echo htmlspecialchars($data[3]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div id="MyLeave" class="hidden">
                <h2>My Leave</h2>
                <p>Manage your leave requests and view leave balance here.</p>
            </div>

            <div id="MyCalendar" class="hidden">
                <h2>My Calendar</h2>
                <div style="display: flex; justify-content: space-between;">
                    <div style="width: 70%;">
                        <?php
                        $year = date('Y');
                        $month = date('m');
                        echo generateCalendar($year, $month);
                        ?>
                    </div>
                    <div class="filter-panel" style="width: 25%;">
                        <h3>Filter Events</h3>
                        <div>
                            <input type="checkbox" id="select-all" name="select-all">
                            <label for="select-all">Select All Events</label>
                        </div>
                        <div>
                            <input type="checkbox" id="my-leave" name="my-leave">
                            <label for="my-leave">My Leave</label>
                        </div>
                        <div>
                            <input type="checkbox" id="leave-request" name="leave-request">
                            <label for="leave-request">My Leave Request</label>
                        </div>
                        <div>
                            <input type="checkbox" id="notify" name="notify">
                            <label for="notify">Notify</label>
                        </div>
                        <div>
                            <input type="checkbox" id="team-leave" name="team-leave">
                            <label for="team-leave">Team Leave</label>
                        </div>
                        <div>
                            <input type="checkbox" id="holiday" name="holiday">
                            <label for="holiday">Holiday</label>
                        </div>
                        <div>
                            <input type="checkbox" id="week-off" name="week-off">
                            <label for="week-off">Week Off</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function showSection(sectionId) {
            const sections = ['form', 'recorded-times', 'MyAttence', 'MyLeave', 'overview', 'MyCalendar'];
            sections.forEach((id) => {
                const element = document.getElementById(id);
                if (element) {
                    element.classList.add('hidden');
                }
            });
            const selectedSection = document.getElementById(sectionId);
            if (selectedSection) {
                selectedSection.classList.remove('hidden');
            }

            // Save selected section in local storage
            localStorage.setItem('selectedSection', sectionId);
        }

        // Load default or saved section on page load
        window.onload = function () {
            const defaultSection = 'overview'; // Default section
            const savedSection = localStorage.getItem('selectedSection') || defaultSection;
            showSection(savedSection);
        };
    </script>
</body>
</html>