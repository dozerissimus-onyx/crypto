<?php


namespace App\Service\CryptoAddressValidator\Utils;


class Base58
{
    //Migrate all from js to PHP
    protected $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    protected $alphabetMap = [];
    protected $base;

    public function __construct()
    {
        for ($i = 0; $i < strlen($this->alphabet); ++$i) {
            $this->alphabetMap[$this->alphabet[$i]] = $i;
        }
        $this->base = strlen($this->alphabet);
    }

    public function decode($string)
    {
        if (strlen($string) === 0) return [];

        $bytes = [0];

        for ($i = 0; $i < strlen($string); ++$i) {
            $c = $string[$i];
            if (!array_key_exists($c, $this->alphabetMap)) return false;

            for ($j = 0; $j < count($bytes); ++$j) {
                $bytes[$j] *= $this->base;
            }
            $bytes[0] += $this->alphabetMap[$c];

            $carry = 0;
            for ($j = 0; $j < count($bytes); ++$j) {
                $bytes[$j] += $carry;
                $carry = $bytes[$j] >> 8;
                $bytes[$j] &= 0xff;
            }

            while ($carry) {
                array_push($bytes, $carry & 0xff);
                $carry >>= 8;
            }
        }

        for ($i = 0; $string[$i] === '1' && $i < strlen($string) - 1; ++$i) {
            array_push($bytes, 0);
        }

        return array_reverse($bytes);
    }
}


// Base58 encoding/decoding
// Originally written by Mike Hearn for BitcoinJ
// Copyright (c) 2011 Google Inc
// Ported to JavaScript by Stefan Thomas
// Merged Buffer refactorings from base58-native by Stephen Pair
// Copyright (c) 2013 BitPay Inc

//var ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz'
//var ALPHABET_MAP = {}
//for (var i = 0; i < ALPHABET.length; ++i) {
//    ALPHABET_MAP[ALPHABET.charAt(i)] = i
//}
//var BASE = ALPHABET.length
//
//module.exports = {
//    decode: function (string) {
//        if (string.length === 0) return []
//
//    var i; var j; var bytes = [0]
//    for (i = 0; i < string.length; ++i) {
//        var c = string[i]
//      if (!(c in ALPHABET_MAP)) throw new Error('Non-base58 character')
//
//      for (j = 0; j < bytes.length; ++j) bytes[j] *= BASE
//      bytes[0] += ALPHABET_MAP[c]
//
//      var carry = 0
//      for (j = 0; j < bytes.length; ++j) {
//          bytes[j] += carry
//        carry = bytes[j] >> 8
//        bytes[j] &= 0xff
//      }
//
//      while (carry) {
//          bytes.push(carry & 0xff)
//        carry >>= 8
//      }
//    }
//    // deal with leading zeros
//    for (i = 0; string[i] === '1' && i < string.length - 1; ++i) {
//        bytes.push(0)
//    }
//
//    return bytes.reverse()
//  }
//}

