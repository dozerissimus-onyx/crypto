<?php


namespace App\Service\Huobi;


class HuobiSpot extends \Lin\Huobi\HuobiSpot
{
    private function init(){
        return [
            'key'=>$this->key,
            'secret'=>$this->secret,
            'host'=>$this->host,
            'options'=>$this->options,
        ];
    }

    public function custom() {
        return new Custom($this->init());
    }
}
