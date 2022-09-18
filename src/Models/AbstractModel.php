<?php

namespace Spork\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Kregel\LaravelAbstract\AbstractEloquentModel;
use Kregel\LaravelAbstract\AbstractModelTrait;

class AbstractModel extends Model implements AbstractEloquentModel
{
    use AbstractModelTrait;

    public function getValidationCreateRules(): array
    {
        return [];
    }

    public function getValidationUpdateRules(): array
    {
        return [];
    }

    public function getAbstractAllowedFilters(): array
    {
        return [];
    }

    public function getAbstractAllowedRelationships(): array
    {
        return [];
    }

    public function getAbstractAllowedSorts(): array
    {
        return [];
    }

    public function getAbstractAllowedFields(): array
    {
        return [];
    }

    public function getAbstractSearchableFields(): array
    {
        return [];
    }
}
