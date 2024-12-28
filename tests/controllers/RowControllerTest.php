<?php

use Api\Core\DbConnection;
use PHPUnit\Framework\TestCase;
use Api\Controller\RowController;
use Api\Core\Response;
use Dotenv\Dotenv;

class RowControllerTest extends TestCase
{
    private $rowController;
    private $dbConnection;
    private $db;
    private $response;

    public function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.env.testing');
        $dotenv->load();

        $this->dbConnection = new DbConnection();
        $this->db = $this->dbConnection->getInstance();

        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        $this->db->query("DROP TABLE IF EXISTS test_table");
        $this->db->query("TRUNCATE TABLE user_tables");
        $this->db->query("TRUNCATE TABLE users");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");

        $this->db->query("CREATE TABLE test_table (id INT PRIMARY KEY, name VARCHAR(255), age INT, user_id INT, display_order INT)");
        $this->db->query("INSERT INTO users (id, username, password) VALUES (1, 'testuser', 'password123')");

        $this->response = new Response(shouldReturn: true);
        $this->rowController = new RowController($this->dbConnection, $this->response);
    }

    public function testDatabaseConnection()
    {
        $dbConnection = new DbConnection();
        $this->assertNotNull($dbConnection->getInstance());
    }

    public function testCreateRowSuccess()
    {
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");

        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'data' => [
                'name' => 'John Doe',
                'age' => 30
            ]
        ];

        $createRowResult = $this->rowController->create($request);

        $this->assertEquals(200, $createRowResult['status']);
        $this->assertEquals('Row successfully added.', $createRowResult['message']);
        $this->assertEquals(0, $createRowResult['data']['rowId']);
    }

    public function testCreateRowFailure()
    {
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");

        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'data' => [
                'name' => 'John Doe',
                'age' => 'thirty'
            ]
        ];

        $createRowResult = $this->rowController->create($request);

        $this->assertEquals(400, $createRowResult['status']);
        $this->assertStringContainsString('Invalid data type for column', $createRowResult['message']);
    }

    public function testUpdateRowSuccess()
    {
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");

        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'data' => [
                'name' => 'Jane Doe',
                'age' => 25
            ],
            'rowId' => 1
        ];

        $updateRowResult = $this->rowController->update($request);

        $this->assertEquals(200, $updateRowResult['status']);
        $this->assertEquals('Row successfully updated.', $updateRowResult['message']);
    }

    public function testUpdateRowFailure()
    {
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");

        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'data' => [
                'name' => 'Jane Doe',
                'age' => 'twenty-five'
            ],
            'rowId' => 1
        ];

        $updateRowResult = $this->rowController->update($request);

        $this->assertEquals(400, $updateRowResult['status']);
        $this->assertStringContainsString('Invalid data type for column', $updateRowResult['message']);
    }

    public function testDeleteRowSuccess()
    {
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");
        $this->db->query("INSERT INTO test_table (id, name, age, display_order, user_id) VALUES (1, 'John Doe', 30, 0, 1)");

        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'rowId' => 1
        ];

        $deleteRowResult = $this->rowController->delete($request);

        $this->assertEquals(200, $deleteRowResult['status']);
        $this->assertEquals('Row deleted successfully.', $deleteRowResult['message']);
    }

    public function testDeleteRowFailure()
    {
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");
        $this->db->query("INSERT INTO test_table (id, name, age, display_order, user_id) VALUES (1, 'John Doe', 30, 0, 1)");

        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'rowId' => 2
        ];

        $deleteRowResult = $this->rowController->delete($request);

        $this->assertEquals(500, $deleteRowResult['status']);
        $this->assertEquals('No row found or you do not have permission to delete this row.', $deleteRowResult['message']);
    }

    public function testReorderRowsSuccess()
    {
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");
        $this->db->query("INSERT INTO test_table (id, name, age, display_order) VALUES (1, 'John Doe', 30, 0)");
        $this->db->query("INSERT INTO test_table (id, name, age, display_order) VALUES (2, 'Jane Doe', 25, 1)");

        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'order' => [2, 1]
        ];

        $reorderRowsResult = $this->rowController->updateRowOrder($request);

        $this->assertEquals(200, $reorderRowsResult['status']);
        $this->assertEquals('Row order updated successfully.', $reorderRowsResult['message']);
    }

    public function testReorderRowsFailure()
    {
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");
        $this->db->query("INSERT INTO test_table (id, name, age, display_order) VALUES (1, 'John Doe', 30, 0)");
        $this->db->query("INSERT INTO test_table (id, name, age, display_order) VALUES (2, 'Jane Doe', 25, 1)");

        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'order' => [2, 3]
        ];

        $reorderRowsResult = $this->rowController->updateRowOrder($request);

        $this->assertEquals(500, $reorderRowsResult['status']);
        $this->assertStringContainsString('Invalid row ID(s):', $reorderRowsResult['message']);
    }
}