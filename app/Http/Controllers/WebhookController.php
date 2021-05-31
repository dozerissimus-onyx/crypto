<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWithdrawalAddressRequest;
use App\Rules\RiskScoreRule;
use App\Service\Elliptic;
use App\Service\GrowSurf;
use App\Service\SumSub;
use App\Service\Wyre;
use App\User;
use Carbon\Carbon;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $growSurf = new GrowSurf();
        $campaignId = 'fr4nyx';
        $participantId = 'x0znmz';
        $participantEmail = 'dozerissimus@gmail.com';
//        dd($growSurf->getParticipantRewards($campaignId, $participantEmail));
//        dd($growSurf->getParticipantRewards($campaignId, $participantEmail));
        dd($growSurf->getCampaign($campaignId));
        dd($growSurf->addParticipant('fr4nyx', 'sergey.o@oobit.com', [
            'firstName' => 'Sally',
            'lastName' => 'Mayweathers',
            'metadata' => [
                'phoneNumber' => '+1 415-123-4567',
                'country' => 'USA',
                'zipCode' => '94303'
            ]
        ]));
    }
}
