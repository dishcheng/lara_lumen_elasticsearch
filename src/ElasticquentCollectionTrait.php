<?php

namespace DishCheng\LaraLumenElasticSearch;

use DishCheng\LaraLumenElasticSearch\Actions\ElasticSearchTrait;

/**
 * Elasticquent Collection Trait
 *
 * Elasticsearch functions that you
 * can run on collections of documents.
 */
trait ElasticquentCollectionTrait
{
    /**
     * @var int The number of records (ie. models) to send to Elasticsearch in one go
     * Also, the number of models to get from the database at a time using Eloquent's chunk()
     */
    static public $entriesToSendToElasticSearchInOneGo=500;
    use ElasticquentTrait;

//    protected $es_config;
//    /**
//     * @param mixed $es_config
//     */
//    public function setEsConfig($es_config): void
//    {
//        $this->es_config=$es_config;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getEsConfig()
//    {
//        return $this->es_config;
//    }


    /**
     * @return |null
     * @throws \Exception
     */
    public function addDocumentsToIndex()
    {
        if ($this->isEmpty()) {
            return null;
        }

        // Use an stdClass to store result of elasticsearch operation
        $result=new \stdClass;

        // Iterate according to the amount configured, and put that iteration's worth of records into elastic search
        // This is done so that we do not exceed the maximum request size
        $all=$this->all();
        $iteration=0;
        do {
            $chunk=array_slice($all, (0+($iteration*static::$entriesToSendToElasticSearchInOneGo)), static::$entriesToSendToElasticSearchInOneGo);
            $params=array();
            foreach ($chunk as $item) {
                $params['body'][]=array(
                    'index'=>array(
                        '_id'=>$item->getKey(),
                        '_index'=>$item->indexName(),
                    ),
                );
                $params['body'][]=$item->getIndexDocumentData();
            }
            $last_result=self::getEsClient()->bulk($params);
            if ((array_key_exists('errors', $last_result)&&$last_result['errors']!=false)||(array_key_exists('Message', $last_result)&&stristr('Request size exceeded', $last_result['Message'])!==false)) {
                throw new \Exception('ADD DOCUMENT FAILED , PLEASE CHECK DATA');
            }
            // Remove vars immediately to prevent them hanging around in memory, in case we have a large number of iterations
            unset($chunk, $params);
            ++$iteration;
        } while (count($all)>($iteration*static::$entriesToSendToElasticSearchInOneGo));
        return $last_result;
    }

    /**
     * Delete Document From Index
     *
     * @return array
     */
    public function deleteDocsFromIndex()
    {
        $all=$this->all();
        $params=array();
        foreach ($all as $item) {
            $params['body'][]=array(
                'delete'=>array(
                    '_id'=>$item->getKey(),
                    '_index'=>$item->indexName(),
                ),
            );
        }
        return self::getEsClient()->bulk($params);
    }
}
