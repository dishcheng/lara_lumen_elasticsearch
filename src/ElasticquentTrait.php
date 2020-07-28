<?php

namespace DishCheng\LaraLumenElasticSearch;

use DishCheng\LaraLumenElasticSearch\Actions\ElasticSearchTrait;
use Exception;
use ReflectionMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Elasticquent Trait
 *
 * Functionality extensions for Elequent that
 * makes working with Elasticsearch easier.
 */
trait ElasticquentTrait
{
    use ElasticSearchTrait;

    /**
     * Uses Timestamps In Index
     *
     * @var bool
     */
    protected $usesTimestampsInIndex=true;

    /**
     * Is ES Document
     *
     * Set to true when our model is
     * populated by a
     *
     * @var bool
     */
    protected $isDocument=false;

    /**
     * Document Score
     *
     * Hit score when using data
     * from Elasticsearch results.
     *
     * @var null|int
     */
    protected $documentScore=null;

    /**
     * Document Version
     *
     * Elasticsearch document version.
     *
     * @var null|int
     */
    protected $documentVersion=null;


    /**
     * @var array
     */
    protected $highlight=[];

    /**
     * New Collection
     *
     * @param array $models
     * @return ElasticquentCollection
     */
    public function newCollection(array $models=array())
    {
        $collection=new ElasticquentCollection($models);
        $collection->es_config=$this->es_config;
        return $collection;
    }

    /**
     * Uses Timestamps In Index.
     */
    public function usesTimestampsInIndex()
    {
        return $this->usesTimestampsInIndex;
    }

    /**
     * Use Timestamps In Index.
     */
    public function useTimestampsInIndex($shouldUse=true)
    {
        $this->usesTimestampsInIndex=$shouldUse;
    }

    /**
     * Don't Use Timestamps In Index.
     *
     * @deprecated
     */
    public function dontUseTimestampsInIndex()
    {
        $this->useTimestampsInIndex(false);
    }

    /**
     * Get Mapping Properties
     *
     * @return array
     */
    public function getMappingProperties()
    {
        return $this->mappingProperties;
    }

    /**
     * Set Mapping Properties
     *
     * @param array $mapping
     * @internal param array $mapping
     */
    public function setMappingProperties(array $mapping=null)
    {
        $this->mappingProperties=$mapping;
    }

    /**
     * @return array
     */
    public function getHighlight(): array
    {
        return $this->highlight;
    }

    /**
     * @param array $highlight
     */
    public function setHighlight(array $highlight): void
    {
        $this->highlight=$highlight;
    }


    /**
     * Get Index Settings
     *
     * @return array
     */
    public function getIndexSettings()
    {
        return $this->indexSettings;
    }

    /**
     * Is Elasticsearch Document
     *
     * Is the data in this module sourced
     * from an Elasticsearch document source?
     *
     * @return bool
     */
    public function isDocument()
    {
        return $this->isDocument;
    }

    /**
     * Get Document Score
     *
     * @return null|float
     */
    public function documentScore()
    {
        return $this->documentScore;
    }

    /**
     * Document Version
     *
     * @return null|int
     */
    public function documentVersion()
    {
        return $this->documentVersion;
    }


    public function getIndexName()
    {
        return $this->indexName();
    }

    /**
     * Get Index Document Data
     *
     * Get the data that Elasticsearch will
     * index for this particular document.
     *
     * @return array
     */
    public function getIndexDocumentData()
    {
        return $this->toArray();
    }


    /**
     * @param array $query
     * @param array $extra_params
     * @return ElasticquentResultCollection
     */
    public static function searchByQuery($query=[], $extra_params=[])
    {
        $instance=new static;
        $params=[];
        if (!empty($query)) {
            $params['query']=$query;
        }
        if (!empty($extra_params)) {
            $params=array_merge($params, $extra_params);
        }
        $result=$instance->eSearch($instance->indexName(), $params);
        return static::hydrateElasticsearchResult($result);
    }


