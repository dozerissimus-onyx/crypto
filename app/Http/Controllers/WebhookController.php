<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWithdrawalAddressRequest;
use App\Rules\RiskScoreRule;
use App\Service\Elliptic;
use App\Service\SumSub;
use App\Service\Wyre;
use App\User;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    public function index()
    {
        //example data from app.elliptic.co
        //data from $transaction
        $exampleHash = 'accf5c09cc027339a3beb2e28104ce9f406ecbbd29775b4a1a17ba213f1e035e';
        $exampleAddress = '15Hm2UEPaEuiAmgyNgd5mF3wugqLsYs3Wn';
        $exampleCustomer = 'testCustomer';

        $currency = Currency::whereCode($code)->first();
        if (!$currency) {
            throw new \ErrorException('Currency code not valid');
        }

        $params = [
            'hash' => $exampleHash,
            'address' => $exampleAddress,
            'customer' => $exampleCustomer,
            'asset' => $currency->code
        ];

        $elliptic = new Elliptic();
        $elliptic->setParams($params);
        $elliptic->transactionSynchronous();

        //I think inside this static method deposit is saved
        Deposit::create([
            'currency_code' => $currency->code,
            'amount' => $amount,
            'hash' => $exampleHash,
            'risk_score' => $elliptic->getRiskScore()
        ]);

        return redirect();
    }

    public function store(StoreWithdrawalAddressRequest $request) {
        $request->validated();

        $address = new Address($request->all());
        $address->save();

        return;
    }

    public function moonpay_customer_data_sync() {
        $applicantId = '';
        $inspectionId = '';

        $sumSub = new SumSub();

        $applicantDocs = $sumSub->getApplicantStatus($applicantId);

        $response = [];

        foreach ($applicantDocs as $data) {
            foreach ($data['imageIds'] as $imageId) {
                if ($data['imageReviewResults'][$imageId]['reviewAnswer'] === SumSub::ANSWER_GREEN && array_key_exists($data['idDocType'], SumSub::$docTypes)) {
                    $response['files'][] = [
                        'type' => SumSub::$docTypes[$data['idDocType']],
                        'side' => null,
                        'country' => $data['country'],
                        'downloadLink' => $sumSub->downloadImage($inspectionId, $imageId)
                    ];
                }
            }
        }

        return $response;
    }

    public function test() {
        //AC_7HMN9VXN7RU
//        $accountID = 'AC_3YZ2B8479AT';

//        dd(date('d-m-Y H:i:s', 1621511618));

        try {
//            $data = (new CoinGeckoClient())->coins()->getMarkets('usd');
            $client = new CoinGeckoClient();
            for ($i = 0; $i < 200; $i++)
            $data = $client->ping();

            if (! is_array($data)) {
                Log::critical('Update Currencies Failed', [
                    'data' => $data,
                ]);

                return;
            }

dd($client->getLastResponse()->getStatusCode());
//            foreach ($data as $item) {
//                $currency = Currency::whereType(CurrencyType::crypto)
//                    ->whereCoingeckoId($item['id'])
//                    ->first();
//
//                if (! $currency) {
//                    continue;
//                }
//
//                $currency->update([
//                    'price' => $item['current_price'] ?? 0,
//                    'ranking' => $item['market_cap_rank'] ?? 0,
//                    '24h_change' => $item['price_change_percentage_24h'] ?? 0,
//                    '24h_volume' => $item['total_volume'] ?? 0,
//                    'circulating_supply' => $item['circulating_supply'] ?? 0,
//                    'market_cap' => $item['market_cap'] ?? 0,
//                ]);
//            }
        } catch (\Throwable $e) {
            dd($e->getCode());
            Log::critical('Update Currencies Failed', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
