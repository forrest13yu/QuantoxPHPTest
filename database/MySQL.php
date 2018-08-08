<?php
class MySQL {
  private static $servername = "localhost";
  private static $username = "root";
  private static $password = "";
  private static $database = "quantox_test";
  private static $conn;
  public static function connect()
  {
    try {
      self::$conn = new PDO("mysql:host=".self::$servername.";dbname=".self::$database, self::$username, self::$password, array(PDO::ATTR_PERSISTENT => TRUE));
      self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      echo "Connected successfully";
    }
    catch(PDOException $e)
    {
      echo "Connection failed: " . $e->getMessage();
    }
  }

  public static function SQL($sql)
  {

    try {
      self::$conn->exec($sql);
        echo "New record created successfully";
    }
    catch(PDOException $e)
    {
      echo $sql . "<br>" . $e->getMessage();
    }
  }
}
  ?>
