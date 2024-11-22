<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';
require_once 'functions.php';

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);

if (!$user) {
    error_log("User not found for ID: $user_id");
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard - HR Management System</title>
    <style>
        :root {
            --primary-blue: #0084ff;
            --orange: #ff9500;
            --red: #ff4b4b;
            --green: #34c759;
            --light-blue: #e8f3ff;
            --border-color: #eaeaea;
            --text-dark: #333;
            --text-light: #666;
            --background: #f5f7f9;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background-color: var(--background);
        }

        .header {
            background: var(--primary-blue);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            color: white;
        }

        .logo {
            height: 30px;
        }

        .search-container {
            flex: 1;
            max-width: 600px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 8px 16px;
            border-radius: 20px;
            border: none;
            font-size: 14px;
        }

        .sidebar {
            position: fixed;
            width: 250px;
            height: calc(100vh - 60px);
            background: white;
            padding: 20px 0;
            border-right: 1px solid var(--border-color);
        }

        .nav-item {
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            color: var(--text-light);
            text-decoration: none;
        }

        .nav-item.active {
            background: var(--light-blue);
            color: var(--primary-blue);
            border-left: 3px solid var(--primary-blue);
        }

        .main-content {
            margin-left: 250px;
            padding: 24px;
        }

        .overview-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .time-display {
            font-size: 48px;
            color: var(--primary-blue);
            font-weight: bold;
            margin: 20px 0 10px;
        }

        .date-display {
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .btn {
            padding: 10px 24px;
            border-radius: 20px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            color: white;
        }

        .btn-break {
            background: var(--orange);
        }

        .btn-clock {
            background: var(--red);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--light-blue);
            padding: 16px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            color: var(--primary-blue);
            margin: 8px 0;
        }

        .chart-container {
            margin-top: 24px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            height: 300px;
        }

        .request-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        .request-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
        }

        .request-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin: 20px 0;
        }

        .request-stat-item {
            display: flex;
            justify-content: space-between;
            color: var(--text-light);
        }

        .raise-request-btn {
            background: var(--primary-blue);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .period-selector {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            margin: 20px 0;
            cursor: pointer;
        }

        .celebrations-card {
            background: var(--light-blue);
            border-radius: 12px;
            padding: 20px;
            margin-top: 24px;
        }

        .tab-group {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .tab {
            padding: 8px 16px;
            cursor: pointer;
            color: var(--text-light);
        }

        .tab.active {
            color: var(--primary-blue);
            border-bottom: 2px solid var(--primary-blue);
        }
    </style>
</head>
<body>
    <header class="header">
        <img src="images/qandle-logo.png" alt="Qandle" class="logo">
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search by name, department or location">
        </div>
    </header>

    <nav class="sidebar">
        <a href="#" class="nav-item">
            <img src="images/profile.png" alt="" style="width: 40px; height: 40px; border-radius: 50%;">
            Arun .
        </a>
        <a href="#" class="nav-item">
            <i class="icon">📄</i>
            My Leave
        </a>
        <a href="#" class="nav-item active">
            <i class="icon">⏰</i>
            My Attendance
        </a>
        <a href="#" class="nav-item">
            <i class="icon">💰</i>
            My Compensation
        </a>
        <a href="#" class="nav-item">
            <i class="icon">🚪</i>
            My Exit
        </a>
        <a href="#" class="nav-item">
            <i class="icon">🔔</i>
            Alerts
        </a>
        <a href="#" class="nav-item">
            <i class="icon">📅</i>
            My Calendar
        </a>
        <a href="#" class="nav-item">
            <i class="icon">👥</i>
            People
        </a>
    </nav>

    <main class="main-content">
        <h1>Overview</h1>
        
        <section class="overview-section">
            <h2>Time & Attendance</h2>
            
            <div class="time-display" id="current-time">06:14:06</div>
            <div class="date-display" id="current-date">22-Nov-2024</div>
            
            <div class="button-group">
                <button class="btn btn-break" id="break-btn">Start Break</button>
                <button class="btn btn-clock" id="clock-btn">Clock Out</button>
            </div>

            <div class="period-selector">
                Period: Last 7 Day(s) ▼
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <h3>Clock In Time</h3>
                    <div class="stat-value" id="clock-in-time">10:15:42</div>
                </div>
                <div class="stat-card">
                    <h3>Break Duration</h3>
                    <div class="stat-value" id="break-duration">00:00:00</div>
                </div>
                <div class="stat-card">
                    <h3>Average Working Hours</h3>
                    <div class="stat-value">05:01</div>
                </div>
                <div class="stat-card">
                    <h3>Average Break Duration</h3>
                    <div class="stat-value">00:00</div>
                </div>
            </div>

            <div class="chart-container">
                <canvas id="attendanceChart"></canvas>
            </div>
        </section>

        <section class="request-section">
            <div class="request-card">
                <h3>Leave</h3>
                <div class="request-stats">
                    <div class="request-stat-item">
                        <span>Raised</span>
                        <span>1</span>
                    </div>
                    <div class="request-stat-item">
                        <span>Pending</span>
                        <span>1</span>
                    </div>
                    <div class="request-stat-item">
                        <span>Approved</span>
                        <span>0</span>
                    </div>
                    <div class="request-stat-item">
                        <span>Rejected/Cancelled</span>
                        <span>0</span>
                    </div>
                </div>
                <button class="raise-request-btn">Raise Request</button>
            </div>

            <div class="request-card">
                <h3>Attendance Regularization</h3>
                <div class="request-stats">
                    <div class="request-stat-item">
                        <span>Raised</span>
                        <span>0</span>
                    </div>
                    <div class="request-stat-item">
                        <span>Pending</span>
                        <span>0</span>
                    </div>
                    <div class="request-stat-item">
                        <span>Approved</span>
                        <span>0</span>
                    </div>
                    <div class="request-stat-item">
                        <span>Rejected/Cancelled</span>
                        <span>0</span>
                    </div>
                </div>
                <button class="raise-request-btn">Raise Request</button>
            </div>
        </section>

        <div class="celebrations-card">
            <div class="tab-group">
                <div class="tab active">BIRTHDAY(S)</div>
                <div class="tab">WORK ANNIVERSARIES</div>
            </div>
            <div style="text-align: center;">
                <img src="images/birthday-icon.png" alt="Birthday" style="width: 64px; height: 64px;">
                <p>22-Nov-2024</p>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', { 
                hour12: false 
            });
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', { 
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        setInterval(updateTime, 1000);
        updateTime();

        // Initialize attendance chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['18 Mon', '19 Tue', '20 Wed', '21 Thu', '22 Fri', '23 Sat', '24 Sun'],
                datasets: [{
                    label: 'Work Hours',
                    data: [5.52, 0, 7.26, 0, 6.14, 0, 0],
                    backgroundColor: '#34c759'
                }, {
                    label: 'Break Duration',
                    data: [0, 10.49, 0, 0, 0, 0, 0],
                    backgroundColor: '#ff9500'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Handle break button
        let isOnBreak = false;
        let breakStartTime = null;
        
        document.getElementById('break-btn').addEventListener('click', function() {
            if (!isOnBreak) {
                this.textContent = 'End Break';
                this.style.backgroundColor = 'var(--red)';
                breakStartTime = new Date();
            } else {
                this.textContent = 'Start Break';
                this.style.backgroundColor = 'var(--orange)';
                const duration = Math.floor((new Date() - breakStartTime) / 1000);
                const minutes = Math.floor(duration / 60);
                const seconds = duration % 60;
                document.getElementById('break-duration').textContent = 
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}:00`;
            }
            isOnBreak = !isOnBreak;
        });

        // Handle clock button
        let isClockedIn = true;
        document.getElementById('clock-btn').addEventListener('click', function() {
            if (isClockedIn) {
                this.textContent = 'Clock In';
                this.style.backgroundColor = 'var(--primary-blue)';
            } else {
                this.textContent = 'Clock Out';
                this.style.backgroundColor = 'var(--red)';
            }
            isClockedIn = !isClockedIn;
        });
    </script>
</body>
</html>