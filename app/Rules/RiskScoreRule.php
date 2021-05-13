<?php

namespace App\Rules;

use App\Service\Elliptic;
use Illuminate\Contracts\Validation\Rule;

class RiskScoreRule implements Rule
{
    protected $request;
    protected $elliptic;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->elliptic = new Elliptic();
        $this->elliptic->setParams([
//            'address' => $request->address,
//            'asset' => $request->currency_code
            'address' => '0x7fec4fca822235da8e7ba04d4d354dd3db8c1074',
            'asset' => 'ERC20',
        ]);
        $this->elliptic->walletSynchronous();
        dd($this->elliptic->getRiskScore());
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
        return $this->elliptic->getRiskScore() && $this->elliptic->getRiskScore() > Elliptic::RISK_HIGH;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'High risk score.';
    }
}
