<?php
// Include the config file to access user information
require_once 'config.php';

// Function to check if the current page is active
function isActive($pageName) {
    return basename($_SERVER['PHP_SELF']) === $pageName ? 'bg-blue-500 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
}

// Function to generate SVG icons
function svgIcon($name) {
    $icons = [
        'grid' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />',
        'bar-chart-2' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
        'list' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />',
        'folder' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
        'layout' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
        'link' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />',
        'monitor' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />',
        'book-open' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />',
        'file-text' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
        'file' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />',
        'settings' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
        'help-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'chevron-left' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />',
        'log-out' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />',
        'feedback' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10z" /><path d="M8 9h8" /><path d="M8 13h6" />',
        'database' => '<ellipse cx="12" cy="5" rx="9" ry="3" /><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" /><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />',
    ];
    return $icons[$name] ?? '';
}

// Define user roles and their permissions
$userRoles = [
    'admin' => ['feedback', 'dashboard', 'analytics', 'manage_performance', 'manage_daily_status','analytics', 'vulnerabilities', 'clients', 'projects', 'users', 'logout', 'profile'],
    'project manager' => ['feedback','dashboard', 'analytics', 'manage_daily_status', 'analytics', 'vulnerabilities', 'clients', 'projects', 'logout', 'profile'],
    'L1' => ['feedback','individual_performance', 'dashboard', 'manage_daily_status' , 'clients', 'vulnerabilities', 'projects', 'logout', 'profile'],
    'L2' => ['feedback','individual_performance', 'dashboard', 'manage_daily_status', 'clients', 'vulnerabilities', 'projects', 'logout', 'profile'],
    'L3' => ['feedback','individual_performance', 'dashboard', 'manage_daily_status', 'clients', 'vulnerabilities', 'projects', 'logout', 'profile'],
    'client' => ['client_dashboard']
];

// Navigation items and sections with permissions
$navItems = [
    ['icon' => 'grid', 'label' => 'Dashboard', 'link' => 'dashboard.php', 'permission' => 'dashboard'],
    ['icon' => 'grid', 'label' => 'Dashboard', 'link' => 'client_dashboard.php', 'permission' => 'client_dashboard'],
    ['icon' => 'bar-chart-2', 'label' => 'Analytics', 'link' => 'analytics.php', 'permission' => 'analytics'],
    ['icon' => 'list', 'label' => 'My Profile', 'link' => 'profile.php', 'permission' => 'profile'],
];

$sections = [
    'Project Management' => [
        ['icon' => 'folder', 'label' => 'Clients', 'link' => 'client_management.php', 'permission' => 'clients'],
        ['icon' => 'layout', 'label' => 'Projects', 'link' => 'project_management.php', 'permission' => 'projects'],
        ['icon' => 'database', 'label' => 'Vuln DB', 'link' => 'vuln_db.php', 'permission' => 'projects'],
        ['icon' => 'file', 'label' => 'Daily Status', 'link' => 'management_daily_status.php', 'permission' => 'manage_daily_status'],
        ['icon' => 'bar-chart-2', 'label' => 'Performance', 'link' => 'performance_management.php', 'permission' => 'manage_performance'],
        ['icon' => 'bar-chart-2', 'label' => 'Performance', 'link' => 'individual_performance.php', 'permission' => 'individual_performance']
        
    ],
    'User Management' => [
        ['icon' => 'users', 'label' => 'Users', 'link' => 'user_management.php', 'permission' => 'users'],
    ],
    '' => [
        ['icon' => 'feedback', 'label' => 'Feedback', 'link' => 'feedback.php', 'permission' => 'feedback'],
        ['icon' => 'log-out', 'label' => 'Logout', 'link' => 'logout.php', 'permission' => 'logout']
    ],
];

function hasPermission($permission, $userRole) {
    global $userRoles;
    return in_array($permission, $userRoles[$userRole]);
}

