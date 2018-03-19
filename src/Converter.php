<?php

namespace IDRsolutions\BuildVuPhpClient;

class Converter {

    const POLL_INTERVAL = 500; //ms
    const TIMEOUT = 10;  // seconds

    const ENDPOINT = 'endpoint';
    const PARAMETERS = 'parameters';
    const FILE_PATH = 'filePath';

    private static function progress($r) {
        $out = json_encode($r, JSON_PRETTY_PRINT) . "\r\n";
        print($out);

    }

    private static function failure($type) {
        $out = $type . " failed."  . "\r\n";
        print($out);
    }

    private static function handleProgress($r) {

        if ($r['state'] === "error") {
            self::progress(array(
                'state' => $r['state'],
                'previewPath' => $r['previewPath'],
                'downloadPath' => $r['downloadPath'],
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

    public static function convert($opt) {

        if (!array_key_exists(self::ENDPOINT, $opt) || !isset($opt[self::ENDPOINT])) {
            exit("Missing endpoint");
        }
        if (!array_key_exists(self::FILE_PATH, $opt) || !isset($opt[self::FILE_PATH])) {
            exit("Missing filePath");
        }
        if (!array_key_exists(self::PARAMETERS, $opt) || !isset($opt[self::PARAMETERS])) {
            exit("Missing parameters");
        }

        $endpoint = $opt[self::ENDPOINT];
        $filePath = $opt[self::FILE_PATH];
        $parameters = $opt[self::PARAMETERS];

        define('MULTIPART_BOUNDARY', '--------------------------'.microtime(true));
        define('FORM_FIELD', 'file');

        $header = 'Content-Type: multipart/form-data; boundary='.MULTIPART_BOUNDARY;
        $file  = file_get_contents($opt[self::FILE_PATH]);
        if (!$file) {
            exit("File not found");
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

        $context = stream_context_create($options);
        $result = file_get_contents($endpoint, false, $context);
        if ($result === FALSE) {    // ERROR
            self::failure("upload");
            exit("failed to upload\n");
        }

        $json = json_decode($result,true);
        $uuid = $json['uuid'];
        $retries = 0;
        $data = array('state' => '');

        while ($data['state'] !== 'processed') {
            $result = file_get_contents($endpoint . "?uuid=" . $uuid);
            if ($result === FALSE) {    // ERROR
                if ($retries > 3) {
                    self::failure("conversion");
                    exit("failed to convert\n");
                }
                $retries++;
            } else {
                $data = json_decode($result, true);
                if ($data['state'] === 'processed') {
                    self::handleProgress($data);
                    return;
                }
                self::handleProgress($data);
                sleep(self::POLL_INTERVAL / 1000);
            }
        }
    }
}
