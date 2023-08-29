<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\ModelHasRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleHasPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{

    public function query(Request $request)
    {
        return Role::query()->filter($request->all())->orderByDesc('id');
    }

    public function index(Request $request): JsonResponse
    {
        $res = $this->query($request)->with('permissions:id,name')
            ->paginate($request->get('limit'));
        return $this->success($res);
    }

    public function list(Request $request): JsonResponse
    {
        $res = $this->query($request)->select(['id', 'name'])->paginate($request->get('limit'));
        return $this->success($res);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->all();

        $this->validate($request, [
            'name' => [
                'required',
                Rule::unique('roles')
                    ->where('organization_id',admin()->getOrganizationId())
                    ->whereNull('deleted_at'),
                'max:255'
            ],
            'brief' => 'nullable|max:255',
        ], [
            'name.required' => '角色名称必填',
            'name.unique' => '角色名已存在',
            'brief.max' => '角色描述长度超限',
        ]);

        $data['guard_name'] = 'custom';
        $data['merchant_id'] = $request->get('merchant_id') ?? $request->get('parent_id') ?? 0;
        Role::query()->create($data);
        return $this->success();
    }

    public function update(Request $request, Role $role): \Illuminate\Http\JsonResponse
    {
        $data = $request->all();
        $this->validate($request, [
            'name' => [
                'required',
                Rule::unique('roles')
                    ->where('organization_id',admin()->getOrganizationId())
                    ->whereNull('deleted_at')->ignore($role->getKey()),
                'max:255'
            ],
            'brief' => 'nullable|max:255',
        ], [
            'name.required' => '角色名必填',
            'name.unique' => '角色名已存在',
            'name.max' => '角色名长度超限',
            'brief.max' => '角色描述长度超限',
        ]);

	    $defaultRoleIds = Role::getDefaultRoleId();
        if (in_array($role->getKey(), $defaultRoleIds)) {
            abort(403, $role->getName() . '为默认角色,禁止修改');
        }

        $data['merchant_id'] = $data['is_show'] ?? 0;
        $role->fill($data);
        $role->save();
        return $this->success();
    }


    public function destroy(Role $role): JsonResponse
    {
        $defaultRoleIds = Role::getDefaultRoleId();

        if (in_array($role->getKey(), $defaultRoleIds)) {
            abort(400, $role->getName() . '为默认角色禁止删除');
        }
        ModelHasRole::query()->where('role_id', '=', $role->getKey())->delete();
        RoleHasPermission::query()->where('role_id', '=', $role->getKey())->delete();
        $role->delete();
        return $this->success();
    }

    public function auth(Request $request, Role $role): JsonResponse
    {

        $data = $request->all();
        $this->validate($request, [
            'permissions' => 'array'
        ], [
            'permissions.array' => '角色权限类型错误',
        ]);
        $oldIds = $role->getAllPermissions()->pluck('id')->toArray();
        $newIds = Permission::query()->whereIn('name', $data['permissions'])
            ->where('type', '=', $role->getAttribute('type'))
            ->get()->pluck('id')->toArray();

        $old = array_diff($oldIds, $newIds);
        $new = array_diff($newIds, $oldIds);
        $role->givePermissionTo($new);
        RoleHasPermission::query()->where('role_id', '=', $role->getKey())
            ->whereIn('permission_id', $old)
            ->delete();
        return $this->success();
    }
}
