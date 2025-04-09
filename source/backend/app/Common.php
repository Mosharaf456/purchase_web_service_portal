<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */



////////////

if (!function_exists('log_message_custom')) {
    function log_message_custom(string $level, string $message, string $fileName = 'log')
    {
        // Load the Logger service
        $logger = \Config\Services::logger();

        // Optional: You can dynamically set the log file name based on the fileName argument.
        // For this example, we will use the default log file, but you could customize this as needed.
        // Write the log message at the specified log level
        // $logger->$level($message);
        $logger->log($level, "[{$fileName}] {$message}");

    }
}

// use App\Libraries\CustomFileHandler;
// use CodeIgniter\Log\Logger;
// use CodeIgniter\Log\LogLevel;
// use Config\Logger as LoggerConfig;

// if (!function_exists('log_message_custom')) {
//     function log_message_custom(string $level, string $message, string $filename = 'custom.log', string $folder = '')
//     {
//         $path = WRITEPATH . 'logs/' . trim($folder, '/') . '/';

//         if (!is_dir($path)) {
//             mkdir($path, 0777, true);
//         }

//         // Create handler manually
//         $handler = new CustomFileHandler(new LoggerConfig());
//         $handler->setPath($path);
//         $handler->setFilename($filename);

//         // Write to log
//         $handler->handle([
//             'level' => $level,
//             'message' => $message,
//             'context' => [],
//         ]);
//     }
// }
