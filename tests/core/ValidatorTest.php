<?php

use Api\Core\DbConnection;
use PHPUnit\Framework\TestCase;
use Api\Helper\Validator;
use Dotenv\Dotenv;

class ValidatorTest extends TestCase
{
    private $validator;
    private $dbConnection;
    private $db;

    protected function setUp(): void
    {
        
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.env.testing');
        $dotenv->load();
        
        $this->dbConnection = new DbConnection();
        $this->db = $this->dbConnection->getInstance();
        
        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        $this->db->query("DROP TABLE IF EXISTS test_table");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");
        
        $tableName = 'test_table';

        $this->db->query("CREATE TABLE $tableName (id INT PRIMARY KEY, name VARCHAR(255), age INT, user_id INT, display_order INT)");
        $this->db->query("INSERT INTO $tableName (id, name, age, user_id, display_order) VALUES (1, 'John Doe', 30, 1, 0)");
        $this->db->query("INSERT INTO $tableName (id, name, age, user_id, display_order) VALUES (2, 'Jane Smith', 30, 1, 1)");
        $this->db->query("INSERT INTO $tableName (id, name, age, user_id, display_order) VALUES (3, 'Max Müller', 30, 1, 2)");

        $this->validator = new Validator();
    }

    public function testCheckTableName()
    {
        // Test with valid table names
        $this->assertEquals($this->validator->checkTableName('valid_table_123')['message'], 'Table name is valid.', 'Valid table name with letters, numbers, and underscore');
        $this->assertEquals($this->validator->checkTableName('table123')['message'], 'Table name is valid.', 'Valid table name with only letters and numbers');
        $this->assertEquals($this->validator->checkTableName('table_name')['message'], 'Table name is valid.', 'Valid table name with underscore');
        $this->assertEquals($this->validator->checkTableName('TABLE123')['message'], 'Table name is valid.', 'Valid table name with uppercase letters');
        $this->assertEquals($this->validator->checkTableName('t')['message'], 'Table name is valid.', 'Valid single character table name');
        $this->assertEquals($this->validator->checkTableName('T123')['message'], 'Table name is valid.', 'Valid table name with a single uppercase letter and numbers');
        $this->assertEquals($this->validator->checkTableName('a_b_c_1_2_3')['message'], 'Table name is valid.', 'Valid table name with multiple underscores and numbers');

        // Test with invalid table names (contains characters outside of letters, numbers, and underscore)
        $this->assertEquals($this->validator->checkTableName('invalid-table')['message'], 'Table name can only contain letters, numbers, and underscores.', 'Invalid table name with hyphen');
        $this->assertEquals($this->validator->checkTableName('table@123')['message'], 'Table name can only contain letters, numbers, and underscores.', 'Invalid table name with special character @');
        $this->assertEquals($this->validator->checkTableName('table#123')['message'], 'Table name can only contain letters, numbers, and underscores.', 'Invalid table name with special character #');
        $this->assertEquals($this->validator->checkTableName('table$123')['message'], 'Table name can only contain letters, numbers, and underscores.', 'Invalid table name with special character $');
        $this->assertEquals($this->validator->checkTableName('table%123')['message'], 'Table name can only contain letters, numbers, and underscores.', 'Invalid table name with special character %');
        $this->assertEquals($this->validator->checkTableName('table!123')['message'], 'Table name can only contain letters, numbers, and underscores.', 'Invalid table name with special character !');
        $this->assertEquals($this->validator->checkTableName('table^123')['message'], 'Table name can only contain letters, numbers, and underscores.', 'Invalid table name with special character ^');
        $this->assertEquals($this->validator->checkTableName('table*123')['message'], 'Table name can only contain letters, numbers, and underscores.', 'Invalid table name with special character *');
        $this->assertEquals($this->validator->checkTableName('table_123$')['message'], 'Table name can only contain letters, numbers, and underscores.', 'Invalid table name with special character $ at the end');
        $this->assertEquals($this->validator->checkTableName('table&123')['message'], 'Table name can only contain letters, numbers, and underscores.', 'Invalid table name with special character &');

        // Test with numeric table names (should not be valid because it doesn't start with a letter)
        $this->assertEquals($this->validator->checkTableName('123table')['message'], 'Table name cannot start with a number.', 'Invalid table name starting with numbers');
        $this->assertEquals($this->validator->checkTableName('1_table')['message'], 'Table name cannot start with a number.', 'Invalid table name starting with number followed by underscore');

        // Test with empty table names (should not be valid)
        $this->assertEquals($this->validator->checkTableName('')['message'], 'Table name cannot be empty.', 'Invalid empty table name');

        // Test with very long table names (valid if it adheres to the pattern)
        $this->assertEquals($this->validator->checkTableName(str_repeat('a', 255))['message'], 'Table name is valid.', 'Valid table name with 255 characters of a');
        $this->assertEquals($this->validator->checkTableName(str_repeat('a', 256))['message'], 'Table name can only contain letters, numbers, and underscores.', 'Invalid table name longer than 255 characters');

        // Test with leading or trailing spaces (should not be valid)
        $this->assertEquals($this->validator->checkTableName(' table_123 ')['message'], 'Table name is valid.', 'Valid table name with leading/trailing spaces');
        $this->assertEquals($this->validator->checkTableName(' table_123')['message'], 'Table name is valid.', 'Valid table name with leading space');
        $this->assertEquals($this->validator->checkTableName('table_123 ')['message'], 'Table name is valid.', 'Valid table name with trailing space');
    }

