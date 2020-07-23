lara_lumen_elasticsearch
=====
The project inspired by `https://github.com/elasticquent/Elasticquent/`,but it didn't support elasticSearch 7.x now.

I don't suggest use in production now . Just tested in lumen6.x + elasticSearch 7.x

## ES Official Document
Based on `https://github.com/elastic/elasticsearch-php/tree/7.x`

`https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html`

## First Step
```
composer require dishcheng/lara_lumen_elasticsearch
```

## In lumen
move `vendor/dishcheng/lara_lumen_elastcisearch/config/elasticsearch.php` to `config/elasticsearch.php`


```php
$app->configure('elasticsearch');
$app->register(\DishCheng\LaraLumenElasticSearch\LaraElasticSearchProvider::class);
```

## How to use
### set model
```
<?php

namespace App\Models;

use DishCheng\LaraLumenElasticSearch\ElasticquentTrait;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use ElasticquentTrait;
    protected $keyType='string';

    protected $table='orders';
    
    /**
     * setIndexName
     * @return string
     */
    public function indexName()
    {
        return 'order_index';
    }

    /**
     * set es connect if not use config setting
     */
//    public $es_config=[
//        'hosts'=>[
//            [
//                'host'=>'localhost',
//                'port'=>'9200',
//                'scheme'=>'http',
//                'path'=>'',
//                'user'=>'',
//                'pass'=>''
//            ],
//        ],
//        'retries'=>1,
//    ];

    /**
     * The elasticsearch index settings.
     * @var array
     */
    protected $indexSettings=[
//        'analysis'=>[
//        ],
    ];

    /*
     * Set Mapping
     */
    protected $mappingProperties=[
        'username'=>[
            'type'=>'keyword',
        ],
        'phone'=>[
            'type' => 'text',
            'analyzer' => 'standard'
        ],
    ];
}

```

### create es index
```php
Order::createModelIndex(2, 0);
```

### delete es index
```php
Order::deleteModelIndex();
```

### index all  table
use `chunkBy`
```php
Order::allReIndex(3000, 'created_at');
```

### index some data
```php
$orders=Order::whereDate('created_at','>','2020-01-01')->get();
$res=$orders->addDocumentsToIndex();
```

### delete some data from index
```php
$orders=Order::whereDate('created_at','>','2020-01-01')->get();
 $res=$orders->deleteDocsFromIndex();
```

### get document by key or fail
```php
$res1=Order::getDocumentByKeyOrFail('xxxxxx');
```

### update partial document by key or fail
```php
Order::updatePartialDocumentByKeyOrFail('xxxxxx',
[
  'phone'=>'13000000000'
]);
```

### update or insert document by key
```php
Order::updatePartialOrAddDocumentByKey('xxxxxx',
[
 'phone'=>'13000000000',
]);
```

### get Index Mapping
```php
Order::getModelMapping();
```

### update Index Mapping
```php
Order::updateMapping();
```

### check Index Mapping exists
```php
Order::mappingExists();
```

### query data
```php
        $res=(Order::searchByQuery([
            'constant_score'=>[
                'filter'=>[
                    'term'=>[
                        'username'=>'Mr.Cai'
                    ]
                ]
            ],
        ], [
            'size'=>10,
            'from'=>0,
            '_source'=>[
                'include'=>["id", "username", "phone", 'travel_date'],
            ],
            "sort"=>[
                //order column only mapping type is date or int
                //once sort , return `_score` will be null
                'travel_date'=>[
                    'order'=>'desc'//or asc
                ]
            ],
//            "highlight"=>[
//                "pre_tags"=>["<tag1>"],
//                "post_tags"=>["</tag1>"],
////                "number_of_fragments"=>3,
////                "fragment_size"=>150,
//                "fields"=>[
//                    'username'=>[
//                        'type'=>'plain'
//                    ]
//                ]
//            ],
        ]));
dd($res, $res->getItems(), $res->totalHitsNumber(), $res->maxScore(),$res->getAggregations(),$res->getHits());
```



## License
Open-sourced software licensed under the MIT license.