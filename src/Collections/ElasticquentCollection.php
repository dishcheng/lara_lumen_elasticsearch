<?php
namespace DishCheng\LaraLumenElasticSearch\Collections;


class ElasticquentCollection extends \Illuminate\Database\Eloquent\Collection
{
    public $es_config;
    use ElasticquentCollectionTrait;
}