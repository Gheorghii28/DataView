<?php

use PHPUnit\Framework\TestCase;
use Api\Model\Table;

class TableTest extends TestCase
{
    private $db;
    private $tableModel;

    protected function setUp(): void
    {
        $this->db = new mysqli("localhost", "root", "", "data_view_test_db");
        if ($this->db->connect_error) {
            $this->fail("Failed to connect to the test database: " . $this->db->connect_error);
        }

        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        $this->db->query("DROP TABLE IF EXISTS old_name");
        $this->db->query("DROP TABLE IF EXISTS new_name");
        $this->db->query("DROP TABLE IF EXISTS table1");
        $this->db->query("DROP TABLE IF EXISTS table2");
        $this->db->query("TRUNCATE TABLE user_tables");
        $this->db->query("TRUNCATE TABLE users");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");
        $this->db->query("INSERT INTO users (id, username, password) VALUES (1, 'testuser', 'password123')");

        $this->tableModel = new Table($this->db);
    }

    protected function tearDown(): void
    {
        $this->db->query("DROP TABLE IF EXISTS test_table");
        $this->db->query("DELETE FROM user_tables");
        $this->db->close();
    }

    public function testCreateTableSuccessfully()
    {
        $columns = ['name' => 'VARCHAR(255)', 'email' => 'VARCHAR(255)'];
        $result = $this->tableModel->create('test_table', $columns);
        $this->assertTrue($result);
        
        $query = $this->db->query("SHOW TABLES LIKE 'test_table'");
        $this->assertEquals(1, $query->num_rows);
    }

    public function testTableExists()
    {
        $this->tableModel->create('test_table', []);
        $this->assertTrue($this->tableModel->exists('test_table'));
        $this->assertFalse($this->tableModel->exists('non_existent_table'));
    }

    public function testSaveTableSuccessfully()
    {
        $userId = 1;
        $tableName = 'test_table';
        $result = $this->tableModel->saveTable($userId, $tableName);
        $this->assertTrue($result);

        $query = $this->db->query("SELECT * FROM user_tables WHERE user_id = $userId AND table_name = '$tableName'");
        $this->assertEquals(1, $query->num_rows);
    }

    public function testDeleteTableSuccessfully()
    {
        $this->tableModel->create('test_table', []);
        $this->tableModel->saveTable(1, 'test_table');

        $result = $this->tableModel->delete('test_table');
        $this->assertTrue($result['success']);

        $query = $this->db->query("SHOW TABLES LIKE 'test_table'");
        $this->assertEquals(0, $query->num_rows);
    }

    public function testDeleteNonExistentTable()
    {
        $result = $this->tableModel->delete('non_existent_table');
        $this->assertFalse($result['success']);
    }

    public function testGetTablesByUser()
    {
        $userId = 1;
        $this->tableModel->saveTable($userId, 'table1');
        $this->tableModel->saveTable($userId, 'table2');

        $resultTables = $this->tableModel->getTablesByUser($userId);
        
        $this->assertCount(3, $resultTables);
        $this->assertEquals('table1', $resultTables['tables'][0]['name']);
        $this->assertEquals('table2', $resultTables['tables'][1]['name']);
    }

    public function testRenameTableSuccessfully()
    {
        $this->tableModel->create('old_name', []);
        $this->tableModel->saveTable(1, 'old_name');

        $result = $this->tableModel->rename('old_name', 'new_name');
        $this->assertTrue($result['success']);

        $this->assertTrue($this->tableModel->exists('new_name'));
        $this->assertFalse($this->tableModel->exists('old_name'));
    }

    public function testRenameTableToExistingName()
    {
        $this->tableModel->create('table1', []);
        $this->tableModel->create('table2', []);

        $result = $this->tableModel->rename('table1', 'table2');
        $this->assertFalse($result['success']);
    }

    public function testUpdateTableOrderSuccessfully()
    {
        $userId = 1;
        $this->tableModel->saveTable($userId, 'table1');
        $this->tableModel->saveTable($userId, 'table2');
        $this->tableModel->saveTable($userId, 'table3');

        $resultTables = $this->tableModel->getTablesByUser($userId);
        $tables = $resultTables['tables'];
        $newOrder = array_column($tables, 'id');
        sort($newOrder);

        $result = $this->tableModel->updateTableOrder($userId, array_reverse($newOrder));
        $this->assertEquals('Table order updated successfully.', $result['message']);
        $this->assertTrue($result['success']);
    }

    public function testUpdateTableOrderFailsOnInvalidData()
    {
        $userId = 1;
        $newOrder = ['invalid', 'data'];
    
        $result = $this->tableModel->updateTableOrder($userId, $newOrder);
    
        $this->assertFalse($result['success'], 'Expected the update to fail with invalid data.');
        
        $this->assertStringContainsString('Error updating table order', $result['message']);
    }

    public function testUpdateTableOrderFailsOnEmptyData() 
    {
        $userId = 1;
        $newOrder = [];

        $result = $this->tableModel->updateTableOrder($userId, $newOrder);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Error updating table order: Invalid table order data', $result['message']);
    }
}