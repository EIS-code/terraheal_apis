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
use App\Booking;
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
        'email',
        'tel_number_code',
        'tel_number',
        'emergency_tel_number_code',
        'emergency_tel_number',
        'nif',
        'address',
        'id_passport',
        'id_passport_front',
        'id_passport_back',
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
        'user_id',
        'user_gender_preference_id',
        'referral_code',
        'password',
        'is_removed',
        'is_email_verified',
        'is_mobile_verified',
        'is_document_verified',
        'source',
        'client_note',
        'gender',
        'age',
        'is_document_uploaded'
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
    public $idPassportPath   = 'user\id_passport\\';
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
    const IS_GUEST = 0;
    const IS_NOT_GUEST = 1;
    const INTERNET = 0;
    const RECOMMENDATION = 1;
    const FLYER = 2;
    const PUBLICITY = 3;
    const HOTEL = 4;
    const BY_CHANCE = 5;
    const ACCEPT = '1';
    const REJECT = '2';
        
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

    const MALE   = 'm';
    const FEMALE = 'f';
    public $gender = [
        self::MALE      => "Male",
        self::FEMALE    => "Female"
    ];
    
    const USER = 'User';
    
    private static $qrCode = ['id' => false, 'dob' => NULL, 'email' => NULL, 'shop_id' => false, 'terraheal_flag' => true];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['nullable', 'unique:users,email,' . $id];
            $numberValidator = ['unique:users,tel_number,' . $id];
            $nameValidator  = ['nullable'];
            
        } else {
            $emailValidator = ['nullable', 'unique:users'];
            $numberValidator = ['nullable', 'unique:users'];
            $nameValidator  = ['required'];
        }

        return Validator::make($data, [
            'name'                 => array_merge(['string', 'max:255'], $nameValidator),
            'surname'              => ['string', 'max:255'],
            'dob'                  => ['string'],
            'country_id'           => ['integer', 'exists:' . Country::getTableName() . ',id'],
            'city_id'              => ['integer', 'exists:' . City::getTableName() . ',id'],
            'gender'               => ['nullable', 'in:m,f'],
            'email'                => array_merge(['string', 'email', 'max:255'], $emailValidator),
            'tel_number_code'      => ['string', 'max:20'],
            'tel_number'           => array_merge(['nullable', 'string', 'max:50'], $numberValidator),
            'emergency_tel_number_code' => ['string', 'max:20'],
            'emergency_tel_number' => ['string', 'max:50'],
            'nif'                  => ['string', 'max:255'],
            'address'              => ['string', 'max:255'],
            'id_passport'          => ['string', 'max:255'],
            'id_passport_front'    => ['string', 'max:255'],
            'id_passport_back'     => ['string', 'max:255'],
            'password'             => ['min:6', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
            'shop_id'              => ['integer', 'exists:' . Shop::getTableName() . ',id'],
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

    public function validatePhoto($request)
    {
        return Validator::make($request->all(), [
            'photo' => 'mimes:jpeg,png,jpg',
        ], [
            'photo' => __('Please select proper file. The file must be a file of type: jpeg, png, jpg.')
        ]);
    }
    
    public function checkMimeTypes($request, $file, $mimes = 'jpeg,png,jpg,doc,docx,pdf')
    {
        return Validator::make($request->all(), [
            'file.*' => 'mimes:jpeg,png,jpg,doc,docx,pdf',
        ], [
            'file.*' => __('Please select proper file. The file must be a file of type: ' . $mimes . '.')
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

    public function country()
    {
        return $this->hasOne('App\Country', 'id', 'country_id');
    }

    public function city()
    {
        return $this->hasOne('App\City', 'id', 'city_id');
    }
    
    public function userDocument()
    {
        return $this->hasOne('App\UserDocument', 'user_id', 'id');
    }
    
    public function userCards()
    {
        return $this->hasMany('App\UserCardDetail', 'user_id', 'id');
    }

    public function userFavoriteServices()
    {
        return $this->hasMany('App\UserFavoriteService', 'user_id', 'id');
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

    public function getIdPassportFrontAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $idPassportPath = (str_ireplace("\\", "/", $this->idPassportPath));
        if (Storage::disk($this->fileSystem)->exists($idPassportPath . $value)) {
            return Storage::disk($this->fileSystem)->url($idPassportPath . $value);
        }

        return $value;
    }

    public function getIdPassportBackAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $idPassportPath = (str_ireplace("\\", "/", $this->idPassportPath));
        if (Storage::disk($this->fileSystem)->exists($idPassportPath . $value)) {
            return Storage::disk($this->fileSystem)->url($idPassportPath . $value);
        }

        return $value;
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
        self::$qrCode['id']      = $this->id;
        self::$qrCode['dob']     = $this->dob;
        self::$qrCode['email']   = $this->email;
        self::$qrCode['shop_id'] = $this->shop_id;

        return json_encode(self::$qrCode);
    }

    public function storeQRCode()
    {
        $qr = new QR();

        $qr->json($this->qrCodeKey());

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

    public static function checkQRCode(string $json):Bool
    {
        $data    = json_decode($json, true);
        $checked = false;

        if (json_last_error() == JSON_ERROR_NONE) {
            $bookingId = !empty($data['booking_id']) ? (int)$data['booking_id'] : false;

            if (!empty($bookingId)) {
                $booking = Booking::find($bookingId);

                if (!empty($booking)) {
                    $userId = $booking->user_id;

                    $user   = self::find($userId);

                    if (!empty($user)) {
                        $test = true;

                        foreach (self::$qrCode as $key => $value) {
                            if (!empty($data[$key])) {
                                if (($key == "terraheal_flag") || (!empty($user->{$key}) && $user->{$key} == $data[$key])) {
                                    $test = true;
                                } else {
                                    $test = false;
                                    break;
                                }
                            } else {
                                $test = false;
                                break;
                            }
                        }

                        $checked = $test;
                    }
                }
            }
        }

        return $checked;
    }

    public function reviews()
    {
        return $this->hasMany('App\Review', 'user_id', 'id');
    }

    public function getGlobalResponse(int $id)
    {
        $model  = new UserFavoriteService();
        $data   = $this->where('id', $id)->with(['country', 'city', 'userFavoriteServices', 'userDocument', 'userCards'])->get();

        if (!empty($data)) {
            foreach ($data as &$record){
                if (!empty($record->userFavoriteServices) && !$record->userFavoriteServices->isEmpty()) {
                    $record->user_favorite_services = $model::mergeResponse($record->userFavoriteServices);
                }
            }
        }

        return $data;
    }
}
