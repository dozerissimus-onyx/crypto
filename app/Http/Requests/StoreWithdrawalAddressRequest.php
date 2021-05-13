<?php
namespace App\Http\Requests;

use App\Rules\CryptoAddressRule;
use App\Rules\CurrencyCodeRule;
use App\Rules\RiskScoreRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\RequiredIf;

class StoreWithdrawalAddressRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'currency_code' => new CurrencyCodeRule(),
            'address' => [new CryptoAddressRule($this), new RiskScoreRule($this)],
            'address_tag' => new RequiredIf(function () {
                return in_array(strtoupper($this->currency_code), ['XRP', 'XLM', 'EOS']);
            }),
        ];
    }
}
