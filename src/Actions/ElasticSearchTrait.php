<?php

namespace DishCheng\LaraLumenElasticSearch\Actions;

use DishCheng\LaraLumenElasticSearch\Traits\ElasticsearchClientTrait;

/**
 * https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/search_operations.html
 *
 * Class ElasticSearchSearchActions
 * @package DishCheng\LaraLumenElasticSearch\Actions
 */
trait ElasticSearchTrait
{
    use ElasticsearchClientTrait;

    
    public static function getEsClient()
    {
        $instance=new self();
        return $instance->getElasticsearchClient($instance->es_config);
    }

    public static function createIndex($index, $body=[])
    {
        $params=[
            'index'=>$index
        ];
        if ($body!=[]) {
            $params['body']=$body;
        }
        if (!self::exists($index)) {
            return self::getEsClient()->indices()->create($params);
        } else {
            throw new \Exception('Index Exists');
        }
    }


    public static function deleteIndex($index)
    {
        if (self::exists($index)) {
            return self::getEsClient()->indices()->delete(['index'=>$index]);
        }
    }


    public static function exists($index)
    {
        return self::getEsClient()->indices()->exists(['index'=>$index]);
    }


    public static function modifyIndex($params)
    {
        return self::getEsClient()->indices()->putSettings($params);
    }

    public static function getIndex($params)
    {
        return self::getEsClient()->indices()->getSettings($params);
    }


    public static function putMapping($params)
    {
        return self::getEsClient()->indices()->putMapping($params);
    }


    public static function getMapping($params)
    {
        return self::getEsClient()->indices()->getMapping($params);
    }


    /**
     * createSingleDocument
     * https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/indexing_documents.html
     * @param $index
     * @param $body
     * @param null $id
     * @param array $additional_params
     * @return array
     */
    public static function createSingleDocument($index, $body, $id=null, array $additional_params=[])
    {
        $params=[
            'index'=>$index,
            'body'=>$body,
        ];
        if (!is_null($id)) {
            $params['id']=$id;
        }
        if (!blank($additional_params)) {
            $params=array_merge($params, $additional_params);
        }
        return self::getEsClient()->index($params);
    }


    /**
     * createMultiDocument
     * https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/indexing_documents.html
     * @param $params
     * @return array
     */
    public static function createMultiDocument(array $params)
    {
        return self::getEsClient()->bulk($params);
    }


    /**
     * getDocument by index and id
     * https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/getting_documents.html
     * @param $index
     * @param $id
     * @return array
     */
    public static function getDocument($index, $id)
    {
        return self::getEsClient()->get([
            'index'=>$index,
            'id'=>$id
        ]);
    }


    /**
     * updateDocumentPartial 部分更新文档
     * https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/updating_documents.html
     * @param $index
     * @param $id
     * @param array $body
     * @return array
     */
    public static function updateDocumentPartial($index, $id, array $body=[])
    {
        $params=[
            'index'=>$index,
            'id'=>$id,
            'body'=>$body
        ];
        return self::getEsClient()->update($params);
    }


    /**
     * updateOrInsertDocument 更新或插入单个文档
     * https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/updating_documents.html
     * @param $index
     * @param $id
     * @param array $doc
     * @return array
     */
    public static function updateOrInsertDocument($index, $id, array $doc=[])
    {
        $params=[
            'index'=>$index,
            'id'=>$id,
            'body'=>[
                'doc'=>$doc
            ]
        ];
        return self::getEsClient()->update($params);
    }


    /**
     * deleteDocument 删除某个文档
     * https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/deleting_documents.html
     * @param $index
     * @param $id
     * @return array
     */
    public static function deleteDocumentFromIndex($index, $id)
    {
        $params=[
            'index'=>$index,
            'id'=>$id,
        ];
        return self::getEsClient()->delete($params);
    }


    /**
     * https://www.elastic.co/guide/en/elasticsearch/reference/7.x/search-request-body.html#search-request-body
     * @param null $index
     * @param array $body
     * @return array
     */
    public static function eSearch($index=null, $body=[])
    {
        $params=[];
        if (!is_null($index)) {
            $params['index']=$index;
        }
        if (!empty($body)) {
            $params['body']=$body;
        }
//        dd($params);
        return self::getEsClient()->search($params);
    }


    /**
     * @param array $indices
     * @return array
     */
    public static function getIndicesStatus(array $indices=[])
    {
        $params=[];
        if ($indices!=[]) {
            $params['index']=$indices;
        }
        return self::getEsClient()->indices()->stats($params);
    }


    /**
     * Corresponds to curl -XGET localhost:9200/_nodes/stats
     * @param array $params
     * @return array
     */
    public static function getNodesStatus(array $params=[])
    {
        return self::getEsClient()->nodes()->stats($params);
    }


    /**
     * Corresponds to curl -XGET localhost:9200/_cluster/stats
     * @param array $params
     * @return array
     */
    public static function getClusterStatus(array $params=[])
    {
        return self::getEsClient()->cluster()->stats();
    }
}