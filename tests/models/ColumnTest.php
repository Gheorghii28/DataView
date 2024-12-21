<?php

use PHPUnit\Framework\TestCase;
use Api\Model\Column;
use Api\Model\Table;

class ColumnTest extends TestCase
{

    private $db;
    private $tableModel;
    private $columnModel;

    protected function setUp(): void
    {
        $this->db = new mysqli("localhost", "root", "", "data_view_test_db");
        if ($this->db->connect_error) {
            $this->fail("Failed to connect to the test database: " . $this->db->connect_error);
        }

        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        $this->db->query("DROP TABLE IF EXISTS test_table");
        $this->db->query("TRUNCATE TABLE user_tables");
        $this->db->query("TRUNCATE TABLE users");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");
        
        $this->db->query("INSERT INTO users (id, username, password) VALUES (1, 'testuser', 'password123')");

        $this->tableModel = new Table($this->db);
        $this->columnModel = new Column($this->db, $this->tableModel);
    }

    protected function tearDown(): void
    {
        $this->db->query("DROP TABLE IF EXISTS test_table");
        $this->db->close();
    }

    public function testGetColumns()
    {
        $columns = ['name' => 'VARCHAR(255)', 'email' => 'VARCHAR(255)'];
        $this->tableModel->create('test_table', $columns);
        $columns = $this->columnModel->getColumns('test_table');

        // Assert the total number of columns is 6 (id, created_at, user_id, display_order, name, email)
        $this->assertCount(6, $columns); 
        
        // Assert each system-generated column is present
        $this->assertEquals('id', $columns[0]['name']);
        $this->assertEquals('created_at', $columns[1]['name']);
        $this->assertEquals('user_id', $columns[2]['name']);
        $this->assertEquals('display_order', $columns[3]['name']);

        // Assert custom columns are also present and in correct order
        $this->assertEquals('name', $columns[4]['name']);
        $this->assertEquals('VARCHAR(255)', strtoupper($columns[4]['type']));
        $this->assertEquals('email', $columns[5]['name']);
        $this->assertEquals('VARCHAR(255)', strtoupper($columns[5]['type']));
    }

    public function testGetColumnType()
    {
        $this->tableModel->create('test_table', ['name' => 'VARCHAR(255)', 'email' => 'VARCHAR(255)']);
        $columnType = $this->columnModel->getColumnType('test_table', 'name');

        $this->assertEquals('varchar(255)', strtolower($columnType));
    }

    public function testRenameColumnSuccessfully()
    {
        $this->tableModel->create('test_table', ['old_name' => 'VARCHAR(255)']);
        $result = $this->columnModel->renameColumn('old_name', 'new_name', 'test_table');

        $this->assertTrue($result['success']);

        $columns = $this->columnModel->getColumns('test_table');
        $this->assertEquals('new_name', $columns[4]['name']);
    }

    public function testRenameNonExistentColumn()
    {
        $this->tableModel->create('test_table', ['name' => 'VARCHAR(255)']);
        $result = $this->columnModel->renameColumn('non_existent', 'new_name', 'test_table');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString("Column 'non_existent' does not exist", $result['message']);
    }

    public function testAddColumnSuccessfully()
    {
        $this->tableModel->create('test_table', ['name' => 'VARCHAR(255)']);
        $result = $this->columnModel->addColumn('test_table', ['email' => 'VARCHAR(255)']);

        $this->assertTrue($result['success']);

        $columns = $this->columnModel->getColumns('test_table');
        $this->assertCount(6, $columns);
        $this->assertEquals('email', $columns[5]['name']);
    }

    public function testDeleteColumnSuccessfully()
    {
        $this->tableModel->create('test_table', ['name' => 'VARCHAR(255)', 'email' => 'VARCHAR(255)']);
        $result = $this->columnModel->deleteColumn('test_table', 'email');

        $this->assertTrue($result['success']);

        $columns = $this->columnModel->getColumns('test_table');
        $this->assertCount(5, $columns);
        $this->assertEquals('name', $columns[4]['name']);
    }

    public function testDeleteNonExistentColumn()
    {
        $this->tableModel->create('test_table', ['name' => 'VARCHAR(255)']);
        $result = $this->columnModel->deleteColumn('test_table', 'non_existent');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString("Column 'non_existent' does not exist", $result['message']);
    }

    public function testReorderColumnsSuccessfully()
    {
        $this->tableModel->create('test_table', ['name' => 'VARCHAR(255)', 'email' => 'VARCHAR(255)', 'age' => 'INT']);
        $result = $this->columnModel->reorderColumns('test_table', ['age', 'email', 'name']);

        $this->assertTrue($result['success']);

        $columns = $this->columnModel->getColumns('test_table');
        $this->assertEquals('age', $columns[0]['name']);
        $this->assertEquals('email', $columns[1]['name']);
        $this->assertEquals('name', $columns[2]['name']);
    }

    public function testReorderColumnsWithNonExistentColumn()
    {
        $this->tableModel->create('test_table', ['name' => 'VARCHAR(255)', 'email' => 'VARCHAR(255)']);
        $result = $this->columnModel->reorderColumns('test_table', ['email', 'non_existent']);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString("Column non_existent does not exist", $result['message']);
    }
}
