<?php

require_once __DIR__ . "/../vendor/autoload.php";

use IDRsolutions\BuildVuPhpClient\Converter;

$baseEndpoint = "http://localhost:8080/microservice-example/";
$endpoint = $baseEndpoint . 'buildvu';

$converter = new Converter();

try {

    $converter->convert(array(
        'endpoint' => $endpoint,
        'parameters' => array(
            'token' => 'token-if-required'
        ),
        'filePath' => __DIR__ . '/file.pdf',
    ));

} catch (Exception $e) {

    echo $e->getMessage();
    echo $e->getTrace();
    exit(1);
}
