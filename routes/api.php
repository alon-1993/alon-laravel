<?php


use App\Http\Controllers\System\AdminController;
use App\Http\Controllers\System\AuthController;
use App\Http\Controllers\System\FileController;
use App\Http\Controllers\System\PasswordController;
use App\Http\Controllers\System\PermissionController;
use App\Http\Controllers\System\RoleController;
use App\Http\Controllers\System\SmsCodeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('captcha', function () {
	return response()->json([
		'data' => app('captcha')->create('default', true),
		'msg' => '操作成功',
		'status' => 200
	]);
})->name('captcha');

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('verification_code', [SmsCodeController::class, 'store'])->name('sms.code');

Route::group([
	'middleware' => ['auth:sanctum', 'qb.permission:operate']
], function () {

	Route::patch('password/{admin}', [PasswordController::class, 'reset'])->name('passwords.reset');
	Route::put('password/{admin}', [PasswordController::class, 'update'])->name('passwords.update');
	Route::apiresource('downloads', App\Http\Controllers\System\BackendDownloadController::class, ['only' => 'index']);

	Route::post('logout', [AuthController::class, 'logout'])->name('logout');

	Route::post('file', [FileController::class, 'upload'])->name('files.upload');

	Route::get('permissions/list', [PermissionController::class, 'list'])->name('permissions.list');
	Route::apiresource('permissions', PermissionController::class, ['only' => 'index']);

	Route::get('admins/list', [AdminController::class, 'list'])->name('admins.list');
	Route::apiresource('admins', AdminController::class, ['except' => ['show']]);

	Route::patch('roles/{role}/auth', [RoleController::class, 'auth'])->name('roles.auth');
	Route::get('roles/list', [RoleController::class, 'list'])->name('roles.list');
	Route::apiresource('roles', RoleController::class, ['except' => ['show']]);


});
