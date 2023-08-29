<?php

use App\Models\Admin;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

if (!function_exists('admin')) {
	/**
	 * 当前管理员
	 * @return Authenticatable|null|Admin
	 */
	function admin(): Authenticatable|null|Admin
	{
		return Auth::user();
	}
}


if (!function_exists('filter_special')) {
	/**
	 * 过滤字符串中的特殊字符
	 */
	function filter_special($str): array|string
	{
		if (is_null($str)) {
			return '';
		}
		$search = [
			' ',
			'　',
			' ',
			'‭',
			'‬',
			chr(194) . chr(160),
			"\n",
			"\r",
			"\t",
			"\r\n",
			"\f",
			"\v",
		];
		return str_replace($search, '', $str);
	}
}


if (!function_exists('yuan2fen')) {
	/**
	 * 金额元转分
	 * @param $amount
	 * @param int $scale
	 * @return string
	 */
	function yuan2fen($amount, int $scale = 0): string
	{
		return bcmul($amount, 100, $scale);
	}
}


if (!function_exists('fen2yuan')) {
	/**
	 * 金额分转元
	 * @param int|null $amount
	 * @param int $scale
	 * @param bool $format
	 * @return int|string
	 */
	function fen2yuan(int $amount = null, int $scale = 2, bool $format = false): int|string
	{
		return empty($amount) ? 0 : ($format ? number_format(bcdiv($amount, 100, 5), $scale) : bcdiv($amount, 100, 2));
	}
}

/**
 * 将binging参数添加到sql的?中
 */
if (!function_exists('binging_into_sql')) {
	function binging_into_sql($bindings, $sql)
	{
		return array_reduce($bindings, function ($sql, $binding) {
			return preg_replace('/\?/', is_numeric($binding) ? $binding : "'" . $binding . "'", $sql, 1);
		}, $sql);
	}
}

/**
 * 从文件地址中截取文件名称
 */
if (!function_exists('url_basename')) {
	/**
	 * @param string $url
	 * @return string
	 */
	function url_basename(string $url): string
	{
		return \Illuminate\Support\Str::before(\Illuminate\Support\Str::afterLast($url, '/'), '?');
	}
}

if (!function_exists('route_display')) {
	/**
	 * 路由名称转中文
	 * @param string $route
	 * @return string
	 */
	function route_display(string $route = ''): string
	{
		if (empty($route)) {
			$route = Route::currentRouteName() ?? '';
		}
		$arr = explode('.', $route);
		$group = config('permission.groups')[$arr[0]] ?? '';
		$method = isset($arr[1]) ? config('permission.methods')[$arr[1]] : '';
		return $group . $method;
	}
}

if (!function_exists('route_group')) {
	/**
	 * 路由名称转中文
	 * @param string $route
	 * @return string
	 */
	function route_group(string $route = ''): string
	{
		if (empty($route)) {
			$route = Route::currentRouteName() ?? '';
		}
		$arr = explode('.', $route);
		return $arr[0] ?? '';
	}
}

//无中划线的uuid
if (!function_exists('unsigned_uuid')) {

	function unsigned_uuid(): string
	{
		return (string)Str::of(Str::uuid())->replace('-', '');
	}
}
