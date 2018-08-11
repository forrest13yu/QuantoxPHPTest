<?php
namespace Vanila\Controllers;

use Carbon\Carbon;

require_once __DIR__ . '/../ResponceHendler.php';

use PDO;

class EventGetController extends EventBase
{
  private $h1;

  public function __construct(PDO $pdo)
  {
    parent::__construct($pdo);
    $h1 = new \JSONHandler();
		$h2 = new \CSVHandler();
		$h3 = new \XMLHandler();

		$h1->setSuccessor($h2);
		$h2->setSuccessor($h3);
    $this->h1 = $h1;
  }

  public function get_all($request)
  {
    $sql= "SELECT `name` as `country` FROM `country` ORDER BY `event_total_count` LIMIT 5";
    $countryes = $this->pdo->GetElements($sql);
    $dt = new Carbon($this->pdo->GetDBTimeStamp());
    $date_from = $dt->addDays(-7)->toDateString();
    foreach ($countryes as $key=>$country) {
      $country_name = $country["country"];
      $events_sql= "SELECT SUM(`daily_total`) AS `daily_total` FROM `event_counter` WHERE `country`= '$country_name' AND `date` > '$date_from'";

      $events =$this->pdo->GetElements($events_sql);

      $countryes[$key]["event_count"] = $events[0]["daily_total"];
    }

    $param = (count($request->params) === 0 ? "" : $request->params["data_type"]);
    $return = $this->h1->handleRequest($param, $countryes);
    return $return;
  }
}
