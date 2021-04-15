<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckValidBookingCount implements Rule
{
    private $totalBookingInfos;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($totalBookingInfos = 0)
    {
        $this->totalBookingInfos = $totalBookingInfos;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !($value != $this->totalBookingInfos);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Total persons doesn\'t match with provided booking info.');
    }
}
