<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Shop;

class Currency extends BaseModel
{
    public static $defaultCurrency   = 'EUR';
    public static $defaultCurrencyId = 1;

    protected $fillable = [
        'code',
        'exchange_rate',
        'country_id'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'code'          => ['required', 'string', 'max:255'],
            'exchange_rate' => ['required', 'float'],
            'country_id'    => ['required', 'integer']
        ]);
    }

    public function getDefultCurrency()
    {
        return $self::$defaultCurrency;
    }

    public function getDefaultShopCurrency(int $shopId, bool $isId = false)
    {
        $modelShop  = new Shop();
        $shop       = $modelShop->find($shopId);

        if (!empty($shop)) {
            $getCurrencyId = $shop->currency_id;

            if (!empty($getCurrencyId)) {
                return ($isId) ? $getCurrencyId : $this->getCodeFromId($getCurrencyId);
            }
        }

        return $thise->getDefultCurrency();
    }

    public function getCodeFromId(int $id)
    {
        $currency = $this->getCurrencyById($id);

        return (!empty($currency)) ? $currency->code : $self::$defaultCurrency;
    }

    public function getRate(int $currencyId)
    {
        $rate = $this->getCurrencyById($currencyId);

        if (!empty($rate)) {
            return $rate->exchange_rate;
        }

        return 0;
    }

    public function getCurrencyById(int $currencyId)
    {
        return self::find($currencyId);
    }
}
