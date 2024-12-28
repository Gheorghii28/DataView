<?php

use PHPUnit\Framework\TestCase;
use Api\Model\Row;

class RowTest extends TestCase
{
    private $db;
    private $rowModel;

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

        $this->db->query("
            CREATE TABLE test_table (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                display_order INT
            )
        ");
        
        $this->db->query("INSERT INTO users (id, username, password) VALUES (1, 'testuser', 'password123')");

        $this->rowModel = new Row($this->db);
    }

    protected function tearDown(): void
    {
        $this->db->query("DROP TABLE IF EXISTS test_table");
        $this->db->close();
    }

    public function testGetRows()
    {
        $this->db->query("INSERT INTO test_table (user_id, name, email) VALUES (1, 'John Doe', 'john@example.com'), (1, 'Jane Doe', 'jane@example.com')");
        
        $rows = $this->rowModel->getRows('test_table', 1);
        
        $this->assertCount(2, $rows);
        $this->assertEquals('John Doe', $rows[0]['name']);
        $this->assertEquals('Jane Doe', $rows[1]['name']);
    }

    public function testInsertRowSuccessfully()
    {
        $data = ['user_id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'];
        $result = $this->rowModel->insertRow('test_table', $data);

        $this->assertTrue($result['success']);
        $this->assertEquals('Row successfully added.', $result['message']);

        $rows = $this->rowModel->getRows('test_table', 1);
        $this->assertCount(1, $rows);
        $this->assertEquals('John Doe', $rows[0]['name']);
    }

    public function testInsertRowWithError()
    {
        $data = ['invalid_column' => 'Invalid Data'];
        $result = $this->rowModel->insertRow('test_table', $data);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Unknown column', $result['message']);
    }

    public function testUpdateRowSuccessfully()
    {
        $this->db->query("INSERT INTO test_table (user_id, name, email) VALUES (1, 'Old Name', 'old@example.com')");
        
        $data = ['name' => 'Updated Name', 'email' => 'updated@example.com'];
        $result = $this->rowModel->updateRow('test_table', 1, $data);

        $this->assertTrue($result['success']);
        $this->assertEquals('Row successfully updated.', $result['message']);

        $rows = $this->rowModel->getRows('test_table', 1);
        $this->assertEquals('Updated Name', $rows[0]['name']);
    }

    public function testUpdateRowWithError()
    {
        $result = $this->rowModel->updateRow('non_existent_table', 1, ['name' => 'Test']);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('doesn\'t exist', $result['message']);
    }

    public function testDeleteRowSuccessfully()
    {
        $this->db->query("INSERT INTO test_table (user_id, name, email) VALUES (1, 'John Doe', 'john@example.com')");
        
        $result = $this->rowModel->deleteRow('test_table', 1, 1);

        $this->assertTrue($result['success']);
        $this->assertEquals('Row deleted successfully.', $result['message']);

        $rows = $this->rowModel->getRows('test_table', 1);
        $this->assertCount(0, $rows);
    }

    public function testDeleteRowWithNoRowsAffected()
    {
        $result = $this->rowModel->deleteRow('test_table', 999, 1);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('No row found', $result['message']);
    }

    public function testReorderRowsSuccessfully()
    {
        $this->db->query("INSERT INTO test_table (id, user_id, name, email, display_order) VALUES 
            (1, 1, 'John', 'john@example.com', 0), 
            (2, 1, 'Jane', 'jane@example.com', 1),
            (3, 1, 'Doe', 'doe@example.com', 2)
        ");

        $newOrder = [3, 1, 2]; // Doe -> John -> Jane
        $result = $this->rowModel->reorderRows('test_table', $newOrder);

        $this->assertTrue($result['success']);
        $this->assertEquals('Row order updated successfully.', $result['message']);

        $rows = $this->rowModel->getRows('test_table', 1);
        $this->assertEquals('Doe', $rows[0]['name']);  // display_order = 0
        $this->assertEquals('John', $rows[1]['name']); // display_order = 1
        $this->assertEquals('Jane', $rows[2]['name']); // display_order = 2
    }

    public function testReorderRowsWithError()
    {
        $this->db->query("INSERT INTO test_table (id, user_id, name, email, display_order) VALUES 
            (1, 1, 'John', 'john@example.com', 0), 
            (2, 1, 'Jane', 'jane@example.com', 1)
        ");

        // Invalid rowId in the order
        $newOrder = [999]; // rowId 999 does not exist
        $result = $this->rowModel->reorderRows('test_table', $newOrder);

        // Check that the operation fails
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Error validating row IDs', $result['message']);
    }
}

