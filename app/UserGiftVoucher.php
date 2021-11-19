<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;
use App\UserGiftVoucherThemeDesign;

class UserGiftVoucher extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'recipient_name',
        'recipient_last_name',
        'recipient_second_name',
        'recipient_mobile',
        'recipient_email',
        'giver_first_name',
        'giver_last_name',
        'giver_mobile',
        'giver_email',
        'giver_message_to_recipient',
        'preference_email',
        'preference_email_date',
        'amount',
        'user_id',
        'design_id',
        'unique_id',
        'is_pack',
        'is_removed',
        'payment_id',
        'shop_id'
    ];
    
    protected $hidden = ['updated_at'];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'recipient_name'             => ['required', 'string', 'max:255'],
            'recipient_last_name'        => ['required', 'string', 'max:255'],
            'recipient_second_name'      => ['nullable', 'string', 'max:255'],
            'recipient_mobile'           => ['required', 'string', 'max:255'],
            'recipient_email'            => ['required', 'email', 'max:255'],
            'giver_first_name'           => ['required', 'string', 'max:255'],
            'giver_last_name'            => ['required', 'string', 'max:255'],
            'giver_mobile'               => ['required', 'string'],
            'giver_email'                => ['required', 'email', 'max:255'],
            'giver_message_to_recipient' => ['required', 'string'],
            'preference_email'           => ['required', 'email', 'max:255'],
            'preference_email_date'      => ['required', 'date', 'date_format:Y-m-d'],
            'amount'                     => ['required', 'between:0,99.99'],
            'user_id'                    => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'shop_id'                    => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
            'design_id'                  => ['required', 'integer', 'exists:' . UserGiftVoucherThemeDesign::getTableName() . ',id'],
            'unique_id'                  => ['required', 'unique:' . self::getTableName() . ',unique_id'],
            'is_pack'                    => ['integer', 'in:0,1'],
            'is_removed'                 => ['integer', 'in:0,1']
        ]);
    }
    
    public function getCreatedAtAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function shop()
    {
        return $this->hasOne('App\Shop', 'id', 'shop_id');
    }
}
