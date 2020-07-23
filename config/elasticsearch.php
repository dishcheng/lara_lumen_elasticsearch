<?php

return [
    //https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/configuration.html#_building_the_client_from_a_configuration_hash
    'client_config_param'=>[
        'hosts'=>[
            [
                'host'=>'localhost',
                'port'=>'9200',
                'scheme'=>'http',
                'path'=>'',
                'user'=>'',
                'pass'=>''
            ],
        ],
        'retries'=>1,
    ]
];
