<?php

require_once __DIR__ . "/../vendor/autoload.php";

use IDRsolutions\BuildVuPhpClient\Converter;

$baseEndpoint = "http://localhost:8080/microservice-example/";

try {

    $previewUrl = Converter::convert(array(
        'baseEndpoint' => $baseEndpoint,
        'parameters' => array(
            'token' => 'token-if-required'
        ),
        // Upload a local file to the server.
        'filePath' => __DIR__ . 'path/to/file.pdf',
        // filePath takes precedence over conversion url. Remove file to use conversionUrl parameter.
        'conversionUrl' => 'http://path.to/file.pdf'
        //'outputDir' => __DIR__ . '/'
    ));
    echo $previewUrl;

} catch (Exception $e) {

    echo $e->getMessage();
    echo $e->getTrace();
    exit(1);
}
