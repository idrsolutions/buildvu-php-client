# BuildVu PHP Client #

BuildVu PHP Client is the PHP API for IDRSolutions' [BuildVu Microservice Example](https://github.com/idrsolutions/buildvu-microservice-example).

It functions as an easy to use, plug and play library that lets you use [BuildVu](https://www.idrsolutions.com/buildvu/) from PHP. 

-----

# Installation #

```
composer require idrsolutions/buildvu-php-client
```

-----

# Usage #

```php
<?php

require_once __DIR__ . "/vendor/autoload.php";

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

-----

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
