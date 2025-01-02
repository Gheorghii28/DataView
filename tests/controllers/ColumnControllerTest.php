<?php
use Api\Core\DbConnection;
use PHPUnit\Framework\TestCase;
use Api\Controller\ColumnController;
use Api\Core\Response;
use Dotenv\Dotenv;

class ColumnControllerTest extends TestCase
{
    private $columnController;
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
        $this->db->query("TRUNCATE TABLE user_tables");
        $this->db->query("TRUNCATE TABLE users");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");
        
        $this->db->query("INSERT INTO users (id, username, password) VALUES (1, 'testuser', 'password123')");
        $this->db->query("INSERT INTO user_tables (id, user_id, table_name, table_order) VALUES (1, 1, 'test_table', 0)");
        $this->db->query("CREATE TABLE test_table (id INT AUTO_INCREMENT PRIMARY KEY)"); 

        $this->response = new Response(shouldReturn: true);
        $this->columnController = new ColumnController($this->dbConnection, $this->response);
    }

    public function testDatabaseConnection(): void
    {
        $dbConnection = new DbConnection();
        $this->assertNotNull($dbConnection->getInstance());
    }

    public function testCreateColumnSuccess(): void
    {
        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'columns' => [
                'name' => 'VARCHAR(255)',
                'age' => 'INT'
            ]
        ];

        $createColumnResult = $this->columnController->create($request);
        
        $this->assertEquals(200, $createColumnResult['status']);
        $this->assertEquals('Columns successfully added to the table.', $createColumnResult['message']);

        $queryResult = $this->db->query("SHOW COLUMNS FROM test_table");
        $columns = [];
        while ($row = $queryResult->fetch_assoc()) {
            $columns[] = $row['Field'];
        }

        $this->assertContains('name', $columns);
        $this->assertContains('age', $columns);
    }

    public function testCreateColumnNameValidationFailure()
    {
        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'columns' => [
                'invalid_column_name!' => 'VARCHAR(255)',
            ]
        ];

        $createColumnResult = $this->columnController->create($request);
        $invalidColumnName = array_keys($request['columns'])[0];
        
        $this->assertEquals(400, $createColumnResult['status']);
        $this->assertEquals("Invalid column name: $invalidColumnName. Column name can only contain letters, numbers, and underscores.", $createColumnResult['message']);
    }

    public function testCreateColumnTypeValidationFailure()
    {
        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'columns' => [
                'valid_column_name' => 'INVALID_TYPE',
            ]
        ];

        $createColumnResult = $this->columnController->create($request);
        $validColumnName = array_keys($request['columns'])[0];
        
        $this->assertEquals(400, $createColumnResult['status']);
        $this->assertEquals("Invalid column type for column '{$validColumnName}'.", $createColumnResult['message']);
    }

    public function testRenameColumnSuccess()
    {
        $this->db->query("ALTER TABLE test_table ADD COLUMN old_col VARCHAR(255)");
        $request = [
            'userId' => 1,
            'oldName' => 'old_col',
            'newName' => 'new_col',
            'tableName' => 'test_table'
        ];

        $renameColumnResult = $this->columnController->rename($request);

        $this->assertEquals(200, $renameColumnResult['status']);
        $this->assertEquals("The column '{$request['oldName']}' in the '{$request['tableName']}' table has been successfully renamed to '{$request['newName']}'.", $renameColumnResult['message']
        );

        $queryResult = $this->db->query("SHOW COLUMNS FROM test_table LIKE 'new_col'");

        $this->assertEquals(1, $queryResult->num_rows);
    }

    public function testRenameColumnValidationFailure()
    {
        $this->db->query("ALTER TABLE test_table ADD COLUMN old_col VARCHAR(255)");
        $request = [
            'userId' => 1,
            'oldName' => 'old_col',
            'newName' => '123_invalid_name',
            'tableName' => 'test_table'
        ];

        $renameColumnResult = $this->columnController->rename($request);

        $this->assertEquals(400, $renameColumnResult['status']);
        $this->assertEquals('Invalid new column name. The column name must start with a letter and contain only letters, numbers, and underscores.', $renameColumnResult['message']);
    }

    public function testDeleteColumnSuccess()
    {
        $this->db->query("ALTER TABLE test_table ADD COLUMN col_to_delete VARCHAR(255)");
        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'columnName' => 'col_to_delete'
        ];

        $deleteColumnResult = $this->columnController->delete($request);

        $this->assertEquals(200, $deleteColumnResult['status']);
        $this->assertEquals("Column '{$request['columnName']}' has been successfully deleted from table '{$request['tableName']}'.", $deleteColumnResult['message']);

        $queryResult = $this->db->query("SHOW COLUMNS FROM test_table LIKE 'col_to_delete'");
        
        $this->assertEquals(0, $queryResult->num_rows);
    }

    public function testUpdateColumnOrderSuccess()
    {
        $this->db->query("ALTER TABLE test_table ADD COLUMN first_col VARCHAR(255)");
        $this->db->query("ALTER TABLE test_table ADD COLUMN second_col VARCHAR(255)");
        $request = [
            'userId' => 1,
            'tableName' => 'test_table',
            'order' => ['second_col', 'first_col']
        ];

        $updateColumnOrderResult = $this->columnController->updateColumnOrder($request);
        
        $this->assertEquals(200, $updateColumnOrderResult['status']);
        $this->assertEquals('Column order updated successfully.', $updateColumnOrderResult['message']);
    }
}
