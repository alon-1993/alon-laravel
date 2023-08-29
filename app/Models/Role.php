<?php

namespace App\Models;

use App\Models\Filters\RoleFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Role extends \Spatie\Permission\Models\Role
{
    use HasFactory, RoleFilter, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'brief',
        'type',
        'status',
        'guard_name',
        'is_super',
        'is_show',
        'merchant_id',
    ];

    protected $hidden = [
        'guard_name',
	    'pivot'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_super' => 'bool',
    ];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return Carbon::instance($date)->toDateTimeString();
    }

    public function getName()
    {
        return $this->getAttribute('name');
    }

	public static function getDefaultRoleId(): array
	{
		return self::query()->where([
			'is_super' => 1
		])->get(['id'])->pluck('id')->toArray();
	}
}
