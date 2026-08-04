<?php
class LProgress {
    public static function InitProgress() {
        // make sure apache does not gzip this type, else it would get buffered
        header('Content-Type: text/event-stream');
        // recommended to prevent caching of event data.
        header('Cache-Control: no-cache');
    }

    public static function DoProgress($aProgressHandler, $aMaximumNumber, $aCurrentNumber, $aMessage = "") {
        $d = array(
            "message" => $aMessage,
            "progress" => $aCurrentNumber / $aMaximumNumber * 100
        );
        echo "id: $aCurrentNumber" . PHP_EOL;
        echo "data: " . json_encode($d) . PHP_EOL;
        echo PHP_EOL;

        // push the data out by all force
        ob_flush();
        flush();
    }
}
