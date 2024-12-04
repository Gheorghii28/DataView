<?php
class TableManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Check if the user has access to this table
    public function hasUserAccess($user_id, $table_name) {
        $count = 0;
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_tables WHERE user_id = ? AND table_name = ?");
        $stmt->bind_param("is", $user_id, $table_name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    // Retrieve the column names and data types of a table
    public function getTableColumns($table_name) {
        $columns = [];
        $column_name = '';
        $data_type = '';
        $stmt = $this->db->prepare("SELECT COLUMN_NAME, DATA_TYPE 
                                    FROM INFORMATION_SCHEMA.COLUMNS 
                                    WHERE TABLE_SCHEMA = DATABASE() 
                                    AND TABLE_NAME = ?");
        $stmt->bind_param("s", $table_name);
        $stmt->execute();
        $stmt->bind_result($column_name, $data_type);
        
        while ($stmt->fetch()) {
            $columns[] = [
                'name' => $column_name,
                'type' => $data_type
            ];
        }

        $stmt->close();
        return $columns;
    }

    // Retrieve the rows of a table
    public function getTableRows($table_name) {
        $query = "SELECT * FROM `" . $this->db->real_escape_string($table_name) . "` ORDER BY `display_order` ASC";
        $result = $this->db->query($query);

        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        return [];
    }
}
