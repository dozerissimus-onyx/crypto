<?php


namespace App\Service\CryptoAddressValidator\Validation;


use App\Service\CryptoAddressValidator\Utils\Base58;

class XTZ extends Base58
{
    public function validate($address)
    {
        $address = (string)$address;
        $valid = parent::decode($address);

        return $valid && count($valid) == 27 && is_array($valid);
    }
}
