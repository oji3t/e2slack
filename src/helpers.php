<?php

if (!function_exists('e2slack')) {
    function e2slack(Exception $e, $config = []){
        return \ExceptionToSlack\Notification::sendTo($e, $config);
    }
}