    /**
     * @return string
     * @throws Exception
     */
    public function getEsIdByPrimaryKey()
    {
        if (is_string($this->getKeyName())) {
            $id=$this->getKey();
        } elseif (is_array($this->getKeyName())) {
            $columns=$this->getKeyName();
            $id_arr=[];
            foreach ($columns as $column) {
                $id_arr[]=$this->getAttribute($column);
            }
            $id_arr=array_filter($id_arr);
            if (empty($id_arr)) {
                //es自定义id
                $id=null;
            } else {
                //程序定义id
                $id=implode('_', $id_arr);
            }
        } else {
            throw new \Exception('Primary Key Type ERROR');
        }
        return $id;
    }

//    /**
//     * Search
//     *
//     *
//     * @param array $body
//     *
//     * @return ElasticquentResultCollection
//     */
//    public static function complexSearch($body=[])
//    {
//        $instance=new static;
//        $result=$instance->search($instance->indexName(), $body);
//        return static::hydrateElasticsearchResult($result);
//    }

//    /**
//     * Partial Update to Indexed Document
//     *
//     * @return array
//     */
//    public function updateIndex()
//    {
//        $params=$this->getBasicEsParams();
//
//        // Get our document body data.
//        $params['body']['doc']=$this->getIndexDocumentData();
//
//        return $this->getElasticSearchClient()->update($params);
//    }


