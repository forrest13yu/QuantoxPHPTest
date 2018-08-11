<?php
class FileExportController
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

    $sever_root = $_SERVER["HTTP_HOST"];

    header("Location:http://localhost/quantoxphptest/public/$FileName");
    exit();
  }
}
