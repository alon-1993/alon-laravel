<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class PermissionRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:refresh {update=0 : 是否更新历史权限名}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '权限表刷新';


    public function handle(): int
    {
        $update = (bool)$this->argument('update');
        $routes = Route::getRoutes();
        $insert = [];
        $permissions = Permission::query()->selectRaw("CONCAT(type,'-',name) as type_name")->get()->pluck('type_name')->toArray();

        foreach ($routes as $route) {


            $uri = $route->uri;
            $method = $route->methods[0];

            $action = $route->action;
            if (!isset($action['domain'])) {
                continue;
            }
            $name = $action['as'] ?? '';
            $domain = $action['domain'] ?? null;
            $guard = $this->getGuard($domain);
            if ($uri == '/' || in_array($name, config('permission.white_list'))) {
                continue;
            }
            $nameArray = explode('.', $name);

            if ($nameArray[1] == 'list') {
                continue;
            }
            if (isset(config('permission.alias')[$name])) {
                $nameZhCn = config('permission.alias')[$name];
            } else {
                $groups = config('permission.groups');
                if (!isset($groups[$nameArray[0]])) {
                    abort(400, $nameArray[0] . '未在group中配置');
                }
                $methods = config('permission.methods');
                if (!isset($methods[$nameArray[1]])) {
                    abort(400, $nameArray[1] . '未在method中配置');
                }
                $nameZhCn = $groups[$nameArray[0]] . $methods[$nameArray[1]];
            }

            if (!in_array($guard . '-' . $name, $permissions)) {
                $insert[] = [
                    'name' => $name,
                    'name_zh_cn' => $nameZhCn,
                    'method' => $method,
                    'uri' => $uri,
                    'guard_name' => 'custom',
                    'type' => $guard,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } elseif ($update) {
                Permission::query()->where([
                    'name' => $name,
                    'type' => $guard
                ])->update([
                    'name' => $name,
                    'name_zh_cn' => $nameZhCn,
                    'method' => $method,
                    'uri' => $uri,
                    'guard_name' => 'custom',
                    'type' => $guard
                ]);
            }

        }
        Permission::query()->insert($insert);
        return self::SUCCESS;
    }


    protected function getGuard($domain): bool|string
    {
        return 'default';
    }
}