    public function testCheckColumns()
    {
        // Test with valid columns (an array of valid column names and types)
        $validColumns = [
            'column1' => 'VARCHAR(255)',
            'column2' => 'INT',
            'column_3' => 'TEXT'
        ];
        $this->assertEquals($this->validator->checkColumns($validColumns)['message'], 'Columns are valid.', 'Valid columns array');

        // Test with invalid column names (invalid characters)
        $invalidColumnNames = [
            'column-name' => 'VARCHAR(255)',  // Invalid name with hyphen
            'age' => 'INT'
        ];
        $this->assertEquals($this->validator->checkColumns($invalidColumnNames)['message'], 'Invalid column name: column-name. Column name can only contain letters, numbers, and underscores.', 'Invalid column name with hyphen');

        // Test with invalid column types (e.g., non-SQL type)
        $invalidColumnTypes = [
            'name' => 'VARCHAR(255)',
            'age' => 'STRING'  // Invalid type
        ];
        $this->assertEquals($this->validator->checkColumns($invalidColumnTypes)['message'], 'Invalid column type for column \'age\'.', 'Invalid column type');

        // Test with empty columns (empty array)
        $this->assertEquals($this->validator->checkColumns([])['message'], 'Invalid columns. Columns array is empty.', 'Empty columns array');

        // Test with columns that are not an array (invalid input types)
        $this->assertEquals($this->validator->checkColumns('not an array')['message'], 'Invalid columns. Columns must be an array.', 'String instead of array');
        $this->assertEquals($this->validator->checkColumns(123)['message'], 'Invalid columns. Columns must be an array.', 'Number instead of array');
        $this->assertEquals($this->validator->checkColumns(null)['message'], 'Invalid columns. Columns must be an array.', 'Null instead of array');

        // Test with columns that have invalid names and types (mixed invalid)
        $mixedInvalidColumns = [
            'column-name' => 'VARCHAR(255)',  // Invalid name with hyphen
            'age' => 'STRING'  // Invalid type
        ];
        $this->assertEquals($this->validator->checkColumns($mixedInvalidColumns)['message'], 'Invalid column name: column-name. Column name can only contain letters, numbers, and underscores.', 'Mixed invalid column name and type');
    }

