<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 *  默认成功返回
	 * @param array $data
	 * @param string $msg
	 * @return JsonResponse
	 */
	public function success(array $data = [], string $msg = '操作成功')
	{
		return response()->json([
			'status' => Response::HTTP_OK,
			'data' => $data ?? [],
			'msg' => $msg
		])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
	}

	/**
	 *  资源重复
	 * @param string $msg
	 * @return JsonResponse
	 */
	public function conflict(string $msg, array $data = [])
	{
		return response()->json([
			'status' => Response::HTTP_CONFLICT,
			'data' =>$data,
			'msg' => $msg
		])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
	}

	/**
	 *  禁止操作
	 * @param string $msg
	 * @return JsonResponse
	 */
	public function forbidden(string $msg, array $data = [])
	{
		return response()->json([
			'status' => Response::HTTP_FORBIDDEN,
			'data' =>$data,
			'msg' => $msg
		])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
	}

	public function badRequest(string $msg, array $data = [])
	{
		return response()->json([
			'status' => Response::HTTP_BAD_REQUEST,
			'data' =>$data,
			'msg' => $msg
		])->setEncodingOptions(JSON_UNESCAPED_UNICODE);

	}
}
