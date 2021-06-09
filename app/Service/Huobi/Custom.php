<?php


namespace App\Service\Huobi;


use Lin\Huobi\Request;

class Custom extends Request
{
    public function getExchangeRates(array $data = []) {
        $this->type='GET';
        $this->path='/v1/stable_coin/exchange_rate';
        $this->data=$data;

        return $this->exec();
    }
}
