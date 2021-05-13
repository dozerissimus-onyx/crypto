<?php


namespace App\Service\CryptoAddressValidator\Validation;


class EOS
{
    public function validate($address)
    {
        $address = (string)$address;

        $regex = '/^[a-z0-9]+$/';

        return (bool)preg_match_all($regex, $address) && strlen($address) == 12;
    }
}

//    function isValidEOSAddress (address, currency, networkType) {
//        var regex = /^[a-z0-9]+$/g // Must be numbers and lowercase letters only
//  if (address.search(regex) !== -1 && address.length === 12) {
//      return true
//  } else {
//      return false
//  }
//}
//}