function getSidebar() {
    global $navItems, $sections;
    $collapsed = isset($_COOKIE['sidebar_collapsed']) ? $_COOKIE['sidebar_collapsed'] === 'true' : false;
    
    // Get the current user's email and role
    $currentUserEmail = getCurrentUserEmail();
    $currentUserRole = getCurrentUserRole();
    
    ob_start();
    ?>
    <div id="sidebar" class="sidebar flex flex-col h-screen transition-all duration-300 <?php echo $collapsed ? 'w-14' : 'w-64'; ?>">
        <!-- Logo and Collapse Button -->
        <div class="flex items-center justify-between h-14 border-b border-gray-800 px-2">
            <a href="/" class="flex items-center h-full">
                <?php if (!$collapsed): ?>
                    <img src="images/logo_white.png" alt="Logo" class="h-8 w-auto">
                <?php else: ?>
                    <img src="images/logo_white.png" alt="Logo" class="h-6 w-auto">
                <?php endif; ?>
            </a>
            <button id="collapseButton" 
                    class="p-1 hover:bg-gray-700 rounded-md flex items-center justify-center" 
                    onclick="toggleSidebar()">
                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="h-4 w-4 transition-transform <?php echo $collapsed ? 'rotate-180' : ''; ?>" 
                     fill="none" 
                     viewBox="0 0 24 24" 
                     stroke="currentColor">
                    <?php echo svgIcon('chevron-left'); ?>
                </svg>
            </button>
        </div>

        <!-- Main Navigation -->
        <nav class="flex-1 space-y-1 px-2 py-4 overflow-y-auto">
            <?php foreach ($navItems as $item): 
                if (hasPermission($item['permission'], $currentUserRole)):
            ?>
                <a href="<?php echo $item['link']; ?>" 
                   class="sidebar-nav-item flex items-center justify-<?php echo $collapsed ? 'center' : 'start'; ?> px-2 py-2 text-sm font-medium rounded-md <?php echo isActive($item['link']); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-6 w-6 <?php echo $collapsed ? '' : 'mr-3'; ?>" 
                         fill="none" 
                         viewBox="0 0 24 24" 
                         stroke="currentColor">
                        <?php echo svgIcon($item['icon']); ?>
                    </svg>
                    <?php if (!$collapsed): ?>
                        <span><?php echo $item['label']; ?></span>
                    <?php endif; ?>
                </a>
            <?php 
                endif;
            endforeach; 

            foreach ($sections as $title => $items):
                $sectionHasItems = false;
                foreach ($items as  $item) {
                    if (hasPermission($item['permission'], $currentUserRole)) {
                        $sectionHasItems = true;
                        break;
                    }
                }
                if ($sectionHasItems):
            ?>
                <div class="pt-4">
                    <?php if (!$collapsed): ?>
                        <h2 class="sidebar-section-title px-3 text-xs font-semibold uppercase tracking-wider">
                            <?php echo $title; ?>
                        </h2>
                    <?php endif; ?>
                    <div class="mt-2 space-y-1">
                        <?php foreach ($items as $item): 
                            if (hasPermission($item['permission'], $currentUserRole)):
                        ?>
                            <a href="<?php echo $item['link']; ?>" 
                               class="sidebar-nav-item flex items-center justify-<?php echo $collapsed ? 'center' : 'start'; ?> px-2  py-2 text-sm font-medium rounded-md <?php echo isActive($item['link']); ?>">
                                <svg  xmlns="http://www.w3.org/2000/svg" 
                                     class="h-6 w-6  <?php echo $collapsed  ? '' : 'mr-3'; ?>" 
                                     fill="none" 
                                     viewBox="0 0 24 24" 
                                     stroke="currentColor">
                                    <?php echo svgIcon($item['icon']); ?>
                                </svg>
                                <?php if (!$collapsed): ?>
                                    <span><?php echo $item['label']; ?></span>
                                <?php endif; ?>
                            </a>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </nav>

        <!-- Bottom Section -->
        <div class="sidebar-footer mt-auto border-t border-gray-800 p-2">
            <div class="flex items-center justify-center relative py-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <?php echo svgIcon('users'); ?>
                    </svg>
                </div>
                <?php if (!$collapsed): ?>
                    <div class="flex-1 ml-3">
                        <span class="text-sm font-medium"><?php echo $currentUserEmail; ?></span>
                        <p class="text-xs text-gray-400"><?php echo ucfirst($currentUserRole); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
<style>
    /* Sidebar styles */
    .sidebar {
        background-color: #1e1e1e;
        color: #ffffff;
    }
    .sidebar-nav-item {
        color: #ffffff;
    }
    .sidebar-nav-item:hover {
        background-color: #2a2a2a;
    }
    .sidebar-nav-item.active {
        background-color: #2d2d2d;
    }
    .sidebar-section-title {
        color: #8a8a8a;
    }
    .sidebar svg {
        color: #ffffff;
    }
    .sidebar-footer {
        border-top-color: #333333;
    }
    /* New style for logo container */
    .sidebar .flex.items-center.h-full {
        margin-left: 0.75rem; /* Adds more margin to the left of the logo */
    }
</style>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const collapsed = sidebar.classList.contains('w-14');
        sidebar.classList.toggle('w-14');
        sidebar.classList.toggle('w-64');
        document.cookie = `sidebar_collapsed=${!collapsed}; path=/; max-age=31536000`;
        location.reload();
    }
</script>