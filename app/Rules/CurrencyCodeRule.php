<?php

namespace App\Rules;

use App\Service\CryptoAddressValidator\CryptoAddressValidator;
use Illuminate\Contracts\Validation\Rule;

class CurrencyCodeRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        return in_array(strtoupper($value), CryptoAddressValidator::$availableAssets);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Cryptocurrency not supported.';
    }
}
