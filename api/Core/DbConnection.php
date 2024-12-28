<?php

namespace Api\Core;
class DbConnection {

  private static $instance = NULL;

  public function getInstance() {
    if (!isset(self::$instance)) {
      require __DIR__ . '/../../config/config.php';

      $hostname = $_ENV['DB_SERVER'];
      $username = $_ENV['DB_USERNAME'];
      $password = $_ENV['DB_PASSWORD'];
      $database = $_ENV['DB_NAME'];
      
      self::$instance = new \mysqli($hostname, $username, $password, $database);

      if (self::$instance->connect_error) {
        throw new \Exception('Connection failed: ' . self::$instance->connect_error);
      }
    }
    return self::$instance;
  }
}
