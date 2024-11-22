<?php
// Start the session
session_start();

// Include the database connection file
require_once 'db_connection.php';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get current user email
function getCurrentUserEmail() {
    return $_SESSION['email'] ?? null;
}

// Function to get current user role
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

// Function to logout user
function logoutUser() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Define access rules
$accessRules = [
    'feedback.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'dashboard.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    '/clients/client_dashboard.php' => ['admin', 'client'],
    'analytics.php' => ['admin', 'project manager'],
    'task_tracker.php' => ['admin', 'project manager', 'L3'],
    'daily_status.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'user_management.php' => ['admin', 'L3', 'project manager'],
    'add_user.php' => ['admin'],
    'edit_user.php' => ['admin'],
    'delete_user.php' => ['admin'],
    'add_client.php' => ['admin', 'L3', 'project manager'],
    'add_project.php' => ['admin', 'L3', 'project manager'],
    'edit_client.php' => ['admin', 'L3', 'project manager'],
    'client_management.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'project_management.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'web_generate_report.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'edit_project.php' => ['admin', 'project manager'],
    'vulnerability_management.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'generate_report.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'view_project.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'add_vulnerabilities.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'edit_vulnerability.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'view_vulnerability.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'individual_performance.php' => ['admin', 'project manager', 'L3', 'L2', 'L1'],
    'performance_management.php' => ['admin', 'project manager', 'L3'],
    // Add more pages and their allowed roles here
];

// Function to check if user has access to a specific page
function hasAccess($page, $projectId = null) {
    global $accessRules;
    $userId = getCurrentUserId();
    $userRole = getCurrentUserRole();

    if ($userRole === 'admin') {
        return true; // Admin has access to all pages
    }

    if ($page === 'view_project.php' || $page === 'vulnerability_management.php' || $page === 'edit_project.php') {
        if ($projectId === null) {
            return false; // Project ID not provided
        }

        $pdo = getDBConnection();
        $sql = "SELECT COUNT(*) FROM project_assignments WHERE project_id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$projectId, $userId]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            return true; // User is assigned to the project
        } else {
            return false; // User is not assigned to the project
        }
    }

    // For other pages, use the existing access rules
    if (!isset($accessRules[$page])) {
        return false; // If the page is not in the rules, deny access by default
    }

    return in_array($userRole, $accessRules[$page]);
}

// Function to require specific access for a page
function requireAccess($page, $projectId = null) {
    if (!hasAccess($page, $projectId)) {
        // Redirect to an access denied page or show an error
        header("Location: access_denied.php");
        exit();
    }
}

// Function to add a new access rule
function addAccessRule($page, $allowedRoles) {
    global $accessRules;
    $accessRules[$page] = $allowedRoles;
}

// Example of how to use these functions in your pages:
// 
// At the top of each page (e.g., vulnerability_management.php):
// 
// require_once 'config.php';
// requireLogin();
// requireAccess(basename(__FILE__), $_GET['project_id']);
// 
// $userId = getCurrentUserId();
// $userEmail = getCurrentUserEmail();
// $userRole = getCurrentUserRole();
//
// To logout:
// if (isset($_GET['logout'])) {
//     logoutUser();
// }
//
// To add a new access rule:
// addAccessRule('new_page.php', ['admin', 'project manager']);