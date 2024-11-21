<?php
require_once "../core/TableManager.php";

function getUserData($mysql_db, $user_id) {
    $data = ['username' => "Guest"]; // Default value for username
    if ($user_id) {
        $stmt = $mysql_db->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $data['username'] = htmlspecialchars($user_data['username']);
        }
        $stmt->close();
    }
    return $data; // Return the data array
}

function loadDashboardData($mysql_db, $user_id) {
    // Currently, we're only fetching the username, but you can expand this 
    // to fetch more data relevant to the dashboard.
    return getUserData($mysql_db, $user_id);
}

function loadProfileData($mysql_db, $user_id) {
    // Currently, we're only fetching the username, but you can expand this 
    // to fetch more data relevant to the user's profile.
    return getUserData($mysql_db, $user_id);
}

function loadTableData($mysql_db, $user_id, $table_name = null) {
    if (!$table_name) {
        return ['error' => 'No table selected'];
    }

    $tableManager = new TableManager($mysql_db); // Instantiate the TableManager

    if (!$tableManager->hasUserAccess($user_id, $table_name)) { // Check if the user has access to the table
        return ['error' => 'Unauthorized access or table does not exist'];
    }

    $columns = $tableManager->getTableColumns($table_name); // Retrieve the table columns
    $rows = $tableManager->getTableRows($table_name); // Retrieve the table rows
    $data = ['columns' => $columns, 'rows' => $rows]; // Prepare the data for the view

    if (empty($data['rows'])) { // If there are no rows, ensure an empty table is shown
        $data['rows'] = []; // Ensure that an empty array is returned for rows
        $data['message'] = 'No data available in the table.'; // Add a message in the view
    }

    return $data;
}

function loadSettingsData($mysql_db, $user_id) {
    // Currently, we're only fetching the username, but you can expand this 
    // to fetch more data relevant to the user's settings.
    return getUserData($mysql_db, $user_id);
}
