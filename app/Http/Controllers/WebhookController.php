<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWithdrawalAddressRequest;
use App\Models\Currency;
use App\Models\EnigmaOrder;
use App\Models\EnigmaProduct;
use App\Models\HuobiOrder;
use App\Models\HuobiSymbol;
use App\Rules\RiskScoreRule;
use App\Service\Elliptic;
use App\Service\EnigmaSecurities;
use App\Service\GrowSurf;
use App\Service\SumSub;
use App\Service\Wyre;
use App\User;
use BlockChair\BlockChair;
use Carbon\Carbon;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Lin\Huobi\Api\Spot\Order;
use Lin\Huobi\HuobiSpot;
use MockingMagician\CoinbaseProSdk\CoinbaseFacade;
use MockingMagician\CoinbaseProSdk\Contracts\Api\ApiInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
//        $key = env('HUOBI_KEY');
//        $secret = env('HUOBI_SECRET');
//
//        $accountId = env('HUOBI_ACCOUNT_ID');
//
//        $huobiSpot = new HuobiSpot($key, $secret);
////        dd($huobiSpot->common()->getCurrencys());
//        dd($huobiSpot->custom()->getExchangeRate([
//            'currency' => 'btc',
//            'amount' => 1000,
//            'type' => 'buy'
//        ]));
//        dd($huobiSpot->wallet()->getWithdrawQuota(['currency' => 'eth']));
//        dd($huobiSpot->market()->getDetailMerged(['symbol' => 'ethbtc']));
//        dd($huobiSpot->market()->getTickers());
//dd($huobiSpot->custom()->postExchange());
//        dump('DepositAddress');
//        dump($huobiSpot->wallet()->getDepositAddress(['currency' => 'btc']));
//
//        dump('ExchangeRates');
//        dump($huobiSpot->custom()->getExchangeRates(['currency' => 'btc']));
//
//
//
//
//
//        dd($huobiSpot->market()->getTrade(['symbol'=>'btcusdt'])); //price
//        dd($huobiSpot->order()->postPlace([
//            'account-id'=>$accountId,
//            'symbol'=>'btcusdt',
//            'type'=>'buy-limit',
//            'amount'=>'5',
//            'price'=>'100',
//        ]));
//        dd($huobiSpot->market()->getDepth(['symbol' => 'btcusdt']));
//        dd($huobiSpot->market()->getTickers()['data']);
//        dd($huobiSpot->market()->getHistoryKline([
//            'symbol' => 'btcusdt',
//            'period' => '1min'
//        ]));
//        dd($huobiSpot->common()->getSymbols());
//        dd($huobiSpot->market()->getTrade(['symbol' => 'compbtc']));

//        $huobi = new HuobiSwap($key, $secret);
////        dump($huobi->market()->getIndex());
//
//        $lastTrades = $huobi->market()->getTrade();
//        foreach ($lastTrades['tick']['data'] as $lastTrade) {
//            dump('contract: ' . $lastTrade['contract_code'] . '; ' . 'price: ' . $lastTrade['price']); //quote
//        }
//        dd($huobi->trade()->postOrder([
//            'contract_code' => 'btc-usd',
//            'volume' => 1,
//            'direction' => 'sell', // buy | sell
//            'price' => 35000,
//            'offset' => 'open', // open | close
//
//        ]));


//        Cache::put('test', $time, 0);

