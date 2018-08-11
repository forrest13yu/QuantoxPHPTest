<?php
class CVSExport
{
  public static function toCSV($countryes)
  {
    $FileName = "_export.csv";

    $file = fopen($FileName,"w");
    fputcsv($file,array_keys($countryes[0]));
    foreach ($countryes as $country)
    {
      fputcsv($file, array_values ($country));
    }
    
    fclose($file);
    $sever_root = str_replace('index.php', '', $_SERVER["HTTP_HOST"].'/'.$_SERVER["SCRIPT_NAME"]);

    header("Location:http://$sever_root$FileName");
    exit();
  }
}