    public function testValidateColumnName()
    {
        // Valid column names (start with a letter and contain only letters, numbers, and underscores)
        $this->assertTrue($this->validator->validateColumnName('columnName'), 'Valid column name: columnName');
        $this->assertTrue($this->validator->validateColumnName('col_umn_123'), 'Valid column name: col_umn_123');
        $this->assertTrue($this->validator->validateColumnName('COLUMN123'), 'Valid column name: COLUMN123');
        $this->assertTrue($this->validator->validateColumnName('a'), 'Valid single letter column name');
        $this->assertTrue($this->validator->validateColumnName('col_name_1'), 'Valid column name with underscore and number');

        // Invalid column names (start with a number or contain invalid characters)
        $this->assertFalse($this->validator->validateColumnName('123column'), 'Invalid column name: starts with number');
        $this->assertFalse($this->validator->validateColumnName('column-name'), 'Invalid column name: contains hyphen');
        $this->assertFalse($this->validator->validateColumnName('column@name'), 'Invalid column name: contains @');
        $this->assertFalse($this->validator->validateColumnName('column#name'), 'Invalid column name: contains #');
        $this->assertFalse($this->validator->validateColumnName('column$name'), 'Invalid column name: contains $');
        $this->assertFalse($this->validator->validateColumnName('column%name'), 'Invalid column name: contains %');
        $this->assertFalse($this->validator->validateColumnName('column*name'), 'Invalid column name: contains *');
        $this->assertFalse($this->validator->validateColumnName('column^name'), 'Invalid column name: contains ^');

        // Invalid column names (too long, more than 255 characters)
        $longName = str_repeat('a', 256); // 256 characters
        $this->assertFalse($this->validator->validateColumnName($longName), 'Invalid column name: exceeds 255 characters');
    }

    public function testValidateAndExtractRequest()
    {
        // Test with valid request (all required fields are present)
        $request = [
            'name' => 'John',
            'age' => 30,
            'email' => 'john@example.com'
        ];
        $requiredFields = ['name', 'age', 'email'];

        $result = $this->validator->validateAndExtractRequest($request, $requiredFields);
        $this->assertEquals($result['message'], 'Validation successful.', 'Validation should be successful when all required fields are present.');
        $this->assertEquals($result['data'], $request, 'Extracted data should match the original request.');

        // Test with missing required field (e.g., "age" is missing)
        $request = [
            'name' => 'John',
            'email' => 'john@example.com'
        ];

        $result = $this->validator->validateAndExtractRequest($request, $requiredFields);
        $this->assertEquals($result['message'], 'Invalid request. Missing field: age.', 'Validation should fail when a required field is missing.');

        // Test with an empty request (missing all required fields)
        $request = [];
        $result = $this->validator->validateAndExtractRequest($request, $requiredFields);
        $this->assertEquals($result['message'], 'Invalid request. Missing field: name.', 'Validation should fail when a required field is missing in an empty request.');

        // Test with extra fields (request contains more than required fields)
        $request = [
            'name' => 'John',
            'age' => 30,
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ];
        $actualResult = [
            'name' => 'John',
            'age' => 30,
            'email' => 'john@example.com'
        ];
        $result = $this->validator->validateAndExtractRequest($request, $requiredFields);
        $this->assertEquals($result['message'], 'Validation successful.', 'Validation should be successful even with extra fields in the request.');
        $this->assertEquals($result['data'], $actualResult, 'The extracted data should include only the required fields.');

        // Test with incorrect field names in requiredFields (e.g., "username" instead of "name")
        $requiredFields = ['username', 'age', 'email'];
        $request = [
            'name' => 'John',
            'age' => 30,
            'email' => 'john@example.com'
        ];
        $result = $this->validator->validateAndExtractRequest($request, $requiredFields);
        $this->assertEquals($result['message'], 'Invalid request. Missing field: username.', 'Validation should fail when the required field name does not match.');
    }

    public function testValidateRowIdsSuccess()
    {
        $tableName = 'test_table';
        $rowIds = [1, 2, 3];

        $result = $this->validator->validateRowIds($this->db, $tableName, $rowIds);

        $this->assertEquals('Validation successful.', $result['message']);
    }

    public function testValidateRowIdsMissingIds()
    {
        $tableName = 'test_table';
        $rowIds = [1, 2, 4];

        $result = $this->validator->validateRowIds($this->db, $tableName, $rowIds);

        $this->assertEquals('Invalid row ID(s): 4', $result['message']);
    }

    public function testValidateRowIdsSQLException()
    {
        $tableName = 'test_fail_table';
        $rowIds = [1, 2, 3];

        $result = $this->validator->validateRowIds($this->db, $tableName, $rowIds);

        $this->assertStringContainsString('MySQL Exception: ', $result['message']);
    }

