<?php

namespace App\Core\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface RestFilterable
{
    /**
     * Handles the provided filter for querying, used by the Rest filtering facade.
     * @param string $field The name of the field to filter on.
     * @param string $operator The operator to filter with.
     * @param mixed $value The value to filter by.
     * @return Builder The modified query builder.
     */
    public function handleFilter(Builder $query, string $field, string $operator, mixed $value): Builder;
}
