<?php

namespace Api\Core;
class DbConnection {

  private static $instance = NULL;

  public static function getInstance() {
    if (!isset(self::$instance)) {
      $config = require __DIR__ . '/../../config/config.php';
      self::$instance = new \mysqli(
        $config['db']['hostname'], 
        $config['db']['username'], 
        $config['db']['password'], 
        $config['db']['database']
      );

      if (self::$instance->connect_error) {
        die('Connection failed: ' . self::$instance->connect_error);
      }
    }
    return self::$instance;
  }
}
