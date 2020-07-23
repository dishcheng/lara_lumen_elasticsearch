<?php

namespace DishCheng\LaraLumenElasticSearch\Traits;

use Elasticsearch\ClientBuilder;

trait ElasticsearchClientTrait
{
    /**
     * Get ElasticSearch Client
     *
     * @return \Elasticsearch\Client
     */
    public function getElasticsearchClient($es_config=[])
    {
        if ($es_config==[]) {
            $es_config=config('elasticsearch.client_config_param');
        }
        return ClientBuilder::fromConfig($es_config);
//        $res= ClientBuilder::fromConfig($this->es_config);
//        $res->indices()->exists(['index'=>'']);
    }
}
