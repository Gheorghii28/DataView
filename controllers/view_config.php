<?php
// Mapping of allowed views to their respective view file paths
$allowedViews = [
    'table' => 'table.php',
    'profile' => 'profile.php',
    'settings' => 'settings.php',
    'dashboard' => 'dashboard.php',
];

// Mapping of views to their respective data loading functions
$viewDataFunctions = [
    'dashboard' => 'loadDashboardData',
    'profile' => 'loadProfileData',
    'table' => 'loadTableData',
    'settings' => 'loadSettingsData',
];
