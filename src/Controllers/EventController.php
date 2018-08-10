<?php
namespace Vanila\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use PDO;

class EventController
{

  private $pdo;
  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  public function insert($request)
  {
    $data = $request->all;
    if($data != null)
    {
      $this->ExecSQL("INSERT INTO `event_counter_tmp`(`country`,`event`) VALUES('". $data["country"] . "','". $data["event"] ."')");
      return '{"status":"done"}';
    }else{
      throw new Exception('Data not pressent');
    }
  }

  public function get_all($request)
  {
    $sql= "SELECT `name` as `country` FROM `country` ORDER BY `event_total_count` LIMIT 5";
    $countryes = $this->GetElements($sql);
    $dt = new Carbon($this->GetDBTimeStamp());
    $date_from = $dt->addDays(-7)->toDateString();
    foreach ($countryes as $key=>$country) {
      $country_name = $country["country"];
      $events_sql= "SELECT SUM(`daily_total`) AS `daily_total` FROM `event_counter` WHERE `country`= '$country_name' AND `date` > '$date_from'";

      $events =$this->GetElements($events_sql);

      $countryes[$key]["event_count"] = $events[0]["daily_total"];
    }

    if(count($request->params) === 0)
    {
      return json_encode($countryes);
    }else{
      switch ($request->params["data_type"]) {
        case "json":
        return json_encode($countryes);
        break;
        case 'csv':
        return $this->toCSV($countryes);
        break;

        default:
        return json_encode($countryes);
      }
    }
  }

  private function toCSV($countryes)
  {
    $FileName = "_export.csv";
    $file = fopen($FileName,"w");

    $HeadingsArray=array();
    foreach($countryes[0] as $name => $value){
      $HeadingsArray[]=$name;
    }
    fputcsv($file,$HeadingsArray);

    foreach ($countryes as $country)
    {
      $valuesArray=array();
      foreach($country as $name => $value){
        $valuesArray[]=$value;
      }

      fputcsv($file, $valuesArray);
    }
    fclose($file);
    $sever_root = $_SERVER["HTTP_HOST"];
    header("Location: http://$sever_root/$FileName");
    exit();
  }

  public function test($request)
  {
    $dt = new Carbon($this->GetDBTimeStamp());
    $serverTime = $dt->addSeconds(-5);
    $select_sql = "SELECT COUNT(event) as `count`, `country`,`event` FROM `event_counter_tmp` WHERE `date` < '$serverTime' GROUP BY `country`,`event` ORDER BY `country`";
    $result = $this->GetElements($select_sql);

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
        $bulk_update_sql .= $this->build_pdo_query($update_sql, ["name"=>$country, "count"=>$total]);
        if($event !== false)
        {
          $total = $event["count"];
          $country = $event["country"];
        }
      }
    }while ($event !== false);

    $this->ExecSQL($bulk_update_sql);
  }

  private function UpdateEvents($country, $event, $daily_total, $date)
  {
    $select_even_sql = "SELECT * FROM `event`";
    $events = $this->GetElements($select_even_sql);
    $event_key = $this->searchForId($event, $events);

    if($event_key !== null)
    {
      $this->ExecSQL("INSERT INTO `event` (`name`) VALUES ('$event')");
    }
    $this->ExecSQL("INSERT INTO `event_counter` (`country`,`event`,`daily_total`,`date`) VALUES ('$country', '$event', $daily_total, '$date') ON DUPLICATE KEY UPDATE `daily_total`=`daily_total`+$daily_total");
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
    return $this->ExecSQL("DELETE FROM `event_counter_tmp` WHERE `date` < '$serverTime'")->rowCount();
  }

  private function GetDBTimeStamp()
  {
    return $this->GetElements("SELECT CURRENT_TIMESTAMP")[0]["CURRENT_TIMESTAMP"];
  }

  private function GetElements($sql)
  {
    $handle = $this->ExecSQL($sql);
    return $handle->fetchAll(PDO::FETCH_ASSOC);
  }

  private function ExecSQL($sql)
  {
    $handle = $this->pdo->prepare($sql);
    $handle->execute();
    return $handle;
  }

  private function build_pdo_query($sql, $param_array)
  {
    foreach ($param_array as $key=>$param) {
      $sql = str_replace(":".$key, $param, $sql);
    }
    return $sql;
  }
}
