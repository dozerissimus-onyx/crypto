<?php

namespace App\Rules;

use App\Service\CryptoAddressValidator\CryptoAddressValidator;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class CryptoAddressRule implements Rule
{
    protected $request;
    protected $validator;

    /**
     * Create a new rule instance.
     *
     * @param Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->validator = CryptoAddressValidator::make($request->currency_code);
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
        return $this->validator->validate($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Address is incorrect.';
    }
}
