<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserFavoriteService extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'type',
        'user_id'
    ];

    const TYPE_MASSAGE = '0';
    const TYPE_THERAPY = '1';

    public $types = [
        self::TYPE_MASSAGE => 'Massage',
        self::TYPE_THERAPY => 'Therapy'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'service_id' => ['required', 'integer', 'exists:' . Service::getTableName() . ',id'],
            'type'       => ['in:' . implode(",", array_keys($this->types))],
            'user_id'    => ['required', 'exists:' . User::getTableName() . ',id']
        ]);
    }

    public function services()
    {
        return $this->hasOne('App\Service', 'id', 'service_id');
    }
    
    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public static function mergeResponse($collection)
    {
        $collection->map(function($record) {
            if (!empty($record->services)) {
                $icon = ServiceImage::where(['service_id' => $record->services->id, 'is_featured' => ServiceImage::IS_FEATURED])->first();
                $record->shop_id        = $record->user->shop_id;
                $record->service_english_name   = $record->services->english_name;
                $record->service_portugese_name  = $record->services->portugese_name;
                $record->service_icon   = $icon->image;

                unset($record->services);
                unset($record->user);
            }
        });

        return $collection;
    }

    public function checkServiceIdExists(int $id)
    {
        $record = Service::find($id);
        return (!empty($record));
    }
}
