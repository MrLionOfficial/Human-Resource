<?php
session_start();
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

$clockInTime = isset($_SESSION['clockInTime']) ? $_SESSION['clockInTime'] : '00:00:00';
$breakDuration = isset($_SESSION['breakDuration']) ? $_SESSION['breakDuration'] : '00:00:00';
$currentDate = date('d-M-Y');

// Handle clock in/out actions
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'clockIn' && !isset($_SESSION['clockInTime'])) {
        $_SESSION['clockInTime'] = date('H:i:s');
        $_SESSION['clockInDate'] = date('Y-m-d');
        $clockInTime = $_SESSION['clockInTime'];
    }
}

// Sample data for the chart
$chartData = [
    ['day' => '18 Mon', 'workHours' => 8.62, 'breakDuration' => 1, 'autoClockOut' => 0],
    ['day' => '19 Tue', 'workHours' => 7.5, 'breakDuration' => 1, 'autoClockOut' => 10.2],
    ['day' => '20 Wed', 'workHours' => 0, 'breakDuration' => 0, 'autoClockOut' => 0],
    ['day' => '21 Thu', 'workHours' => 0, 'breakDuration' => 0, 'autoClockOut' => 0],
    ['day' => '22 Fri', 'workHours' => 0, 'breakDuration' => 0, 'autoClockOut' => 0],
    ['day' => '23 Sat', 'workHours' => 0, 'breakDuration' => 0, 'autoClockOut' => 0],
    ['day' => '24 Sun', 'workHours' => 0, 'breakDuration' => 0, 'autoClockOut' => 0]
];

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
    <h1 style="font-size: 24px; margin-bottom: 10px;">Overview</h1>
    <h3 style="font-size: 24px; margin-bottom: 10px;">Time & Attendance</h3>
    <div style="display: flex; justify-content: space-between; gap: 20px;"> 
        <!-- Time Display Section -->
    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; background-color: #f9f9f9; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
    <!-- Current Time -->
    <div style="font-size: 48px; color: #007bff; font-weight: bold;" id="clock-time">00:00:00</div>
    <div style="font-size: 15px; color: #333; font-weight: normal;" id="current-date"></div>
    <div style="margin-top: 15px; display: flex; gap: 10px;">
        <!-- Start Break Button -->
        <button id="break-btn" onclick="toggleBreak()" style="padding: 10px 20px; font-size: 16px; background-color: #f0ad4e; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
            Start Break
        </button>
        <!-- Clock In/Out Button -->
        <button id="clock-btn" onclick="toggleClock()" style="padding: 10px 20px; font-size: 16px; background-color: #007bff; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
            Clock In
        </button>
    </div>
    </div>

   <!-- Summary Section -->
    <div style="flex: 2; display: flex; flex-direction: column; justify-content: space-between; margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; gap: 10px;">
        <div style="flex: 1; text-align: center; background-color: #007bff; color: #fff; padding: 15px; border-radius: 8px; font-size: 16px;">
            <p id="clock-in-time" style="margin: 0;">00:00:00</p>
            <p style="margin: 0;">Clock In Time</p>
        </div>
        <div style="flex: 1; text-align: center; background-color: #f0ad4e; color: #fff; padding: 15px; border-radius: 8px; font-size: 16px;">
            <p id="break-duration" style="margin: 0;">00:00:00</p>
            <p style="margin: 0;">Break Duration</p>
        </div>
    </div>
    <div style="margin-top: 15px; display: flex; justify-content: space-between; gap: 10px;">
        <div style="flex: 1; text-align: center; padding: 15px; background-color: #5cb85c; color: #fff; border-radius: 8px; font-size: 16px;">
            <p id="avg-working-hours" style="margin: 0;">08:30</p>
            <p style="margin: 0;">Average Working Hours</p>
        </div>
        <div style="flex: 1; text-align: center; padding: 15px; background-color: #d9534f; color: #fff; border-radius: 8px; font-size: 16px;">
            <p id="avg-break-duration" style="margin: 0;">30:00</p>
            <p style="margin: 0;">Average Break Duration</p>
        </div>
    </div>
</div>

