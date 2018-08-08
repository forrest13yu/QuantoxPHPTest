<?php
class Counter
{
    // Hold an instance of the class
    private static $instance;
    private $counterTota = 0;

    public static function singleton()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Counter;
        }
        return self::$instance;
    }

    public function incCounter()
    {
      return $this->counterTota++;
    }
}
?>
