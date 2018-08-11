<?php
namespace Vanila\Controllers;

use Carbon\Carbon;

class EventController extends EventBase
{
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
}
