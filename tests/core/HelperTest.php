<?php

use Api\Model\Column;
use Dotenv\Dotenv;
use Api\Core\DbConnection;
use Api\Helper\Helper;
use Api\Model\Row;
use Api\Model\Table;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    private $dbConnection;
    private $db;
    private $helper;
    private $tableModel;
    private $columnModel;
    private $rowModel;

    protected function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.env.testing');
        $dotenv->load();

        $this->dbConnection = new DbConnection();
        $this->db = $this->dbConnection->getInstance();

        $tablesData = [
            'table1' => [
                [
                    'id' => 1,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'age' => 29,
                    'user_id' => 1,
                    'display_order' => 0,
                ],
                [
                    'id' => 2,
                    'first_name' => 'Jane',
                    'last_name' => 'Doe',
                    'age' => 25,
                    'user_id' => 1,
                    'display_order' => 1,
                ],
                [
                    'id' => 3,
                    'first_name' => 'Doe',
                    'last_name' => 'John',
                    'age' => 30,
                    'user_id' => 1,
                    'display_order' => 2,
                ],
            ],
            'table2' => [],
            'table3' => [],
        ];

        // Create tables and insert data
        foreach ($tablesData as $tableName => $rows) {
            $this->db->query("DROP TABLE IF EXISTS $tableName");
            $this->db->query("CREATE TABLE IF NOT EXISTS $tableName (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(255) NOT NULL,
                last_name VARCHAR(255) NOT NULL,
                age INT NOT NULL,
                user_id INT NOT NULL,
                display_order INT DEFAULT 0
            )");

            foreach ($rows as $row) {
                $columns = implode(", ", array_keys($row));
                $values = implode(", ", array_map(function ($value) {
                    return "'" . $this->db->real_escape_string($value) . "'";
                }, array_values($row)));
            
                $query = sprintf("INSERT INTO %s (%s) VALUES (%s)", $tableName, $columns, $values);
                $this->db->query($query);
            }
        }

        $this->helper = new Helper();
        $this->tableModel = new Table($this->db);
        $this->columnModel = new Column($this->db, $this->tableModel);
        $this->rowModel = new Row($this->db);
    }

    protected function tearDown(): void
    {
        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        $this->db->query("DROP TABLE IF EXISTS table1");
        $this->db->query("DROP TABLE IF EXISTS table2");
        $this->db->query("DROP TABLE IF EXISTS table3");
        $this->db->query("SET FOREIGN" . "_KEY_CHECKS=1");
    }

    public function testUpdateOrder()
    {
        $tableRequest = [
            'order' => [2, 1, 0],
            'userId' => 1,
        ];
        $tableMethod = 'updateTableOrder';
        $tableRequiredFields = ['order', 'userId'];

        $tableResult = $this->helper->updateOrder($tableRequest, $this->tableModel, $tableMethod, $tableRequiredFields);

        $this->assertTrue($tableResult['success']);
        $this->assertEquals('Table order updated successfully.', $tableResult['message']);

        $columnRequest = [
            'order' => ['last_name', 'first_name', 'age'],
            'userId' => 1,
            'tableName' => 'table1',
        ];
        $columnMethod = 'reorderColumns';
        $columnRequiredFields = ['order', 'userId', 'tableName'];

        $columnResult = $this->helper->updateOrder($columnRequest, $this->columnModel, $columnMethod, $columnRequiredFields);

        $this->assertTrue($columnResult['success']);
        $this->assertEquals('Column order updated successfully.', $columnResult['message']);

        $rowRequest = [
            'order' => [2, 1, 3],
            'userId' => 1,
            'tableName' => 'table1',
        ];
        $rowMethod = 'reorderRows';
        $rowRequiredFields = ['order','userId', 'tableName'];

        $rowResult = $this->helper->updateOrder($rowRequest, $this->rowModel, $rowMethod, $rowRequiredFields);

        $this->assertTrue($rowResult['success']);
        $this->assertEquals('Row order updated successfully.', $rowResult['message']);
    }

    public function testUpdateOrderInvalidRequest()
    {
        $tableRequest = [
            'order' => [2, 1, 0],
        ];
        $tableMethod = 'updateTableOrder';
        $tableRequiredFields = ['order', 'userId'];

        $tableResult = $this->helper->updateOrder($tableRequest, $this->tableModel, $tableMethod, $tableRequiredFields);

        $this->assertFalse($tableResult['success']);
        $this->assertEquals('Invalid request format or missing fields.', $tableResult['message']);
    }

    public function testUpdateOrderInvalidOrder()
    {
        $tableRequest = [
            'order' => 'invalid',
            'userId' => 1,
        ];
        $tableMethod = 'updateTableOrder';
        $tableRequiredFields = ['order', 'userId'];

        $tableResult = $this->helper->updateOrder($tableRequest, $this->tableModel, $tableMethod, $tableRequiredFields);

        $this->assertFalse($tableResult['success']);
        $this->assertEquals('Invalid order format.', $tableResult['message']);
    }

    public function testUpdateOrderInvalidMethod()
    {
        $tableRequest = [
            'order' => [2, 1, 0],
            'userId' => 1,
        ];
        $tableMethod = 'invalidMethod';
        $tableRequiredFields = ['order', 'userId'];

        $tableResult = $this->helper->updateOrder($tableRequest, $this->tableModel, $tableMethod, $tableRequiredFields);

        $this->assertFalse($tableResult['success']);
        $this->assertEquals('The method invalidMethod does not exist in the model Api\Model\Table.', $tableResult['message']);
    }

    public function testGenerateColumnsSQL()
    {
        $columns = [
            'first_name' => 'VARCHAR(255)',
            'last_name' => 'VARCHAR(255)',
            'age' => 'INT',
        ];

        $columnsSQL = $this->helper->generateColumnsSQL($columns);

        $this->assertEquals("`id` INT AUTO_INCREMENT PRIMARY KEY, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `user_id` INT NOT NULL, `display_order` INT DEFAULT 0, `first_name` VARCHAR(255), `last_name` VARCHAR(255), `age` INT", $columnsSQL);
    }

    public function testExistColumnNameInTable()
    {
        $columnName = 'first_name';
        $tableName = 'table1';

        $result = $this->helper->existColumnNameInTable($columnName, $tableName, $this->db);

        $this->assertTrue($result);
    }
}