//        $growSurf = new GrowSurf();
//        $campaignId = 'fr4nyx';
//        $participantId = 'x0znmz';
//        $participantEmail = 'dozerissimus@gmail.com';
////        dd($growSurf->getParticipantRewards($campaignId, $participantEmail));
////        dd($growSurf->getParticipantRewards($campaignId, $participantEmail));
//        dd($growSurf->getCampaign($campaignId));
//        dd($growSurf->addParticipant('fr4nyx', 'sergey.o@oobit.com', [
//            'firstName' => 'Sally',
//            'lastName' => 'Mayweathers',
//            'metadata' => [
//                'phoneNumber' => '+1 415-123-4567',
//                'country' => 'USA',
//                'zipCode' => '94303'
//            ]
//        ]));
    }

    public function huobi()
    {
        $key = env('HUOBI_KEY');
        $secret = env('HUOBI_SECRET');
        $accountId = env('HUOBI_ACCOUNT_ID');

        $huobiSpot = new HuobiSpot($key, $secret);

        $amount = 10.05; //example
        $sellCurrency = 'eth';
        $buyCurrency = 'btc';
//        $sellCurrency = 'btc';
//        $buyCurrency = 'eth';

        $sellCurrencyId = Currency::whereCode(strtolower($sellCurrency))->id;
        $buyCurrencyId = Currency::whereCode(strtolower($buyCurrency))->id;

        $symbol = HuobiSymbol::where(['base_currency' => $sellCurrency, 'quote_currency' => $buyCurrency])
            ->orWhere(function ($query) use ($sellCurrency, $buyCurrency) {
                $query->where(['base_currency' => $buyCurrency, 'quote_currency' => $sellCurrency]);
            })->first();

        $depositAddresses = $huobiSpot->wallet()->getDepositAddress(['currency' => $sellCurrency])['data'];

        $marketData = $huobiSpot->market()->getDetailMerged(['symbol' => $symbol->symbol]);

        $quote = $marketData['tick']['close'];

        //Deposit create with $depositAddresses[0]['address'], $depositAddresses[0]['addressTag'], $sellCurrency, $amount

        $direction = strtolower($sellCurrency) === $symbol->base_currency ? 'sell' : 'buy';
        $type = $direction . '-market';

        $orderId = $huobiSpot->order()->postPlace([
            'account-id' => $accountId,
            'symbol' => $symbol->symbol,
            'type' => $type,
            'amount' => $amount,
        ]);

        $order = new HuobiOrder();

        $order->fill([
            'user_id' => Auth::id(),
            'order_id' => $orderId,
            'sell_currency_id' => $sellCurrencyId,
            'buy_currency_id' => $buyCurrencyId,
            'symbol' => $symbol->symbol,
            'quote' => $quote,
            'amount' => $amount,
            'type' => $type,
            'status' => HuobiOrder::STATUS_CREATED
        ]);
        $order->save();

        dd($orderId);
    }

    public function enigma() {
        $enigma = new EnigmaSecurities();
dd($enigma->getTrade());
        $baseCurrency = 'btc';
        $baseQty = 0; //Example (maybe get from form)
        $quoteCurrency = 'usd';
        $quoteQty = 100; //Example (maybe get from form)
        $direction = 'buy';

        $productName = strtoupper($baseCurrency) . '-' . strtoupper($quoteCurrency);

        if (!$product = EnigmaProduct::whereProductName($productName)->first()) {
            throw new NotFoundHttpException('Product not found');
        }

        $quoteArgs = [
            'side' => $direction,
            'product_id' => $product->product_id,
        ];
        if ($baseQty) {
            $quoteArgs['quantity'] = $baseQty;
        }
        if ($quoteQty) {
            $quoteArgs['nominal'] = $quoteQty;
        }

        $quote = $enigma->postQuote($quoteArgs);

        $quoteId = $quote['quote_id'];
        $price = $quote['price'];

        $orderType = 'RFQ';
        $trade = $enigma->postTrade([
            'type' => $orderType,
            'side' => $quote['side'],
            'product_id' => $quote['product_id'],
            'quantity' => $quote['quantity'],
            'quote_id' => $quoteId
        ]);

        if (!empty($trade)) {
            $order = new EnigmaOrder();
            $order->fill([
                'order_id' => $trade['order_id'],
                'type' => $orderType,
                'product_id' => $trade['product_id'],
                'product_name' => $trade['product_name'],
                'user_id' => Auth::id(),
                'message' => $trade['message'],
                'side' => $trade['side'],
                'quantity' => $trade['quantity'],
                'price' => $trade['price'],
                'nominal' => $trade['nominal'],
                'status' => EnigmaOrder::STATUS_CREATED
            ]);
            $order->save();
        }

        dd($trade);

    }

    public function fees() {
        $baseUrl = 'https://api.exchange.coinbase.com';

        $coinBase = CoinbaseFacade::createDefaultCoinbaseApi(
            $baseUrl,
            config('api.coinBasePro.key'),
            config('api.coinBasePro.secret'),
            config('api.coinBasePro.passphrase')
        );

        dd($coinBase->withdrawals()->getFeeEstimate('BTC', '3LoJFcGiBgCzy235poxmq8uZGFGSK3ZbJN'));

        $currency = 'BTC';

        $account = Account::where(['id' => Auth::id(), 'currency_code' => $currency])->get();

        dd($account->coinbase_fee);
    }
}
