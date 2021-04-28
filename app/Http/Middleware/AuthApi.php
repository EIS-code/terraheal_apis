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

        $isMobile = (in_array($getKeyInfo->type, [ApiKey::TYPE_USERS, ApiKey::TYPE_THERAPISTS, ApiKey::TYPE_FREELANCER_THERAPISTS]));

        if ($isMobile && $this->totalLogins($getKeyInfo)) {
            return response()->json([
                'code' => 401,
                'msg'  => __('You can\'t login on multiple device.')
            ]);
        }

        // Superadmin ID
        $request->merge(['superadmin_id' => $getKeyInfo->superadmin_id]);
        // Shop ID
        $request->merge(['shop_id' => $getKeyInfo->shop_id]);
        // Model ID
        $request->merge(['id' => $getKeyInfo->model_id]);

        return $next($request);
    }

    private function validate(string $key)
    {
        $apiKey     = ApiKey::select(ApiKey::getTableName() . '.*', ApiKeyShop::getTableName() . '.shop_id', ApiKeyShop::getTableName() . '.superadmin_id')
                        ->where(ApiKey::getTableName() . '.key', $key)
                        ->join(ApiKeyShop::getTableName(), ApiKey::getTableName() . '.api_key_id', '=', ApiKeyShop::getTableName() . '.id');

        $apiKeyShop = ApiKeyShop::select(ApiKey::getTableName() . '.*', ApiKeyShop::getTableName() . '.shop_id', ApiKeyShop::getTableName() . '.superadmin_id')
                        ->where(ApiKeyShop::getTableName() . '.key', $key)
                        ->join(ApiKey::getTableName(), ApiKey::getTableName() . '.api_key_id', '=', ApiKeyShop::getTableName() . '.id');

        $getKeyInfo = $apiKeyShop->union($apiKey)->first();

        // For Superadmin if nothing found.
        if (empty($getKeyInfo)) {
            $getKeyInfo = $this->isSuperadmin($key);
        }

        return $getKeyInfo;
    }

    private function isSuperadmin(string $key)
    {
        $apiKeySuperadmin = ApiKeyShop::select(ApiKey::getTableName() . '.*', ApiKeyShop::getTableName() . '.shop_id', ApiKeyShop::getTableName() . '.superadmin_id')
                                      ->whereNull('shop_id')
                                      ->whereNotNull('superadmin_id')
                                      ->where(ApiKeyShop::getTableName() . '.key', $key)
                                      ->leftJoin(ApiKey::getTableName(), ApiKey::getTableName() . '.api_key_id', '=', ApiKeyShop::getTableName() . '.id')
                                      ->first();

        return $apiKeySuperadmin;
    }

    private function totalLogins(ApiKey $apiKey)
    {
        $getUsers = ApiKey::where('model_id', $apiKey->model_id)->where('type', $apiKey->type)->count();

        return ($getUsers > env('ALLOWED_API_USERS_LOGIN'));
    }
}
