<?php

namespace IDRsolutions\BuildVuPhpClient;

if(!defined('STDIN'))  define('STDIN',  fopen('php://stdin',  'r'));
if(!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'w'));
if(!defined('STDERR')) define('STDERR', fopen('php://stderr', 'w'));

class Converter {

    const POLL_INTERVAL = 500; //ms
    const TIMEOUT = 10;  // seconds

    const BASE_ENDPOINT_KEY = 'baseEndpoint';
    const PARAMETERS_KEY = 'parameters';
    const FILE_PATH_KEY = 'filePath';
    const OUTPUT_DIR_KEY = 'outputDir';

    private static function progress($r) {
        fwrite(STDOUT, json_encode($r, JSON_PRETTY_PRINT) . "\r\n");
    }

    private static function handleProgress($r) {

        if ($r['state'] === "error") {
            self::progress(array(
                'state' => $r['state'],
                'error' => $r['error'])
            );
        } elseif ($r['state'] === "processed") {
            self::progress(array(
                'state' => $r['state'],
                'previewPath' => $r['previewPath'],
                'downloadPath' => $r['downloadPath'])
            );
        } else {
            self::progress(array(
                'state' => $r['state'])
            );
        }
    }

    private static function validateInput($opt) {

        if (!array_key_exists(self::BASE_ENDPOINT_KEY, $opt) || !isset($opt[self::BASE_ENDPOINT_KEY])) {
            self::exitWithError("Missing endpoint.");
        }
        if (!array_key_exists(self::FILE_PATH_KEY, $opt) || !isset($opt[self::FILE_PATH_KEY])) {
            self::exitWithError("Missing filePath.");
        }
        if (!array_key_exists(self::PARAMETERS_KEY, $opt) || !isset($opt[self::PARAMETERS_KEY])) {
            self::exitWithError("Missing parameters.");
        }
    }

    private static function createContext($opt) {

        $filePath = $opt[self::FILE_PATH_KEY];
        $parameters = $opt[self::PARAMETERS_KEY];

        define('MULTIPART_BOUNDARY', '--------------------------'.microtime(true));
        define('FORM_FIELD', 'file');

        $header = 'Content-Type: multipart/form-data; boundary='.MULTIPART_BOUNDARY;
        $file = file_get_contents($opt[self::FILE_PATH_KEY]);
        if (!$file) {
            self::exitWithError("File not found.");
        }

        $content =
            "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"".FORM_FIELD."\"; filename=\"".basename($filePath)."\"\r\n".
            "Content-Type: application/zip\r\n\r\n".
            $file."\r\n--".MULTIPART_BOUNDARY."--\r\n";

        $options = array(
            'http' => array(
                'method' => "POST",
                'TIMEOUT' => self::TIMEOUT,
                'header' => $header,
                'content' => $content,
                self::FILE_PATH_KEY => $filePath,
                self::PARAMETERS_KEY => $parameters
            )
        );
        return stream_context_create($options);
    }

    private static function poll($baseEndpoint, $result, $outputDir = NULL) {

        $json = json_decode($result, true);
        $retries = 0;
        $data = array('state' => '');

        while ($data['state'] !== 'processed') {
            $result = file_get_contents($baseEndpoint . "buildvu?uuid=" . $json['uuid']);
            if (!$result) {    // ERROR
                if ($retries > 3) {
                    self::exitWithError("Failed to convert.");
                }
                $retries++;
            } else {
                $data = json_decode($result, true);
                if ($data['state'] === 'processed') {
                    self::handleProgress($data);
                    if ($outputDir != NULL) {
                        self::download($baseEndpoint . $data['downloadPath'], $outputDir);
                    }
                    return $baseEndpoint . $data['previewPath'];  // SUCCESS
                }
                self::handleProgress($data);
                sleep(self::POLL_INTERVAL / 1000);
            }
        }
    }

    private static function download($downloadPath, $outputDir) {
        $fileName = pathinfo($downloadPath)['basename'];
        $fullOutputPath = $outputDir . $fileName;
        file_put_contents($fullOutputPath, fopen($downloadPath, 'r'));
    }

    private static function exitWithError($printStr) {
        fwrite(STDERR, $printStr);
        exit(1);
    }

    public static function convert($opt) {

        self::validateInput($opt);
        $baseEndpoint = $opt[self::BASE_ENDPOINT_KEY];
        $endpoint = $baseEndpoint . 'buildvu';
        $context = self::createContext($opt);

        $result = file_get_contents($endpoint, false, $context);
        if (!$result) {    // ERROR
            self::exitWithError("Failed to upload.");
        }

        $outputDir = NULL;
        if (array_key_exists(self::OUTPUT_DIR_KEY, $opt) || isset($opt[self::OUTPUT_DIR_KEY])) {
            $outputDir = $opt[self::OUTPUT_DIR_KEY];
        }

        return self::poll($baseEndpoint, $result, $outputDir);
    }
}
