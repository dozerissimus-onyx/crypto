<?php


namespace App\Service\CryptoAddressValidator;


use Merkeleon\PhpCryptocurrencyAddressValidation\Validation;

abstract class CryptoAddressValidator extends Validation
{
    public static array $availableAssets = ['BTC', 'BCH', 'ETH', 'LINK', 'USDC', 'USDT', 'XRP', 'LTC', 'XLM', 'ZEC', 'EOS', 'XTZ', 'COMP', 'GRT', 'YFI'];

    public static function make($iso)
    {
        $class = 'App\Service\CryptoAddressValidator\Validation\\' . strtoupper($iso);
        if (class_exists($class))
        {
            return new $class();
        }

        return parent::make($iso);
    }

    abstract public function validate($address);
}
