<?php

namespace DishCheng\LaraLumenElasticSearch\Builder;


use DishCheng\LaraLumenElasticSearch\Collections\ElasticquentResultCollection;
use Illuminate\Database\Query\Builder;

class EsBuilder
{
    public static function macroEsQueryBuilder()
    {
        \Illuminate\Database\Query\Builder::macro('esQuery', function ($query=[], $extra_params=[]) {
            $params=[];
            if (!empty($query)) {
                $params['query']=$query;
            }
            if (!empty($extra_params)) {
                $params=array_merge($params, $extra_params);
            }
            return $this->esSearchParams=$params;
        });

        \Illuminate\Database\Query\Builder::macro('runQuery', function () {
            $builder = $this;

            dd($builder);
            $result=$instance->eSearch($instance->esIndexName, $params);
            return static::hydrateElasticsearchResult($result);
        });
    }
}