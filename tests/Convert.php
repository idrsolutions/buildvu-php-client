<?php

require_once __DIR__ . "/../vendor/autoload.php";

use IDRsolutions\BuildVuPhpClient\Converter;

$baseEndpoint = "http://localhost:8080/buildvu-microservice-example-1.0.0-alpha/";

try {

    $previewUrl = Converter::convert(array(
        'baseEndpoint' => $baseEndpoint,
        'parameters' => array(
            'token' => 'token-if-required'
        ),
        'filePath' => __DIR__ . '/file.pdf',
        'outputDir' => __DIR__ . '/'
    ));
    echo $previewUrl;

} catch (Exception $e) {

    echo $e->getMessage();
    echo $e->getTrace();
    exit(1);
}
