<?php
namespace Vanila\Controllers;

use PDO;

class EventBase
{
  protected $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = new PdoController($pdo);
  }
}
