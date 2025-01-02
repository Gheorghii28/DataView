<?php
use PHPUnit\Framework\TestCase;
use Api\Core\DbConnection;
use Dotenv\Dotenv;

class DbConnectionTest extends TestCase
{
    private $dbConnection;
    private $db;

    protected function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.env.testing');
        $dotenv->load();

        $this->dbConnection = new DbConnection();
        $this->db = $this->dbConnection->getInstance();
    }

    protected function resetSingleton(): void
    {
        $reflection = new \ReflectionClass(DbConnection::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null);
    }

    protected function setEnvironmentVariables($server, $username, $password, $database): void
    {
        $_ENV['DB_SERVER'] = $server;
        $_ENV['DB_USERNAME'] = $username;
        $_ENV['DB_PASSWORD'] = $password;
        $_ENV['DB_NAME'] = $database;
    }

    public function testGetInstanceSuccessfulConnection(): void
    {
        $this->assertInstanceOf(\mysqli::class, $this->db);
        $this->assertTrue($this->db->ping());
    }

    public function testSingletonBehavior(): void
    {
        $db1 = $this->dbConnection->getInstance();
        $db2 = $this->dbConnection->getInstance();

        $this->assertSame($db1, $db2);
    }

    public function testQueryExecution(): void
    {
        $this->db->query("INSERT INTO test_table (id, user_id, name, age) VALUES (1, 1, 'John Doe', 30)");

        $result = $this->db->query('SELECT * FROM test_table LIMIT 1');

        $this->assertNotFalse($result);
        $this->assertGreaterThan(0, $result->num_rows);
    }

    #[\PHPUnit\Framework\Attributes\Group('last')]
    public function testConnectionFailure(): void
    {
        $this->resetSingleton();
        $this->setEnvironmentVariables('wrong_host', 'wrong_user', 'wrong_password', 'wrong_db');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Connection failed:');
        
        $dbConnection = new DbConnection();
        $dbConnection->getInstance();
    }

    protected function tearDown(): void
    {
        if ($this->db instanceof \mysqli) {
            $this->db->query('TRUNCATE TABLE test_table');
        }
    }
}
