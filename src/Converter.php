<?php

namespace IDRsolutions\BuildVuPhpClient;

class Converter {

    const POLL_INTERVAL = 500; //ms
    const TIMEOUT = 10;  // seconds

    const ENDPOINT = 'endpoint';
    const PARAMETERS = 'parameters';
    const FILE_PATH = 'filePath';

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

        if (!array_key_exists(self::ENDPOINT, $opt) || !isset($opt[self::ENDPOINT])) {
            self::exitWithError("Missing endpoint.");
        }
        if (!array_key_exists(self::FILE_PATH, $opt) || !isset($opt[self::FILE_PATH])) {
            self::exitWithError("Missing filePath.");
        }
        if (!array_key_exists(self::PARAMETERS, $opt) || !isset($opt[self::PARAMETERS])) {
            self::exitWithError("Missing parameters.");
        }
    }

    private static function createContext($opt) {

        $filePath = $opt[self::FILE_PATH];
        $parameters = $opt[self::PARAMETERS];

        define('MULTIPART_BOUNDARY', '--------------------------'.microtime(true));
        define('FORM_FIELD', 'file');

        $header = 'Content-Type: multipart/form-data; boundary='.MULTIPART_BOUNDARY;
        $file = file_get_contents($opt[self::FILE_PATH]);
        if (!$file) {
            self::exitWithError("File not found.");
        }

        $content =
            "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"".FORM_FIELD."\"; filename=\"".basename($opt[self::FILE_PATH])."\"\r\n".
            "Content-Type: application/zip\r\n\r\n".
            $file."\r\n--".MULTIPART_BOUNDARY."--\r\n";

        $options = array(
            'http' => array(
                'method' => "POST",
                'TIMEOUT' => self::TIMEOUT,
                'header' => $header,
                'content' => $content,
                self::FILE_PATH => $filePath,
                self::PARAMETERS => $parameters
            )
        );

        return stream_context_create($options);
    }

    private static function poll($endpoint, $result) {

        $json = json_decode($result, true);
        $retries = 0;
        $data = array('state' => '');

        while ($data['state'] !== 'processed') {
            $result = file_get_contents($endpoint . "?uuid=" . $json['uuid']);
            if (!$result) {    // ERROR
                if ($retries > 3) {
                    self::exitWithError("Failed to convert.");
                }
                $retries++;
            } else {
                $data = json_decode($result, true);
                if ($data['state'] === 'processed') {
                    self::handleProgress($data);
                    exit(0); // SUCCESS
                }
                self::handleProgress($data);
                sleep(self::POLL_INTERVAL / 1000);
            }
        }
    }

    private static function exitWithError($printStr) {
        fwrite(STDERR, $printStr);
        exit(1);
    }

    public static function convert($opt) {

        self::validateInput($opt);
        $endpoint = $opt[self::ENDPOINT];
        $context = self::createContext($opt);

        $result = file_get_contents($endpoint, false, $context);
        if (!$result) {    // ERROR
            self::exitWithError("Failed to upload.");
        }

        self::poll($endpoint, $result);
    }
}
