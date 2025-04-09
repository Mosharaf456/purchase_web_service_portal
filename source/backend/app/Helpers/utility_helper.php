<?php
function log_me($var = '', $firstNameOfTheFile = 'log')
{
    if (php_sapi_name() == 'cli') {
        if (!ini_get('date.timezone')) {
            date_default_timezone_set('UTC');
        }
    }

    $msg=print_r($var, true)."\n";
    // error_log($msg, 3, '/log.php');
    log_message_custom('error', $msg, $firstNameOfTheFile);
}