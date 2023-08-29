<?php

namespace App\Models;

use App\Models\Filters\PermissionFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Permission extends \Spatie\Permission\Models\Permission
{
    use HasFactory,PermissionFilter;

    protected $fillable = [
        'name',
        'name_zh_cn',
        'method',
        'uri',
        'guard_name',
        'type',
    ];

    protected $hidden = [
        'guard_name',
	    'pivot'
    ];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return Carbon::instance($date)->toDateTimeString();
    }
}
