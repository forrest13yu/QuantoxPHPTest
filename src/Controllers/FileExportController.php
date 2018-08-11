<?php
class FileExportController
{
  public static function toCSV($countryes)
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
}
