<?php

namespace App\Http\Traits;


use App\Enum\CustomOrderByEnum;
use Illuminate\Database\Eloquent\Builder;

trait QueryUtilityTrait
{
    public function sort(Builder $builder, $sortType): Builder
    {
        switch ($sortType){
            case CustomOrderByEnum::ALPHA_ASC->value:
                $builder->orderBy('title');
                break;
            case CustomOrderByEnum::ALPHA_DESC->value:
                $builder->orderBy('title', 'DESC');
                break;
            case CustomOrderByEnum::CREATE->value:
                $builder->orderBy('created_at', 'DESC');
                break;
            case CustomOrderByEnum::UPDATE->value:
                $builder->orderBy('updated_at', 'DESC');
                break;
        }
        return $builder;
    }
}
