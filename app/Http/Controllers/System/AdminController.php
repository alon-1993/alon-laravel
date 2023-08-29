<?php

namespace App\Http\Controllers\System;

use App\Enums\Admin\AdminStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\Models\Admin;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{


    /**
     * 管理员列表
     * @param Request $request
     * @return JsonResponse
     */
	public function index(Request $request): JsonResponse
	{
		$res = Admin::query()->with(['roles:id,name'])->filter($request->all())
			->orderByDesc('id')->paginate($request->get('limit'));
		return $this->success($res);
	}

    /**
     * 管理员下拉列表
     * @param Request $request
     * @return JsonResponse
     */
	public function list(Request $request): JsonResponse
	{
		$res = Admin::query()
			->filter($request->all())
			->orderByDesc('id')
			->select(['id', 'name'])
			->paginate($request->get('limit'));
		return $this->success($res);
	}

    /**
     * 添加管理员
     * @param AdminRequest $request
     * @return JsonResponse
     */
	public function store(AdminRequest $request): JsonResponse
	{
		$data = $request->all();

        $defaultRoleIds = Role::query()->where('is_super', '=', 1)->get(['id'])->pluck('id')->toArray();
		$intersect = array_intersect($defaultRoleIds, $data['role_ids']);
		if (!empty($intersect)) {
			abort(403, '禁止建立系统默认角色');
		}
		$countRoleId = Role::query()->whereIn('id', $data['role_ids'])->count('id');
		if (empty($countRoleId)) {
			abort(404, '未查询到角色id所属角色');
		}
		if (count($data['role_ids']) != $countRoleId) {
			abort(400, '存在非法角色');
		}
		$admin = Admin::create($data);
		$admin->syncRoles($data['role_ids']);
        $this->adminSaved($admin, $data);
		return $this->success();
	}

    protected function adminSaved(Admin $admin, array $data){}

    /**
     * 修改管理员
     * @param AdminRequest $request
     * @param Admin $admin
     * @return JsonResponse
     */
	public function update(AdminRequest $request, Admin $admin): JsonResponse
	{
		$data = $request->all();

		$origRoleIds = $admin->getRoleIds()->toArray();

        $roleIds = Role::query()->where('is_super', '=', 1)
            ->get(['id'])->pluck('id')->toArray();
        $roleIds[] = config('permission.super_admin_role_id.organization');

		$intersect = array_intersect($roleIds, $origRoleIds);
		$diff = array_diff($intersect,$data['role_ids']);

		if (!empty($diff)) {
			abort(403, '当前用户为系统角色,禁止修改角色类型');
		}
		$roleIds = Role::query()->whereIn('id', $data['role_ids'])->get(['id'])->toArray();
		if (empty($roleIds)) {
			abort(400, '未查询到角色id所属角色');
		}

		if ($data['status'] == AdminStatus::DISABLED() && $admin->isSuper()) {
			abort(400, '超管用户不可禁用');
		}

		$fillData = [
			'phone' => $data['phone'],
			'email' => $data['email'],
			'name' => $data['name'],
			'status' => $data['status'],
			'avatar' => $data['avatar'] ?? null,
			'is_operator' => $data['is_operator'] ?? 0
		];
		$admin->fill($fillData);
		$admin->save();
		$admin->syncRoles($data['role_ids']);

		return $this->success();
	}


    /**
     * 删除管理员
     * @param Admin $admin
     * @return JsonResponse
     */
	public function destroy(Admin $admin): JsonResponse
	{
		if ($admin->isSuper()) {
			abort(403, '超级管理员禁止删除');
		}

        if($admin->getKey() === admin()->getKey()){
            abort(403, '请不要删除自己');
        }

		$admin->delete();
		return $this->success();
	}
}
