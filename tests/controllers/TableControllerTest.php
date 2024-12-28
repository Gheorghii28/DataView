<?php

use Api\Core\DbConnection;
use PHPUnit\Framework\TestCase;
use Api\Controller\TableController;
use Api\Core\Response;
use Dotenv\Dotenv;

class TableControllerTest extends TestCase
{
    private $tableController;
    private $dbConnection;
    private $db;
    private $response;

    protected function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.env.testing');
        $dotenv->load();

        $this->dbConnection = new DbConnection();
        $this->db = $this->dbConnection->getInstance();

        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        $this->db->query("DROP TABLE IF EXISTS test_table");
        $this->db->query("DROP TABLE IF EXISTS test_table2");
        $this->db->query("DROP TABLE IF EXISTS existing_table");
        $this->db->query("DROP TABLE IF EXISTS new_test_table");
        $this->db->query("TRUNCATE TABLE user_tables");
        $this->db->query("TRUNCATE TABLE users");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");
        
        $this->db->query("INSERT INTO users (id, username, password) VALUES (1, 'testuser', 'password123')");

        $this->response = new Response(shouldReturn: true);
        $this->tableController = new TableController($this->dbConnection, $this->response);
    }

    public function testDatabaseConnection()
    {
        $dbConnection = new DbConnection();
        $this->assertNotNull($dbConnection->getInstance());
    }

    public function testCreateTableSuccess()
    {
        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'columns' => [
                'name' => 'VARCHAR(255)',
                'age' => 'INT',
            ]
        ];

        $createTableResult = $this->tableController->create($request);
        $this->assertEquals(200, $createTableResult['status']);
        $this->assertEquals("Table '{$request['tableName']}' created and linked to user successfully.", $createTableResult['message']);
    }

    public function testCreateTableConflict()
    {
        $this->db->query("CREATE TABLE existing_table (id INT PRIMARY KEY, name VARCHAR(255))");
        $this->db->query("INSERT INTO user_tables (user_id, table_name) VALUES (1, 'existing_table')");

        $request = [
            'userId' => 1,
            'tableName' => 'existing_table',
            'columns' => [
                'name' => 'VARCHAR(255)',
                'age' => 'INT',
            ]
        ];

        $createTableResult = $this->tableController->create($request);

        $this->assertEquals(409, $createTableResult['status']);
        $this->assertEquals("Table '{$request['tableName']}' already exists.", $createTableResult['message']);
    }  

    public function testGetTableDataSuccess()
    {
        $this->db->query("CREATE TABLE test_table (id INT PRIMARY KEY, name VARCHAR(255), age INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, user_id INT, display_order INT DEFAULT 0)");
        $this->db->query("INSERT INTO test_table (user_id, name, age) VALUES (1, 'John Doe', 30)");
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");

        $queryResult = $this->db->query("SELECT created_at FROM test_table WHERE user_id = 1 AND name = 'John Doe'");
        $row = $queryResult->fetch_assoc();
        $actualCreatedAt = $row['created_at'];
        $request = [
            'userId' => 1,
            'tableName' => 'test_table'
        ];
        $expectedColumns = [
            ['name' => 'id', 'type' => 'int(11)'],
            ['name' => 'name', 'type' => 'varchar(255)'],
            ['name' => 'age', 'type' => 'int(11)'],
            ['name' => 'created_at', 'type' => 'timestamp'],
            ['name' => 'user_id', 'type' => 'int(11)'],
            ['name' => 'display_order', 'type' => 'int(11)']
        ];
        $expectedRows = [
            [
                'id' => 0, 
                'name' => 'John Doe', 
                'age' => 30,
                'created_at' => $actualCreatedAt,
                'user_id' => 1,
                'display_order' => 0
            ]
        ];

        $getTableResult = $this->tableController->get($request);

        $this->assertEquals(200, $getTableResult['status']);
        $this->assertEquals('Table data fetched successfully.', $getTableResult['message']);
        $this->assertEquals(['columns' => $expectedColumns, 'rows' => $expectedRows], $getTableResult['data']);
    } 

    public function testDeleteTableSuccess()
    {
        $this->db->query("CREATE TABLE test_table (id INT PRIMARY KEY, name VARCHAR(255))");
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");

        $request = [
            'userId' => 1,
            'tableName' => 'test_table'
        ];

        $deleteTableResult = $this->tableController->delete($request);
        
        $this->assertEquals(200, $deleteTableResult['status']);
        $this->assertEquals("Table '{$request['tableName']}' has been successfully deleted.", $deleteTableResult['message']);

        $queryResult = $this->db->query("SHOW TABLES LIKE '{$request['tableName']}'");

        $this->assertEquals(0, $queryResult->num_rows);
    }

    public function testRenameTableSuccess()
    {
        $this->db->query("CREATE TABLE test_table (id INT PRIMARY KEY, name VARCHAR(255))");
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");

        $request = [
            'userId' => 1,
            'oldName' => 'test_table',
            'newName' => 'new_test_table',
        ];

        $renameTableResult = $this->tableController->rename($request);
                
        $this->assertEquals(200, $renameTableResult['status']);
        $this->assertEquals("The table has been successfully renamed from '{$request['oldName']}' to '{$request['newName']}'.", $renameTableResult['message']);
    }
    
    public function testGetUserTablesSuccess()
    {
        $this->db->query("CREATE TABLE test_table (id INT PRIMARY KEY, name VARCHAR(255), age INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, user_id INT, display_order INT DEFAULT 0)");
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");
        $this->db->query("INSERT INTO test_table (user_id, name, age) VALUES (1, 'John Doe', 30)");

        $request = [
            'userId' => 1,
        ];

        $getUserTablesResult = $this->tableController->getUserTables($request);

        $this->assertEquals(200, $getUserTablesResult['status']);
        $this->assertEquals('User tables fetched successfully.', $getUserTablesResult['message']);
        $this->assertNotEmpty($getUserTablesResult['data']);
        $this->assertEquals('test_table', $getUserTablesResult['data'][0]['name']);
    }

    public function testUpdateTableOrderSuccess()
    {
        $this->db->query("CREATE TABLE test_table (id INT PRIMARY KEY, name VARCHAR(255), age INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, user_id INT, display_order INT DEFAULT 0)");
        $this->db->query("CREATE TABLE test_table2 (id INT PRIMARY KEY, name VARCHAR(255), age INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, user_id INT, display_order INT DEFAULT 0)");
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (2, 1, 'test_table2', 1)");

        $request = [
            'userId' => 1,
            'order' => [2, 1]
        ];

        $updateTableOrderResult = $this->tableController->updateTableOrder($request);

        $this->assertEquals(200, $updateTableOrderResult['status']);
        $this->assertEquals('Table order updated successfully.', $updateTableOrderResult['message']);
    }
}
