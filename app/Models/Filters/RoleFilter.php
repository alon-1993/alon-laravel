<?php

namespace App\Models\Filters;

use Illuminate\Database\Eloquent\Builder;

trait RoleFilter
{
    use Filter;

    protected function nameFilter($name): Builder
    {
        return $this->builder->where('name', 'like', '%' . $name . '%');
    }

    protected function typeFilter($value): Builder
    {
        return $this->builder->where('type', '=', $value);
    }

    protected function isSuperFilter($value): Builder
    {
        return $this->builder->where('is_super', '=', $value);
    }
}
