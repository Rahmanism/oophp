<?php

class Files {

    public static $myVar = 'some value';

    public static function DoSomething() {
        echo self::$myVar;
    }

    private static $timesFileIsChecked = 0;

    public static function CheckImage($file) {
        self::$timesFileIsChecked++;
        $size = getimagesize($file);
        //echo '<pre>'; print_r($size); echo '</pre>';
        return (!empty($size) ) ? $size : 'Not an image';
    }

    public static function GetNumChecked() {
        return self::$timesFileIsChecked;
    }

}

?>
