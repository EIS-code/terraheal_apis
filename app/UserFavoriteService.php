<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;
use App\Massage;
use App\Therapy;
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
            'service_id' => ['required', 'integer'],
            'type'       => ['in:' . implode(",", array_keys($this->types))],
            'user_id'    => ['required', 'exists:' . User::getTableName() . ',id']
        ]);
    }

    public function services()
    {
        if ($this->type == self::TYPE_THERAPY) {
            return $this->hasOne('App\Therapy', 'id', 'service_id');
        }

        return $this->hasOne('App\Massage', 'id', 'service_id');
    }

    public static function mergeResponse($collection)
    {
        $collection->map(function($record) {
            if (!empty($record->services)) {
                $record->shop_id        = $record->services->shop_id;
                $record->service_name   = $record->services->name;
                $record->service_image  = $record->services->image;
                $record->service_icon   = $record->services->icon;

                unset($record->services);
            }
        });

        return $collection;
    }

    public function checkServiceIdExists(int $id, $type = self::TYPE_MASSAGE):Bool
    {
        if ($type == self::TYPE_THERAPY) {
            $record = Therapy::find($id);
        } else {
            $record = Massage::find($id);
        }

        return (!empty($record));
    }
}
