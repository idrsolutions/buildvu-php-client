# BuildVu PHP Client #

BuildVu PHP Client is the PHP API for IDRSolutions' [BuildVu Microservice Example](https://github.com/idrsolutions/buildvu-microservice-example).

It functions as an easy to use, plug and play library that lets you use [BuildVu](https://www.idrsolutions.com/buildvu/) via a REST endpoint from PHP. 

For tutorials on how to deploy BuildVu to an app server, visit the [documentation](https://support.idrsolutions.com/hc/en-us/sections/360000444652-Deploy-BuildVu-to-an-app-server).

-----

# Installation #

```
composer require idrsolutions/buildvu-php-client
```

-----

# Usage #

## Example Conversion Script ##
```php
<?php

require_once __DIR__ . "/PATH/TO/vendor/autoload.php";

use IDRsolutions\BuildVuPhpClient\Converter;

$baseEndpoint = "http://localhost:8080/microservice-example/";
$endpoint = $baseEndpoint . 'buildvu';

$previewUrl = Converter::convert(array(
    'endpoint' => $endpoint,
    'parameters' => array(
        'token' => 'token-if-required'
    ),
    'filePath' => __DIR__ . '/file.pdf',
    'outputDir' => __DIR__ . '/'
));
echo $previewUrl;
```
## Command Line ##
```
myproject/
├── composer.json
├── composer.lock
├── conversion_location
│   ├── convert.php
│   ├── input_files
│   │   └── file.pdf
│   └── output
└── vendor
    ├── autoload.php
    ├── composer
    │   └── ...
    └── idrsolutions
        └── buildvu-php-client
            └── ...
```
#### Appropriate Script Changes ####
```php
<?php

require_once __DIR__ . "/../vendor/autoload.php";

use IDRsolutions\BuildVuPhpClient\Converter;

$baseEndpoint = "http://localhost:8080/microservice-example/";
$endpoint = $baseEndpoint . 'buildvu';

$previewUrl = Converter::convert(array(
    'endpoint' => $endpoint,
    'parameters' => array(
        'token' => 'token-if-required'
    ),
    'filePath' => __DIR__ . 'input_files/file.pdf',
    'outputDir' => __DIR__ . 'output/'
));
echo $previewUrl;
```

#### Execute ####

```
cd conversion_location
php convert.php
```
#### Output ####

```
{
    "state": "processing"
}
{
    "state": "processed",
    "previewPath": "output\/c0096728-3490-4f5f-96a8-0f20a5a1244c\/file\/index.html",
    "downloadPath": "output\/c0096728-3490-4f5f-96a8-0f20a5a1244c\/file.zip"
}
http://localhost:8080/buildvu-microservice-example-1.0.0-alpha/output/c0096728-3490-4f5f-96a8-0f20a5a1244c/file/index.html
```

## Hosted Script ##

This example uses XAMPP htdocs.

```
htdocs
├── buildvu
│   ├── composer.json
│   ├── composer.lock
│   ├── convert.php
│   └── vendor
│       ├── autoload.php
│       ├── composer
│       │   ├── ...
│       └── idrsolutions
│           └── buildvu-php-client
│               └── ...
└── conversion
    ├── input_files
    │   └── file.pdf
    └── output
```

#### Appropriate Script Changes ####
```
<?php

require_once __DIR__ . "/vendor/autoload.php";

use IDRsolutions\BuildVuPhpClient\Converter;

$baseEndpoint = "http://localhost:8080/microservice-example/";

try {

    $previewUrl = Converter::convert(array(
        'baseEndpoint' => $baseEndpoint,
        'parameters' => array(
            'token' => 'token-if-required'
        ),
        'filePath' => __DIR__ . '/../conversion/input_files/file.pdf',
        'outputDir' => __DIR__ . '/../conversion/output'
    ));
    echo $previewUrl;

} catch (Exception $e) {

    echo $e->getMessage();
    echo $e->getTrace();
    exit(1);
}
```

#### Execution ####

In this case, the Apache server is deployed at localhost:80. To execute the script, visit:

```localhost:80/buildvu/convert.php```

#### Output ####

The webpage will display the link to the preview:

```http://localhost:8080/buildvu-microservice-example-1.0.0-alpha/output/c0096728-3490-4f5f-96a8-0f20a5a1244c/file/index.html```

The downloaded zip will be available in ```htdocs/conversion/output```.

# Who do I talk to? #

Found a bug, or have a suggestion / improvement? Let us know through the Issues page.

Got questions? You can contact us [here](https://idrsolutions.zendesk.com/hc/en-us/requests/new).

-----

Copyright 2018 IDRsolutions

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

[http://www.apache.org/licenses/LICENSE-2.0](http://www.apache.org/licenses/LICENSE-2.0)

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
