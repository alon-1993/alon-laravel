<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Services\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
	    $this->app->bind('Illuminate\Pagination\LengthAwarePaginator', function ($app, $options) {
		    return new Paginator(
			    $options['items'], $options['total'], $options['perPage'], $options['currentPage'], $options['options']
		    );
	    });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
	    // HTTPS 访问
	    if(config('app.is_https')){
		    URL::forceScheme('https');
	    }

	    //低于 5.7.7 的 MySQL 或者版本低于 10.2.2 的 MariaDB,索引只支持最多191位,需特殊指定
	    Schema::defaultStringLength(191);

	    //慢查询日志
	    DB::listen(function ($query) {
		    $sql = $query->sql;
		    $bingings = $query->bindings;
		    $time = $query->time;
		    if (config('app.debug') == 'true' && $time <= 1000) {
			    $sql = binging_into_sql($bingings,$sql);
			    Log::channel('sql')->info('query', compact('time', 'sql'));
		    }
		    //超过1秒记录日志
		    if ($time > 1000) {
			    $sql = binging_into_sql($bingings,$sql);
			    Log::channel('sql')->info('slowly query', compact('time', 'sql'));
		    }
	    });
	    /* toRawSql end*/
	    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
	    //自定义token是否过期的方法
	    Sanctum::authenticateAccessTokensUsing(function ($accessToken, $isValid){
		    $expiration = config('sanctum.expiration');
		    $time = $accessToken->last_used_at??$accessToken->created_at;
		    return $time->gt(now()->subMinutes($expiration));
	    });
    }
}
