<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class News extends BaseModel
{
    protected $fillable = [
        'title',
        'sub_title',
        'description',
        'is_read'
    ];

    const READ = '1';
    const NOT_READ = '0';
    public $isRead = [
        self::READ      => 'Yes',
        self::NOT_READ  => 'Nope'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'title'         => ['required', 'string', 'max:255'],
            'sub_title'     => ['string', 'max:255'],
            'description'   => ['string'],
            'is_read'       => ['in:', implode(",", array_keys($this->isRead))]
        ]);
    }

    public function __construct()
    {
        parent::__construct();

        $this->addHidden('is_read');

        $this->removeHidden('updated_at');
    }
}
