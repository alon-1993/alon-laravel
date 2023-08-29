<?php

namespace App\Models;

use App\Enums\Admin\AdminStatus;
use App\Enums\Admin\TypeEnum as AdminType;
use App\Enums\AdminRelation\TypeEnum as AdminRelationType;
use App\Models\Filters\AdminFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable, HasRoles, HasApiTokens, AdminFilter;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected string $guard_name = 'custom';

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return Carbon::instance($date)->toDateTimeString();
    }

    public function isDisabled(): bool
    {
        return $this->getAttribute('status') == AdminStatus::DISABLED();
    }

    /**
     * @param string $name
     * @param array|string[] $abilities
     * @param DateTimeInterface|null $expiresAt
     * @param null $relationId
     * @return NewAccessToken
     */
    public function createToken(string $name, array $abilities = ['*'], DateTimeInterface $expiresAt = null, $merchantId = null, $parentId = null): NewAccessToken
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(40)),
            'abilities' => $abilities,
            'last_used_at' => now()
        ]);

        return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    public function updateToken($array)
    {
        $this->currentAccessToken()->update($array);
    }

    public function getPhone()
    {
        return $this->getAttribute('phone');
    }

    public function isSuper(): bool
    {
        $roles = Role::query()->where('is_super','=', 1)->get();
        return $this->hasAnyRole($roles);
    }

    public function getRoleIds(): \Illuminate\Support\Collection
    {
        return $this->roles->pluck('id');
    }

    public static function create($data): \Illuminate\Database\Eloquent\Model|Admin|\Illuminate\Database\Eloquent\Builder
    {
        $data['password'] = bcrypt($data['password'] ?? 'abc123');
        return self::query()->firstOrCreate([
			'type' => $data['type'],
            'phone' => $data['phone']
        ],$data);
    }

    public function merchants(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        //最后要关联的类,中间表,中间表里的本表的外键,中间表里被关联表的外键
        return $this->belongsToMany(Merchant::class, AdminRelation::class, 'admin_id', 'relation_id')
            ->where('admin_relations.type','=',AdminRelationType::MERCHANT())
            ->whereNull('admin_relations.deleted_at');
    }

    public function isMerchant(): bool
    {
        return $this->getAttribute('type') == AdminType::MERCHANT();
    }

    public function getMerchantId()
    {
        return admin()?->currentAccessToken()?->getAttribute('merchant_id');
    }

    public function getParentId()
    {
        return admin()?->currentAccessToken()?->getAttribute('parent_id');
    }

    public function getType()
    {
        return $this->getAttribute('type');
    }

    public function adminRelations()
    {
        return $this->hasMany(AdminRelation::class, 'admin_id','id');
    }

    public function getOrganizationId()
    {
        return $this->getAttribute('organization_id');
    }

    public function organization(): HasOne
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    public function scopeOrganization($query)
    {
        return $query->where('type', '=', AdminType::ORGANIZATION());
    }


    public function scopeNotOrganization(Builder $query): Builder
    {
        return $query->where('type', '!=', AdminType::ORGANIZATION());
    }
}
