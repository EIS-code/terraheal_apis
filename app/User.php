<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Country;
use App\City;
use App\Shop;
// use App\BaseModel;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Libraries\QR;

class User extends BaseModel implements Authenticatable
{
    use Notifiable, AuthenticableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'surname',
        'dob',
        // 'country',
        'email',
        'tel_number_code',
        'tel_number',
        'emergency_tel_number_code',
        'emergency_tel_number',
        'nif',
        'address',
        'id_passport',
        'avatar',
        'avatar_original',
        'device_token',
        'device_type',
        'app_version',
        // 'photo',
        'oauth_uid',
        'oauth_provider',
        'profile_photo',
        'qr_code_path',
        'country_id',
        'city_id',
        'shop_id',
        'referral_code',
        'password',
        'is_removed',
        'is_email_verified',
        'is_mobile_verified',
        'is_document_verified',
        'source',
        'client_note',
        'gender'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'is_removed', 'updated_at'
    ];

    public $fileSystem       = 'public';
    public $profilePhotoPath = 'user\profile\\';
    public $qrCodePath       = 'user\qr_codes\\';

    public static $notRemoved = '0';
    public static $removed = '1';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    /*protected $casts = [
        'email_verified_at' => 'datetime',
    ];*/

    const OAUTH_PROVIDER_GOOGLE   = 1;
    const OAUTH_PROVIDER_FACEBOOK = 2;
    const OAUTH_PROVIDER_TWITTER  = 3;
    const OAUTH_PROVIDER_LINKEDIN = 4;
    const IS_GUEST = 1;
    const INTERNET = 0;
    const RECOMMENDATION = 1;
    const FLYER = 2;
    const PUBLICITY = 3;
    const HOTEL = 4;
    const BY_CHANCE = 5;
    
    public static $oauthProviders = [
        self::OAUTH_PROVIDER_GOOGLE   => 'Google',
        self::OAUTH_PROVIDER_FACEBOOK => 'Facebook',
        self::OAUTH_PROVIDER_TWITTER  => 'Twitter',
        self::OAUTH_PROVIDER_LINKEDIN => 'LinkedIn'
    ];
    
    public static $source = [
        self::INTERNET => 'Internet',
        self::RECOMMENDATION => 'Recommendation',
        self::FLYER => 'Flyer',
        self::PUBLICITY => 'Publicity',
        self::HOTEL => 'Hotel',
        self::BY_CHANCE => 'By chance'
    ];

    private static $qrCode = ['id' => false, 'dob' => NULL, 'email' => NULL, 'shop_id' => false, 'terraheal_flag' => true];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:users,email,' . $id];
        } else {
            $emailValidator = ['unique:users'];
        }

        return Validator::make($data, [
            'name'                 => ['required','string', 'max:255'],
            'surname'              => ['string', 'max:255'],
            'dob'                  => ['string'],
            'country_id'           => ['integer', 'exists:' . Country::getTableName() . ',id'],
            'city_id'              => ['integer', 'exists:' . City::getTableName() . ',id'],
            'gender'               => ['in:m,f'],
            'email'                => array_merge(['string', 'email', 'max:255'], $emailValidator),
            'tel_number_code'      => ['string', 'max:20'],
            'tel_number'           => ['required','string', 'max:50'],
            'emergency_tel_number_code' => ['string', 'max:20'],
            'emergency_tel_number' => ['string', 'max:50'],
            'nif'                  => ['string', 'max:255'],
            'address'              => ['string', 'max:255'],
            'id_passport'          => ['string', 'max:255'],
            'password'             => ['min:6', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
            'shop_id'              => ['integer', 'exists:' . Shop::getTableName() . ',id'],
            // 'shop_id'           => ['required', 'integer'],
            'avatar'               => ['max:255'],
            'avatar_original'      => ['max:255'],
            'device_token'         => ['max:255'],
            'device_type'          => ['max:255'],
            'app_version'          => ['max:255'],
            'profile_photo'        => ['max:10240'],
            'qr_code_path'         => ['max:10240'],
            'oauth_uid'            => ['max:255'],
            'oauth_provider'       => [(!empty($data['oauth_uid']) ? 'required' : ''), (!empty($data['oauth_uid']) ? 'in:1,2,3,4' : '')],
            'is_email_verified'    => ['in:0,1'],
            'is_mobile_verified'   => ['in:0,1'],
            'is_document_verified' => ['in:0,1'],
            'referral_code'        => ['max:255'],
            'is_removed'           => ['integer', 'in:0,1'],
        ], [
            'password.regex'  => 'Password should contains at least one [a-z, A-Z, 0-9, @, $, !, %, *, #, ?, &].'
        ]);
    }

    public function shop()
    {
        return $this->hasOne('App\Shop', 'id', 'shop_id');
    }

    public function country()
    {
        return $this->hasOne('App\Country', 'id', 'country_id');
    }

    public function city()
    {
        return $this->hasOne('App\City', 'id', 'city_id');
    }

    public function validateProfilePhoto($request)
    {
        return Validator::make($request->all(), [
            'profile_photo' => 'mimes:jpeg,png,jpg',
        ], [
            'profile_photo' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }

    public function getProfilePhotoAttribute($value)
    {
        $default = asset('images/clients.png');

        if (empty($value)) {
            return $default;
        }

        $profilePhotoPath = (str_ireplace("\\", "/", $this->profilePhotoPath));
        if (Storage::disk($this->fileSystem)->exists($profilePhotoPath . $value)) {
            return Storage::disk($this->fileSystem)->url($profilePhotoPath . $value);
        }

        return $default;
    }

    public function getQrCodePathAttribute($value)
    {
        $default = NULL;

        if (empty($value)) {
            return $default;
        }

        $qrCodePath = (str_ireplace("\\", "/", $this->qrCodePath));
        if (Storage::disk($this->fileSystem)->exists($qrCodePath . $value)) {
            return Storage::disk($this->fileSystem)->url($qrCodePath . $value);
        }

        return $default;
    }

//    public function getDobAttribute($value)
//    {
//        if (empty($value)) {
//            return $value;
//        }
//
//        return strtotime($value) * 1000;
//        // return Carbon::createFromTimestampMs($value)->format('Y-m-d H:i:s');
//    }

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->surname;
    }
    
    public function getSourceAttribute($value)
    {
        return (isset(self::$source[$value])) ? self::$source[$value] : $value;
    }

    public function qrCodeKey()
    {
        $this->qrCode['id']      = $this->id;
        $this->qrCode['dob']     = $this->dob;
        $this->qrCode['email']   = $this->email;
        $this->qrCode['shop_id'] = $this->shop_id;

        return json_encode($this->qrCode);
    }

    public function storeQRCode()
    {
        $qr = new QR();

        $qr->contact($this->qrCodeKey());

        $qrUrl = $qr->getUrl();

        if (!empty($qrUrl)) {
            $contents = file_get_contents($qrUrl);

            $isStore  = Storage::disk($this->fileSystem)->put($this->qrCodePath . $this->id . '.png', $contents);

            if ($isStore) {
                $this->qr_code_path = $this->id . '.png';

                if ($this->update()) {
                    return $this->qr_code_path;
                }
            }
        }

        return false;
    }

    public static function isOurQRCode(string $json):Bool
    {
        $data = json_decode($json, true);

        if (json_last_error() == JSON_ERROR_NONE) {
            $keys   = array_keys(self::$qrCode);
            $qrData = self::$qrCode;
            $flag   = false;

            foreach ($data as $key => $value) {
                if (in_array($key, $keys)) {
                    $flag = true;

                    unset($qrData[$key]);
                } else {
                    $flag = false;

                    break;
                }
            }

            return ($flag && count($qrData) == 0);
        }

        return false;
    }

    public function reviews()
    {
        return $this->hasMany('App\Review', 'user_id', 'id');
    }

    public function getGlobalResponse(int $id)
    {
        $data = $this->where('id', $id)->with(['country', 'city'])->first();

        return $data;
    }
}
