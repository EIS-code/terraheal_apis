<?php

namespace App\Http\Middleware;

use App\Repositories\ApiKeyRepository;
use Closure;
use App\ApiKey;
use App\ApiKeyShop;
use DB;

class AuthApi
{
    private $excludedRoutes = [
        
    ];

    private $isSuperadmin = false;

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

        // Exclude client and Shop APIs temporary as Prasangsir said we will add it later after whole app complete. 20212904
        if (
                $request->is('user*') || 
                $request->is('location*') || 
                $request->is('massage*') || 
                $request->is('therapy*') || 
                $request->is('shops*') || 
                $request->is('dashboard*') || 
                $request->is('events*') || 
                $request->is('clients*') || 
                $request->is('rooms*') || 
                $request->is('receptionist*') || 
                $request->is('therapist/signin') || 
                $request->is('therapist/freelancer/signin') || 
                $request->is('waiting*') || 
                $request->is('service*') || 
                $request->is('manager*')
        ) {
            return $next($request);
        }

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

        if ($this->isSuperadmin) {
            // Superadmin ID
            $request->merge(['superadmin_id' => $getKeyInfo->superadmin_id]);
        } else {
            // Shop ID
            $request->merge(['shop_id' => $getKeyInfo->shop_id]);
            // Model ID
            $request->merge(['id' => $getKeyInfo->model_id]);
        }

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

        $getKeyInfo = $apiKey->union($apiKeyShop)->first();

        // For Superadmin if nothing found.
        if (empty($getKeyInfo)) {
            $getKeyInfo = $this->isSuperadmin($key);
        }

        return $getKeyInfo;
    }

    private function isSuperadmin(string $key)
    {
        /*$apiKeySuperadmin = ApiKeyShop::select(ApiKey::getTableName() . '.*', ApiKeyShop::getTableName() . '.shop_id', ApiKeyShop::getTableName() . '.superadmin_id')
                                      ->whereNull('shop_id')
                                      ->whereNotNull('superadmin_id')
                                      ->where(ApiKeyShop::getTableName() . '.key', $key)
                                      ->leftJoin(ApiKey::getTableName(), ApiKey::getTableName() . '.api_key_id', '=', ApiKeyShop::getTableName() . '.id')
                                      ->first();*/

        $apiKeyModel      = new ApiKey();
        $apiKeyShopModel  = new ApiKeyShop();
        $apiKeySuperadmin = DB::select("select `" . $apiKeyModel::getTableName() . "`.*, `" . $apiKeyShopModel::getTableName() . "`.`shop_id`, `" . $apiKeyShopModel::getTableName() . "`.`superadmin_id` from `" . $apiKeyShopModel::getTableName() . "` left join `" . $apiKeyModel::getTableName() . "` on `" . $apiKeyModel::getTableName() . "`.`api_key_id` = `" . $apiKeyShopModel::getTableName() . "`.`id` where `shop_id` is null and `superadmin_id` is not null and `" . $apiKeyShopModel::getTableName() . "`.`key` = '" . $key . "'");

        if (!empty($apiKeySuperadmin[0])) {
            $this->isSuperadmin = true;

            return $apiKeySuperadmin[0];
        }

        return [];
    }

    private function totalLogins(ApiKey $apiKey)
    {
        $getUsers = ApiKey::where('model_id', $apiKey->model_id)->where('type', $apiKey->type)->count();

        return ($getUsers > env('ALLOWED_API_USERS_LOGIN'));
    }
}