    /**
     * @param $id
     * @return Model|null
     */
    public static function getDocumentByKey($id)
    {
        $instance=new static();
        try {
            return $instance::getDocumentByKeyOrFail($id);
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @param $id
     * @return Model
     * @throws \ReflectionException
     */
    public static function getDocumentByKeyOrFail($id)
    {
        $instance=new static();
        $res_data=$instance::getDocument($instance->indexName(), $id);
        return self::newFromBuilderRecursive($instance, $res_data['_source']);
    }


    /**
     * 部分更新一个文档。(如果$extra未设置doc_as_upsert=true且文档id不存在，则抛出异常)
     * @param $id
     * @param $document_data
     * @param array $extra
     * @return array
     */
    public static function updatePartialDocumentByKeyOrFail($id, $document_data, $extra=[])
    {
        $instance=new static();
        return $instance::updateDocumentPartial($instance->indexName(), $id, array_merge([
            'doc'=>$document_data,
        ], $extra));
    }

    /**
     * 部分更新或插入一个文档
     * @param $id
     * @param $document_data
     * @return array
     */
    public static function updatePartialOrAddDocumentByKey($id, $document_data)
    {
        return self::updatePartialDocumentByKeyOrFail($id, $document_data, [
            "doc_as_upsert"=>true
        ]);
    }


    /**
     * @param bool $getIdIfPossible
     * @param null $limit
     * @param null $offset
     * @return array
     * @throws Exception
     */
    public function getBasicEsParams($getIdIfPossible=true, $limit=null, $offset=null)
    {
        $params=array(
            'index'=>$this->indexName(),
        );
        if ($getIdIfPossible&&$this->getEsIdByPrimaryKey()) {
            $params['id']=$this->getEsIdByPrimaryKey();
        }

        if (is_numeric($limit)) {
            $params['size']=$limit;
        }

        if (is_numeric($offset)) {
            $params['from']=$offset;
        }
        return $params;
    }

    /**
     * Build the 'fields' parameter depending on given options.
     *
     * @param bool $getSourceIfPossible
     * @param bool $getTimestampIfPossible
     * @return array
     */
    private function buildFieldsParameter($getSourceIfPossible, $getTimestampIfPossible)
    {
        $fieldsParam=array();

        if ($getSourceIfPossible) {
            $fieldsParam[]='_source';
        }

        if ($getTimestampIfPossible) {
            $fieldsParam[]='_timestamp';
        }

        return $fieldsParam;
    }


    /**
     * @return bool
     */
    public static function mappingExists()
    {
        $instance=new static;

        $mapping=$instance::getModelMapping();

        return (empty($mapping)) ? false : true;
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getModelMapping()
    {
        $instance=new static;
        $params=$instance->getBasicEsParams(false);
        return $instance::getMapping($params);
    }


    /**
     * @param bool $ignoreConflicts
     * @return array
     * @throws Exception
     */
    public static function updateMapping($ignoreConflicts=false)
    {
        $instance=new static;

        $mapping=$instance->getBasicEsParams(false);

        if (is_null($instance->getMappingProperties())) {
            throw new Exception('Mapping Can\'t be Null');
        }
        $params=array(
            '_source'=>array('enabled'=>true),
            'properties'=>$instance->getMappingProperties(),
        );

        $mapping['body']=$params;

        return $instance::putMapping($mapping);
    }


    /**
     * @param null $shards
     * @param null $replicas
     * @return mixed
     * @throws Exception
     */
    public static function createModelIndex($shards=null, $replicas=null)
    {
        $instance=new static;
        $body=[];
        $settings=$instance->getIndexSettings();
        if ($settings) {
            $body['settings']=$settings;
        }

        if (!is_null($shards)) {
            $body['settings']['number_of_shards']=$shards;
        }

        if (!is_null($replicas)) {
            $body['settings']['number_of_replicas']=$replicas;
        }
        $mappingProperties=$instance->getMappingProperties();
        if (!is_null($mappingProperties)) {
            $body['mappings']=[
                '_source'=>array('enabled'=>true),
                'properties'=>$mappingProperties,
            ];
        }
        return $instance::createIndex($instance->indexName(), $body);
    }

    /**
     * Delete Index
     *
     * @return array
     */
    public static function deleteModelIndex()
    {
        $instance=new static;
        return $instance::deleteIndex($instance->indexName());
    }

    /**
     * Index Exists.
     *
     * Does this index exist?
     *
     * @return bool
     */
    public static function indexExists()
    {
        $instance=new static;
        return $instance::exists(['index'=>$instance->indexName()]);
    }


    /**
     * @param int $chunkSize
     * @param null $primaryKey
     */
    public static function allReIndex(int $chunkSize, $primaryKey=null, $coulumns=['*'], $connection='')
    {
        $instance=new static;
        if (is_null($primaryKey)) {
            $primaryKey=$instance->getKeyName();
        }
        if ($connection!='') {
            $instance->setConnection($connection);
        }
        $instance->select($coulumns)
            ->chunkById($chunkSize, function ($docs) {
                $docs->addDocumentsToIndex();
            }, $primaryKey);
    }

    /**
     * New From Hit Builder
     *
     * @param array $hit
     * @return Model
     * @throws \ReflectionException
     */
    public function newFromHitBuilder($hit=array())
    {
        $key_name=$this->getKeyName();
        if (is_array($key_name)) {
            $key_name=implode('_', $key_name);
        }

        $attributes=$hit['_source'];

        if (isset($hit['_id'])) {
            $idAsInteger=intval($hit['_id']);
            $attributes[$key_name]=$idAsInteger ? $idAsInteger : $hit['_id'];
        }

        // Add fields to attributes
        if (isset($hit['fields'])) {
            foreach ($hit['fields'] as $key=>$value) {
                $attributes[$key]=$value;
            }
        }

        // Add fields to attributes
        $instance=$this::newFromBuilderRecursive($this, $attributes);

        if (isset($hit['highlight'])) {
            $instance->highlight=$hit['highlight'];
        }
        // In addition to setting the attributes
        // from the index, we will set the score as well.
        $instance->documentScore=$hit['_score'];

        // This is now a model created
        // from an Elasticsearch document.
        $instance->isDocument=true;

        // Set our document version if it's
        if (isset($hit['_version'])) {
            $instance->documentVersion=$hit['_version'];
        }

        return $instance;
    }


    /**
     * @param array $result
     * @return ElasticquentResultCollection
     */
    public static function hydrateElasticsearchResult(array $result)
    {
        $items=$result['hits']['hits'];
        return static::hydrateElasticquentResult($items, $meta=$result);
    }


    /**
     * @param array $items
     * @param null $meta
     * @return ElasticquentResultCollection
     */
    public static function hydrateElasticquentResult(array $items, $meta=null)
    {
        $instance=new static;

        $items=array_map(function ($item) use ($instance) {
            return $instance->newFromHitBuilder($item);
        }, $items);
        return $instance->newElasticquentResultCollection($items, $meta);
    }


    /**
     * @param Model $model
     * @param array $attributes
     * @param Relation|null $parentRelation
     * @return Model
     * @throws \ReflectionException
     */
    public static function newFromBuilderRecursive(Model $model, array $attributes=[], Relation $parentRelation=null)
    {
        $instance=$model->newInstance([], $exists=true);

        $instance->setRawAttributes((array)$attributes, $sync=true);

        // Load relations recursive
        static::loadRelationsAttributesRecursive($instance);
        // Load pivot
        static::loadPivotAttribute($instance, $parentRelation);

        return $instance;
    }


    /**
     * @param Model $model
     * @param array $items
     * @param Relation|null $parentRelation
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function hydrateRecursive(Model $model, array $items, Relation $parentRelation=null)
    {
        $instance=$model;
        $items=array_map(function ($item) use ($instance, $parentRelation) {
            // Convert all null relations into empty arrays
            $item=$item ?: [];

            return static::newFromBuilderRecursive($instance, $item, $parentRelation);
        }, $items);

        return $instance->newCollection($items);
    }


    /**
     * @param Model $model
     * @throws \ReflectionException
     */
    public static function loadRelationsAttributesRecursive(Model $model)
    {
        $attributes=$model->getAttributes();

        foreach ($attributes as $key=>$value) {
            if (method_exists($model, $key)) {
                $reflection_method=new ReflectionMethod($model, $key);

                // Check if method class has or inherits Illuminate\Database\Eloquent\Model
                if (!static::isClassInClass("Illuminate\Database\Eloquent\Model", $reflection_method->class)) {
                    $relation=$model->$key();

                    if ($relation instanceof Relation) {
                        // Check if the relation field is single model or collections
                        if (is_null($value)===true||!static::isMultiLevelArray($value)) {
                            $value=[$value];
                        }

                        $models=static::hydrateRecursive($relation->getModel(), $value, $relation);

                        // Unset attribute before match relation
                        unset($model[$key]);
                        $relation->match([$model], $models, $key);
                    }
                }
            }
        }
    }


    /**
     * @param Model $model
     * @param Relation|null $parentRelation
     */
    public static function loadPivotAttribute(Model $model, Relation $parentRelation=null)
    {
        $attributes=$model->getAttributes();

        foreach ($attributes as $key=>$value) {
            if ($key==='pivot') {
                unset($model[$key]);
                $pivot=$parentRelation->newExistingPivot($value);
                $model->setRelation($key, $pivot);
            }
        }
    }


    /**
     * @param array $models
     * @param null $meta
     * @return ElasticquentResultCollection
     */
    public function newElasticquentResultCollection(array $models=[], $meta=null)
    {
        return new ElasticquentResultCollection($models, $meta);
    }

    /**
     * Check if an array is multi-level array like [[id], [id], [id]].
     *
     * For detect if a relation field is single model or collections.
     *
     * @param array $array
     * @return boolean
     */
    private static function isMultiLevelArray(array $array)
    {
        foreach ($array as $key=>$value) {
            if (!is_array($value)) {
                return false;
            }
        }
        return true;
    }


    /**
     * @param $classNeedle
     * @param $classHaystack
     * @return bool
     * @throws \ReflectionException
     */
    private static function isClassInClass($classNeedle, $classHaystack)
    {
        // Check for the same
        if ($classNeedle==$classHaystack) {
            return true;
        }

        // Check for parent
        $classHaystackReflected=new \ReflectionClass($classHaystack);
        while ($parent=$classHaystackReflected->getParentClass()) {
            /**
             * @var \ReflectionClass $parent
             */
            if ($parent->getName()==$classNeedle) {
                return true;
            }
            $classHaystackReflected=$parent;
        }

        return false;
    }
}
