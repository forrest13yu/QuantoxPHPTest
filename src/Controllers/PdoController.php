<?php
namespace Vanila\Controllers;

use PDO;

class PdoController
{
  private $pdo;
  public function __construct(PDO $pdo)
  {
      $this->pdo = $pdo;
  }

  public function ExecSQL($sql)
  {
    $handle = $this->pdo->prepare($sql);
    $handle->execute();
    return $handle;
  }

  public function build_pdo_query($sql, $param_array)
  {
    foreach ($param_array as $key=>$param) {
      $sql = str_replace(":".$key, $param, $sql);
    }
    return $sql;
  }

  public function GetElements($sql)
  {
    $handle = $this->ExecSQL($sql);
    return $handle->fetchAll(PDO::FETCH_ASSOC);
  }

  public function GetDBTimeStamp()
  {
    return $this->GetElements("SELECT CURRENT_TIMESTAMP")[0]["CURRENT_TIMESTAMP"];
  }

  public function searchForId($id, $array) {
    foreach ($array as $key => $val) {
      if ($val['name'] === $id) {
        return $key;
      }
    }
    return null;
  }
}
