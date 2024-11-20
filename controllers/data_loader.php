<?php
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

function loadTableData($mysql_db, $user_id) {
    // Currently, we're only fetching the username, but you can expand this 
    // to fetch more data relevant to the user's table.
    return getUserData($mysql_db, $user_id);
}

function loadSettingsData($mysql_db, $user_id) {
    // Currently, we're only fetching the username, but you can expand this 
    // to fetch more data relevant to the user's settings.
    return getUserData($mysql_db, $user_id);
}
