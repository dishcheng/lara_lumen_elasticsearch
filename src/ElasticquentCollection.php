<?php
namespace DishCheng\LaraLumenElasticSearch;



class ElasticquentCollection extends \Illuminate\Database\Eloquent\Collection
{
    public $es_config;
    use ElasticquentCollectionTrait;
}