<script>
    let clockInTime = null;
    let breakStartTime = null;
    let isOnBreak = false;

    function toggleClock() {
        const clockButton = document.getElementById('clock-btn');
        const clockInTimeElement = document.getElementById('clock-in-time');
        const clockTimeElement = document.getElementById('clock-time');


        if (clockButton.innerText === 'Clock In') {
            // Set Clock In Time
            clockInTime = new Date();
            clockInTimeElement.innerText = clockInTime.toLocaleTimeString();
            clockButton.innerText = 'Clock Out';
            clockButton.style.backgroundColor = '#dc3545'; // Change color to red

            // Start Timer
            timerInterval = setInterval(() => {
                const currentTime = new Date();
                const elapsedTime = Math.floor((currentTime - clockInTime) / 1000); // Elapsed time in seconds
                const hours = Math.floor(elapsedTime / 3600);
                const minutes = Math.floor((elapsedTime % 3600) / 60);
                const seconds = elapsedTime % 60;

                clockTimeElement.innerText = `${hours.toString().padStart(2, '0')}:${minutes
                    .toString()
                    .padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }, 1000);
        } else {
            // Clock Out and Stop Timer
            clearInterval(timerInterval);
            timerInterval = null;

            clockButton.innerText = 'Clock In';
            clockButton.style.backgroundColor = '#007bff'; // Change color to blue
            clockTimeElement.innerText = '00:00:00';
            clockInTime = null;
        }
    }

    function toggleBreak() {
        const breakButton = document.getElementById('break-btn');
        const breakDurationElement = document.getElementById('break-duration');
        const currentTime = new Date();

        if (!isOnBreak) {
            // Start Break
            breakStartTime = currentTime;
            breakButton.innerText = 'End Break';
            breakButton.style.backgroundColor = '#d9534f'; // Change to red
            isOnBreak = true;
        } else {
            // End Break and calculate duration
            const breakEndTime = currentTime;
            const duration = Math.floor((breakEndTime - breakStartTime) / 1000); // Duration in seconds
            const minutes = Math.floor(duration / 60);
            const seconds = duration % 60;

            breakDurationElement.innerText = `${minutes.toString().padStart(2, '0')}:${seconds
                .toString()
                .padStart(2, '0')}`;
            breakButton.innerText = 'Start Break';
            breakButton.style.backgroundColor = '#f0ad4e'; // Change back to orange
            isOnBreak = false;
        }
    }
    function updateDate() {
    const dateElement = document.getElementById('current-date');

    const currentDate = new Date();
    const day = currentDate.getDate();
    const month = currentDate.toLocaleDateString('en-US',{ month: 'long' });
    const year = currentDate.getFullYear();
    const dateString = `${day} ${month} ${year}`;
    dateElement.innerText = dateString;
}

// Initial call to set the current date
updateDate();
</script>

        <!-- Chart Section -->
        <div style="flex: 3; background-color: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <h3 style="font-size: 18px; margin-bottom: 10px;">Nov 2024</h3>
            <div style="height: 200px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                <p>Chart Placeholder</p>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                <a href="#" style="text-decoration: none; color: #007bff;">&lt; Pre Week</a>
                <a href="#" style="text-decoration: none; color: #007bff;">Next Week &gt;</a>
            </div>
        </div>
     </div>
<div id="calendar-section" style="margin-top: 20px; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
      <h2 style="font-size: 24px; margin-bottom: 10px;">Calendar</h2>

        <!-- Calendar UI -->
       <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="flex: 1; max-width: 100%; overflow-x: auto;">
            <!-- Calendar Table -->
            <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 14px; color: #333;">
                <thead style="background-color: #f5f5f5;">
                    <tr>
                        <th style="padding: 10px;">Sunday</th>
                        <th style="padding: 10px;">Monday</th>
                        <th style="padding: 10px;">Tuesday</th>
                        <th style="padding: 10px;">Wednesday</th>
                        <th style="padding: 10px;">Thursday</th>
                        <th style="padding: 10px;">Friday</th>
                        <th style="padding: 10px;">Saturday</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Rows for August 2024 -->
                    <tr>
                        <td style="padding: 10px; background-color: #f9f9f9;">28</td>
                        <td style="padding: 10px; background-color: #f9f9f9;">29</td>
                        <td style="padding: 10px; background-color: #f9f9f9;">30</td>
                        <td style="padding: 10px; background-color: #f9f9f9;">31</td>
                        <td style="padding: 10px;">1</td>
                        <td style="padding: 10px;">2</td>
                        <td style="padding: 10px;">3</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">4</td>
                        <td style="padding: 10px;">5</td>
                        <td style="padding: 10px;">6</td>
                        <td style="padding: 10px;">7</td>
                        <td style="padding: 10px;">8</td>
                        <td style="padding: 10px;">9</td>
                        <td style="padding: 10px;">10</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">11</td>
                        <td style="padding: 10px;">12</td>
                        <td style="padding: 10px;">13</td>
                        <td style="padding: 10px;">14</td>
                        <td style="padding: 10px;">15</td>
                        <td style="padding: 10px;">16</td>
                        <td style="padding: 10px;">17</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">18</td>
                        <td style="padding: 10px;">19</td>
                        <td style="padding: 10px;">20</td>
                        <td style="padding: 10px;">21</td>
                        <td style="padding: 10px;">22</td>
                        <td style="padding: 10px;">23</td>
                        <td style="padding: 10px;">24</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">25</td>
                        <td style="padding: 10px;">26</td>
                        <td style="padding: 10px;">27</td>
                        <td style="padding: 10px;">28</td>
                        <td style="padding: 10px;">29</td>
                        <td style="padding: 10px;">30</td>
                        <td style="padding: 10px;">31</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div style="flex: 1; max-width: 300px; background-color: #f5f5f5; padding: 15px; border-radius: 8px;">
            <h3 style="font-size: 18px; margin-bottom: 10px;">Filter Events</h3>
            <ul style="list-style: none; padding: 0; font-size: 14px; color: #555;">
                <li style="margin-bottom: 5px;"><span style="color: #d9534f; font-weight: bold;">●</span> My Leave: 0 Day(s)</li>
                <li style="margin-bottom: 5px;"><span style="color: #f0ad4e; font-weight: bold;">●</span> My Leave Request: 0 Day(s)</li>
                <li style="margin-bottom: 5px;"><span style="color: #5cb85c; font-weight: bold;">●</span> Notify: 0 Day(s)</li>
                <li style="margin-bottom: 5px;"><span style="color: #5bc0de; font-weight: bold;">●</span> Team Leave: 0 Day(s)</li>
                <li style="margin-bottom: 5px;"><span style="color: #007bff; font-weight: bold;">●</span> Holiday: 1 Day(s)</li>
                <li style="margin-bottom: 5px;"><span style="color: #6f42c1; font-weight: bold;">●</span> Week Off: 9 Day(s)</li>
            </ul>
        </div>
    </div>
</div>

</div>


            <div id="MyAttence" class="hidden">
                <h2>My Attendance</h2>
                <p>View and manage your attendance records here</p>
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