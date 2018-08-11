<?php
namespace Vanila\Controllers;

use PDO;

require_once __DIR__ . '/../PdoHendler.php';

class EventBase
{
  protected $pdo;
  public function __construct(PDO $pdo)
  {
    $this->pdo = new \PdoHendler($pdo);
  }
}
