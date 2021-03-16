<?php

namespace App\Http\Middleware;

use App\Repositories\ApiKeyRepository;
use Closure;
use App\ApiKey;
use App\ApiKeyShop;

class AuthApi
{
    private $excludedRoutes = [
        
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);

        $apiKey = (!empty($request->header('api-key'))) ? $request->header('api-key') : false;

        if (in_array($request->path(), $this->excludedRoutes)) {
            return $next($request);
        }

        $getKeyInfo = $this->validate($apiKey);

        if (!$apiKey || empty($getKeyInfo)) {
            return response()->json([
                'code' => 401,
                'msg'  => __('API key is missing or wrong.')
            ]);
        }

        if ($this->totalLogins($getKeyInfo)) {
            return response()->json([
                'code' => 401,
                'msg'  => __('You can\'t login on multiple device.')
            ]);
        }

        $request->merge(['shop_id' => $getKeyInfo->shop_id]);
        $request->merge(['id' => $getKeyInfo->model_id]);

        return $next($request);
    }

    private function validate(string $key)
    {
        $getKeyInfo = ApiKey::select(ApiKey::getTableName() . '.*', ApiKeyShop::getTableName() . '.shop_id')
                            ->where(ApiKey::getTableName() . '.key', $key)
                            ->join(ApiKeyShop::getTableName(), ApiKey::getTableName() . '.api_key_id', '=', ApiKeyShop::getTableName() . '.id')
                            ->first();

        return $getKeyInfo;
    }

    private function totalLogins(ApiKey $apiKey)
    {
        $getUsers = ApiKey::where('model_id', $apiKey->model_id)->where('type', $apiKey->type)->count();

        return ($getUsers > env('ALLOWED_API_USERS_LOGIN'));
    }
}
