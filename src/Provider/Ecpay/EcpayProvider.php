<?php

namespace Ryan\Desktop\Provider\Ecpay;

use Exception;
use Illuminate\Support\Arr;
use Ryan\Desktop\Provider\AbstractProvider;
use Ryan\Desktop\Provider\ProviderInterface;

class EcpayProvider
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['user:email'];

    public function __construct()
    {
        $this->merchantId = config('ecpay.MerchantId', '2000132');
        $this->hashKey = config('ecpay.HashKey', '5294y06JbISpM5x9');
        $this->hashIv = config('ecpay.HashIV', 'v77hoKGq4kWxNNIS');

        $this->apiUrl = 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5';
        $this->postData = [
            'MerchantID' => $this->merchantId,
        ];
    }

    /**
     * Handle the Post Data
     * @param array $data
     * @return $this
     * @throws ECPayException
     */
    public function setPostData(array $data = [])
    {
        // $asd = [
        //     'OrderId' => 'alpha_num|max:20',
        //     'ItemName' => 'required_if:Items,""',
        //     'TotalAmount' => 'required_if:Items,""',
        //     'Items' => 'required_if:ItemName,""',
        //     'ItemDescription' => 'required|max:200',
        //     'PaymentMethod' => 'in:'.implode(',', ECPayPaymentMethod::getConstantValues()->toArray()),
        //     'StoreId' => 'alpha_num|max:20',
        //     'ClientBackURL' => 'max:200',
        //     'ItemURL' => 'max:200',
        //     'Remark' => 'max:100',
        //     'ChooseSubPayment' => 'in:'.implode(',', ECPayPaymentMethodItem::getConstantValues()->toArray()),
        //     'OrderResultURL' => 'max:200',
        //     'NeedExtraPaidInfo' => 'in:'.implode(',', ECPayExtraPaymentInfo::getConstantValues()->toArray()),
        //     'IgnorePayment' => 'max:100',
        //     'PlatformID' => 'max:20',
        //     'CustomField1' => 'max:50',
        //     'CustomField2' => 'max:50',
        //     'CustomField3' => 'max:50',
        //     'CustomField4' => 'max:50',
        //     'ExpireDate' => 'int|min:1|max:60',
        //     'PaymentInfoURL' => 'url|max:200',
        //     'ClientRedirectURL' => 'url|max:200'
        // ];
        $this->postData['ReturnURL'] = 'd';
        $this->postData['MerchantTradeNo'] = str()->random(8);
        $this->postData['MerchantTradeDate'] = date('Y/m/d H:i:s');

        $this->postData['PaymentType'] = 'aio';
        $this->postData['TotalAmount'] = '100';
        $this->postData['TradeDesc'] = '款項';
        $this->postData['ChoosePayment'] = 'CVS';

        $this->postData['ItemName'] = 'd';

        $this->postData['CheckMacValue'] = $this->checkMacValueGenerator($this->postData);
        // $this->postData->setData($data)->setBasicInfo()->setOrderInfo()->setOptionalInfo()->optimize();
        return $this;
    }

    public function setOrderId(string $data)
    {
        unset($this->postData['CheckMacValue']);
        $this->postData['MerchantTradeNo'] = str($data)->remove('-');
        $this->postData['CheckMacValue'] = $this->checkMacValueGenerator($this->postData);
        return $this;
    }

    public function setTotal(string $data)
    {
        $this->postData['TotalAmount'] = $data;
        $this->postData['CheckMacValue'] = $this->checkMacValueGenerator($this->postData);
        return $this;
    }

    public function setPayment(string $data)
    {
        unset($this->postData['CheckMacValue']);
        $this->postData['ChoosePayment'] = $data;
        $this->postData['CheckMacValue'] = $this->checkMacValueGenerator($this->postData);
        return $this;
    }

    public function checkMacValueGenerator($data, $hashData = [])
    {
        if (empty($hashData)) {
            $hashData['key'] = $this->hashKey;
            $hashData['iv'] = $this->hashIv;
            $hashData['type'] = 'sha256';
        }
        if (isset($hashData['ignore'])) {
            foreach ($hashData['ignore'] as $field) {
                unset($data[$field]);
            }
        }
        uksort($data, array(self::class, 'merchantSort'));

        $checkCodeStr = 'HashKey=' . $hashData['key'];
        foreach ($data as $key => $val) {
            $checkCodeStr .= '&' . $key . '=' . $val;
        }
        $checkCodeStr .= '&HashIV=' . $hashData['iv'];
        if ($hashData['type'] === 'md5') {
            $checkCodeStr = self::replaceSymbol(strtolower(urlencode($checkCodeStr)));
            return strtoupper(md5($checkCodeStr));
        } else {
            $checkCodeStr = self::replaceSymbol(urlencode($checkCodeStr));
            return strtoupper(hash($hashData['type'], strtolower($checkCodeStr)));
        }
    }

    /**
     * 參數內特殊字元取代
     * 傳入    $sParameters    參數
     * 傳出    $sParameters    回傳取代後變數
     */
    public static function replaceSymbol($sParameters)
    {
        if (!empty($sParameters)) {
            $sParameters = str_replace('%2D', '-', $sParameters);
            $sParameters = str_replace('%2d', '-', $sParameters);
            $sParameters = str_replace('%5F', '_', $sParameters);
            $sParameters = str_replace('%5f', '_', $sParameters);
            $sParameters = str_replace('%2E', '.', $sParameters);
            $sParameters = str_replace('%2e', '.', $sParameters);
            $sParameters = str_replace('%21', '!', $sParameters);
            $sParameters = str_replace('%2A', '*', $sParameters);
            $sParameters = str_replace('%2a', '*', $sParameters);
            $sParameters = str_replace('%28', '(', $sParameters);
            $sParameters = str_replace('%29', ')', $sParameters);
        }
        return $sParameters;
    }

    /**
     * 自訂排序使用
     */
    private static function merchantSort($a, $b)
    {
        return strcasecmp($a, $b);
    }

    public function send()
    {
        $data = [
            'apiUrl' => $this->apiUrl,
            'postData' => $this->postData
        ];
        return $data;
        return view('ecpay::send', $data);
    }
}