    public function testValidateRowDataTypes() {
        $tableColumns = [
            ['name' => 'id', 'type' => 'INT(11)'],
            ['name' => 'name', 'type' => 'VARCHAR(255)'],
            ['name' => 'age', 'type' => 'INT(11)'],
            ['name' => 'email', 'type' => 'VARCHAR(255)'],
        ];
        $validRowData = [
            'id' => 1,
            'name' => 'John Doe',
            'age' => 30,
            'email' => 'john@example.com',
        ];
        $invalidRowData = [
            'id' => 1,
            'name' => 'John Doe',
            'age' => 'thirty',
            'email' => 'john@example.com',
        ];
    
        $result = $this->validator->validateRowDataTypes($validRowData, $tableColumns);
        $this->assertEquals('Validation successful.', $result['message'], 'Valid data types should pass validation.');
    
        $result = $this->validator->validateRowDataTypes($invalidRowData, $tableColumns);
        $this->assertEquals("Invalid data type for column 'age'. Expected type: INT(11).", $result['message'], 'Invalid data type should return an error.');
    } 
    
    public function testUserHasAccessToTable() {
        $userId = 1;
        $tableName = 'test_table';

        $userTables = [
            ['id' => 1, 'name' => 'test_table'],
            ['id' => 2, 'name' => 'another_table']
        ];

        $getTablesByUser = function($userId) use ($userTables) {
            return ['tables' => $userTables];
        };

        $result = $this->validator->hasAccessToTable($userId, $tableName, $getTablesByUser);
        $this->assertEquals('Table access granted.', $result['message'], 'User should have access to the table.');
    }

    public function testUserDoesNotHaveAccessToTable() {
        $userId = 1;
        $tableNameNotAccessible = 'restricted_table';

        $userTables = [
            ['id' => 1, 'name' => 'test_table'],
            ['id' => 2, 'name' => 'another_table']
        ];

        $getTablesByUser = function($userId) use ($userTables) {
            return ['tables' => $userTables];
        };

        $result = $this->validator->hasAccessToTable($userId, $tableNameNotAccessible, $getTablesByUser);
        $this->assertEquals("Forbidden. You do not have permission to access this table.", $result['message'], 'User should not have access to the table.');
    }

    public function testMissingUserId() {
        $userId = null;
        $tableName = 'test_table';

        $userTables = [
            ['id' => 1, 'name' => 'test_table'],
            ['id' => 2, 'name' => 'another_table']
        ];

        $getTablesByUser = function($userId) use ($userTables) {
            return ['tables' => $userTables];
        };

        $result = $this->validator->hasAccessToTable($userId, $tableName, $getTablesByUser);
        $this->assertEquals('Unauthorized. User ID is missing.', $result['message'], 'Error expected when user ID is missing.');
    }

    public function testMissingTableName() {
        $userId = 1;
        $tableName = null;

        $userTables = [
            ['id' => 1, 'name' => 'test_table'],
            ['id' => 2, 'name' => 'another_table']
        ];

        $getTablesByUser = function($userId) use ($userTables) {
            return ['tables' => $userTables];
        };

        $result = $this->validator->hasAccessToTable($userId, $tableName, $getTablesByUser);
        $this->assertEquals('Table name is required.', $result['message'], 'Error expected when table name is missing.');
    }

    public function testUserHasNoTables() {
        $userId = 1;
        $tableName = 'test_table';
        $userTables = [];

        $getTablesByUser = function($userId) use ($userTables) {
            return ['tables' => $userTables];
        };

        $result = $this->validator->hasAccessToTable($userId, $tableName, $getTablesByUser);
        $this->assertEquals("Forbidden. You do not have permission to access this table.", $result['message'], 'User should not have access to the table if no tables are assigned.');
    }

    public function testUserAccessToNonExistentTable() {
        $userId = 1;
        $tableName = 'non_existent_table';

        $userTables = [
            ['id' => 1, 'name' => 'test_table'],
            ['id' => 2, 'name' => 'another_table']
        ];

        $getTablesByUser = function($userId) use ($userTables) {
            return ['tables' => $userTables];
        };

        $result = $this->validator->hasAccessToTable($userId, $tableName, $getTablesByUser);
        $this->assertEquals("Forbidden. You do not have permission to access this table.", $result['message'], 'User should not have access to the table that does not exist.');
    }
}
