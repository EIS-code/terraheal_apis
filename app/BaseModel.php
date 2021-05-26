<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{

    public static $notRemoved = '0';

    public static $removed = '1';

    public static $removedColumn = 'is_removed';

    protected $hidden = ['is_removed', 'created_at', 'updated_at', 'deleted_at'];

    public static $storage = NULL;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        static::getStorage();
    }

    public function addHidden($fields)
    {
        if (is_array($fields)) {
            array_merge($fields, $this->hidden);
        } elseif (is_string($fields)) {
            array_push($this->hidden, $fields);
        }
    }

    public function removeHidden($fields)
    {
        if (is_array($fields)) {
            foreach ($fields as $field) {
                if (in_array($field, $this->hidden)) {
                    $key = array_search($field, $this->hidden);

                    unset($this->hidden[$key]);
                }
            }
        } elseif (is_string($fields)) {
            if (in_array($fields, $this->hidden)) {
                $key = array_search($fields, $this->hidden);

                unset($this->hidden[$key]);
            }
        }
    }

    public function appendNewFields($fields)
    {
        if (is_array($fields)) {
            array_merge($fields, $this->appends);
        } elseif (is_string($fields)) {
            array_push($this->appends, $fields);
        }
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function newQuery() {
        try {
            $tableFillables = $this->fillable;
            $tableColumns   = \Schema::getColumnListing(parent::getTable());
            $where          = [];

            // Default shop_id.
            $shopId = (int)request()->get('shop_id', false);

            if (!empty($shopId) && in_array('shop_id', $tableFillables)) {
                $where[$this::getTableName() . '.shop_id'] = $shopId;
            }

            if (in_array(self::$removedColumn, $tableFillables) && in_array(self::$removedColumn, $tableColumns)) {
                return parent::newQuery()->where('is_removed', '=', self::$notRemoved)->where($where);
            } else {
                return parent::newQuery()->where($where);
            }
        } catch(Exception $exception) {}

        return parent::newQuery();
    }

    public static function getStorage()
    {
        return self::$storage = rtrim(env('APP_URL'), '/') . '/' . 'storage/';
    }

    public function getUpdatedAtAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }

    public function setMysqlStrictFalse(): Void
    {
        config()->set('database.connections.mysql.strict', false);
        \DB::reconnect();
    }

    public function setMysqlStrictTrue(): Void
    {
        config()->set('database.connections.mysql.strict', true);
        \DB::reconnect();
    }
}
