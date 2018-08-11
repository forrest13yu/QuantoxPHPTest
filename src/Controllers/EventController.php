<?php
namespace Vanila\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use function test\JSONHandler;

use PDO;
require_once 'FileExportController.php';
require_once 'test.php';
class EventController
{
  private $pdo;
  private $h1;
  public function __construct(PDO $pdo)
  {
    $this->pdo = new PdoController($pdo);

    $h1 = new \JSONHandler();
		$h2 = new \CSVHandler();
		$h3 = new \XMLHandler();
		$h1->setSuccessor($h2);
		$h2->setSuccessor($h3);
    $this->h1 = $h1;
  }

  public function insert($request)
  {
    $data = $request->all;
    if($data != null)
    {
      $this->pdo->ExecSQL("INSERT INTO `event_counter_tmp`(`country`,`event`) VALUES('". $data["country"] . "','". $data["event"] ."')");
      return '{"status":"done"}';
    }else{
      throw new Exception('Data not pressent');
    }
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

    return $this->ReturnData($request, $countryes);
  }

  private function ReturnData($request, $countryes)
  {
    $param = (count($request->params) === 0 ? "" : $request->params["data_type"]);
    return $this->h1->handleRequest($param, $countryes);
  }

  public function test($request)
  {
    $dt = new Carbon($this->pdo->GetDBTimeStamp());
    $serverTime = $dt->addSeconds(-5);
    $select_sql = "SELECT COUNT(event) as `count`, `country`,`event` FROM `event_counter_tmp` WHERE `date` < '$serverTime' GROUP BY `country`,`event` ORDER BY `country`";
    $result = $this->pdo->GetElements($select_sql);

    $this->UpdateCount($result, $dt->toDateString());
    $this->RemoveOldCount($serverTime);
    return '{"stats":"done"}';
  }

  private function UpdateCount($result, $date)
  {
    $update_sql = "INSERT INTO `country`(`name`, `event_total_count`) VALUES (':name',':count') ON DUPLICATE KEY UPDATE `event_total_count`= `event_total_count`+ :count;";
    $bulk_update_sql = "";

    reset($result);
    $event = current($result);

    $country = $event["country"];
    $total = $event["count"];
    do{
      $this->UpdateEvents($country, $event['event'], $total, $date);
      $event = next($result);

      if(($event !== false) && ($country === $event["country"]))
      {
        $total += $event["count"];
      }else {
        $bulk_update_sql .= $this->pdo->build_pdo_query($update_sql, ["name"=>$country, "count"=>$total]);
        if($event !== false)
        {
          $total = $event["count"];
          $country = $event["country"];
        }
      }
    }while ($event !== false);

    $this->pdo->ExecSQL($bulk_update_sql);
  }

  private function UpdateEvents($country, $event, $daily_total, $date)
  {
    $select_even_sql = "SELECT * FROM `event`";
    $events = $this->pdo->GetElements($select_even_sql);
    $event_key = $this->searchForId($event, $events);

    if($event_key !== null)
    {
      $this->pdo->ExecSQL("INSERT INTO `event` (`name`) VALUES ('$event')");
    }
    $this->pdo->ExecSQL("INSERT INTO `event_counter` (`country`,`event`,`daily_total`,`date`) VALUES ('$country', '$event', $daily_total, '$date') ON DUPLICATE KEY UPDATE `daily_total`=`daily_total`+$daily_total");
  }

  function searchForId($id, $array) {
    foreach ($array as $key => $val) {
      if ($val['name'] === $id) {
        return $key;
      }
    }
    return null;
  }

  private function RemoveOldCount($serverTime)
  {
    return $this->pdo->ExecSQL("DELETE FROM `event_counter_tmp` WHERE `date` < '$serverTime'")->rowCount();
  }
}
