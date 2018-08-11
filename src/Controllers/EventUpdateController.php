<?php
namespace Vanila\Controllers;
use Exception;
use Carbon\Carbon;

class EventUpdateController extends EventBase
{
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
    $bulk_update_country_sql = "";
    $bulk_update_event_sql = "";

    reset($result);
    $event = current($result);

    $country = $event["country"];
    $total = $event["count"];
    do{
      $bulk_update_event_sql .= $this->UpdateEvents($country, $event['event'], $total, $date);
      $event = next($result);

      if(($event !== false) && ($country === $event["country"]))
      {
        $total += $event["count"];
      }else {
        $bulk_update_country_sql .= $this->pdo->build_pdo_query($update_sql, ["name"=>$country, "count"=>$total]);
        if($event !== false)
        {
          $total = $event["count"];
          $country = $event["country"];
        }
      }
    }while ($event !== false);

    $this->pdo->ExecSQL($bulk_update_country_sql);
    $this->pdo->ExecSQL($bulk_update_event_sql);
  }

  private function UpdateEvents($country, $event, $daily_total, $date)
  {
    $select_even_sql = "SELECT * FROM `event`";
    $events = $this->pdo->GetElements($select_even_sql);
    $event_key = $this->pdo->searchForId($event, $events);

    if($event_key !== null)
    {
      $this->pdo->ExecSQL("INSERT INTO `event` (`name`) VALUES ('$event')");
    }
    return "INSERT INTO `event_counter` (`country`,`event`,`daily_total`,`date`) VALUES ('$country', '$event', $daily_total, '$date') ON DUPLICATE KEY UPDATE `daily_total`=`daily_total`+$daily_total;";
  }

  private function RemoveOldCount($serverTime)
  {
    return $this->pdo->ExecSQL("DELETE FROM `event_counter_tmp` WHERE `date` < '$serverTime'")->rowCount();
  }
}
