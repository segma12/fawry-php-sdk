<?php

namespace Segma\FawryPhpSdk;


class Fawry
{
    public $merchant_code;
    public $merchant_key;
    public $app_debug;

    /**
     * @param $merchant_code
     * @param $merchant_key
     * @param false $app_debug
     */
    public function __construct($merchant_code, $merchant_key, $app_debug = false)
    {
        $this->merchant_code = $merchant_code;
        $this->merchant_key = $merchant_key;
        $this->app_debug = $app_debug;
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    private function post($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, true));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept'       => 'application/json',
                'Content-Length: ' . strlen(json_encode($data))
            )
        );
        return json_decode(curl_exec($ch));
    }

    /**
     * @param $url
     * @param array $data
     * @return mixed
     */
    private function get($url, array $data) {
        $params = http_build_query($data);
        $ch = curl_init(urldecode($url."?".$params));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($ch));
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    private function delete($url, $data)
    {
        $params = http_build_query($data);
        $ch = curl_init(urldecode($url."?".$params));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($ch));
    }

    /**
     * @param $customer_id
     * @param $customer_mobile
     * @param $customer_email
     * @param $card_number
     * @param $expiry_year
     * @param $expiry_month
     * @param $cvv
     * @param false $isDefault
     * @return mixed
     */
    public function createCardToken($customer_id, $customer_mobile, $customer_email, $card_number, $expiry_year, $expiry_month, $cvv, $isDefault = false) {
        $url = $this->app_debug ?
            'https://atfawry.fawrystaging.com/fawrypay-api/api/cards/cardToken' :
            'https://www.atfawry.com/ECommerceWeb/api/cards/cardToken';
        return $this->post($url, [
                "merchantCode" => $this->merchant_code,
                "customerProfileId" => md5($customer_id),
                "customerMobile" => $customer_mobile,
                "customerEmail" => $customer_email,
                "cardNumber" => $card_number,
                "expiryYear" => $expiry_year,
                "expiryMonth" => $expiry_month,
                "cvv" => $cvv,
                'isDefault' => $isDefault
            ]
        );
    }

    /**
     * @param $customer_id
     * @return mixed
     */
    public function listCustomerTokens($customer_id)
    {
        $url = $this->app_debug ?
            'https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/cards/cardToken' :
            'https://www.FawryPay.com/ECommerceWeb/Fawry/cards/cardToken';
        return $this->get($url, [
                'merchantCode' => $this->merchant_code,
                'customerProfileId' => md5($customer_id),
                'signature' => hash('sha256',
                    $this->merchant_code .
                    md5($customer_id) .
                    $this->merchant_key
                ),
            ]
        );
    }

    /**
     * @param $customer_id
     * @param $customer_card_token
     * @return mixed
     */
    public function deleteCardToken($customer_id, $customer_card_token)
    {
        $url = $this->app_debug ? 'https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/cards/cardToken':
        'https://www.FawryPay.com/ECommerceWeb/Fawry/cards/cardToken';
        return $this->delete($url, [
                'merchantCode' => $this->merchant_code,
                'customerProfileId' => md5($customer_id),
                'cardToken' => $customer_card_token,
                'signature' => hash('sha256', $this->merchant_code.md5($customer_id).$customer_card_token.$this->merchant_key
                )
            ]
        );
    }

    /**
     * @param $merchantRefNum
     * @param $customer_card_token
     * @param $customer_id
     * @param $customer_mobile
     * @param $customer_email
     * @param $amount
     * @param string $currency
     * @param array $chargeItems
     * @param string $description
     * @return mixed
     */
    public function payByCardToken($merchantRefNum, $customer_card_token, $customer_id, $customer_mobile, $customer_email, $amount, $currency = 'EGP', $chargeItems = [], $description = '')
    {
        $url = $this->app_debug ? 'https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/payments/charge' :
        'https://www.atfawry.com/ECommerceWeb/Fawry/payments/charge';
        return $this->post($url, [
                'merchantCode' => $this->merchant_code,
                'merchantRefNum' => $merchantRefNum,
                'paymentMethod' => 'CARD',
                'cardToken' => $customer_card_token,
                'customerProfileId' => md5($customer_id),
                'customerMobile' => $customer_mobile,
                'customerEmail' => $customer_email,
                'amount' => number_format((float) $amount, 2, '.', ''),
                'currencyCode' => $currency,
                'chargeItems' => $chargeItems,
                'description' => $description,
                'signature' => hash('sha256',
                    $this->merchant_code .
                    $merchantRefNum.
                    md5($customer_id) .
                    'CARD' .
                    number_format((float) $amount, 2, '.', '').
                    $customer_card_token .
                    $this->merchant_key
                )
            ]
        );
    }

    /**
     * @param $merchantRefNum
     * @param $customer_card_token
     * @param $cvv
     * @param $customer_id
     * @param $customer_name
     * @param $customer_mobile
     * @param $customer_email
     * @param $amount
     * @param $callbackURL
     * @param array $chargeItems
     * @param string $language
     * @param string $currency
     * @param null $description
     * @return mixed
     */
    public function payByCardToken3DS($merchantRefNum, $customer_card_token, $cvv, $customer_id, $customer_name, $customer_mobile, $customer_email, $amount, $callbackURL, $chargeItems = [], $authCaptureModePayment = false, $language = 'en-gb', $currency = 'EGP', $description = null)
    {
        $url = $this->app_debug ? 'https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/payments/charge' :
            'https://www.atfawry.com/ECommerceWeb/Fawry/payments/charge' ;
        return $this->post($url, [
                "merchantCode" => $this->merchant_code,
                "customerName" => $customer_name,
                "customerMobile" => $customer_mobile,
                "customerEmail" => $customer_email,
                "customerProfileId" => md5($customer_id),
                "cardToken" => $customer_card_token,
                "cvv" => $cvv,
                "merchantRefNum" => $merchantRefNum,
                "amount" => number_format((float) $amount, 2, '.', ''),
                "currencyCode" => $currency,
                "language" => $language,
                "chargeItems" => $chargeItems,
                "enable3DS" => true,
                "authCaptureModePayment" => $authCaptureModePayment,
                "returnUrl" => $callbackURL,
                "signature" => hash('sha256', $this->merchant_code.
                    $merchantRefNum .
                    md5($customer_id) .
                    'CARD' .
                    number_format((float) $amount, 2, '.', '') .
                    $customer_card_token .
                    $cvv .
                    $callbackURL .
                    $this->merchant_key
                ),
                "paymentMethod" => "CARD",
                "description" => $description
            ]
        );
    }

    /**
     * @param $merchantRefNum
     * @param $card_number
     * @param $card_expiry_year
     * @param $card_expiry_month
     * @param $cvv
     * @param $customer_id
     * @param $customer_name
     * @param $customer_mobile
     * @param $customer_email
     * @param $amount
     * @param array $chargeItems
     * @param string $language
     * @param string $currency
     * @param null $description
     * @return mixed
     */
    public function payByCard($merchantRefNum, $card_number, $card_expiry_year, $card_expiry_month, $cvv, $customer_id, $customer_name , $customer_mobile, $customer_email, $amount, array $chargeItems = [], $language = 'en-gb' , $currency = 'EGP' , $description = null)
    {
        $url = $this->app_debug ? 'https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/payments/charge' :
            'https://www.atfawry.com/ECommerceWeb/Fawry/payments/charge';
        return $this->post($url, [
                "merchantCode" => $this->merchant_code,
                "customerName" => $customer_name,
                "customerMobile" => $customer_mobile,
                "customerEmail" => $customer_email,
                "customerProfileId" => md5($customer_id),
                "cardNumber" => $card_number,
                "cardExpiryYear" => $card_expiry_year,
                "cardExpiryMonth" => $card_expiry_month,
                "cvv" => $cvv,
                "merchantRefNum" => $merchantRefNum,
                "amount" => number_format((float) $amount, 2, '.', ''),
                "currencyCode" => $currency,
                "language" => $language,
                "chargeItems" => $chargeItems,
                "paymentMethod" => "CARD",
                "description" => $description,
                "signature" => hash("sha256", $this->merchant_code .
                    $merchantRefNum .
                    md5($customer_id) .
                    'CARD' .
                    number_format((float) $amount, 2, '.', '') .
                    $card_number .
                    $card_expiry_year .
                    $card_expiry_month .
                    $cvv .
                    $this->merchant_key
                )
            ]
        );
    }

    /**
     * @param $merchantRefNum
     * @param $card_number
     * @param $card_expiry_year
     * @param $card_expiry_month
     * @param $cvv
     * @param $customer_id
     * @param $customer_name
     * @param $customer_mobile
     * @param $customer_email
     * @param $amount
     * @param $calbackURL
     * @param array $chargeItems
     * @param string $language
     * @param string $currency
     * @param null $description
     * @return mixed
     */
    public function payByCard3DS($merchantRefNum, $card_number, $card_expiry_year, $card_expiry_month, $cvv, $customer_id, $customer_name , $customer_mobile, $customer_email, $amount, $calbackURL, array $chargeItems = [], $authCaptureModePayment = false, $language = 'en-gb' , $currency = 'EGP' , $description = null)
    {
        $url = $this->app_debug ? 'https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/payments/charge'
            : 'https://www.atfawry.com/ECommerceWeb/Fawry/payments/charge';
        return $this->post($url, [
                "merchantCode" => $this->merchant_code,
                "customerName" => $customer_name,
                "customerMobile" => $customer_mobile,
                "customerEmail" => $customer_email,
                "customerProfileId" => md5($customer_id),
                "cardNumber" => $card_number,
                "cardExpiryYear" => $card_expiry_year,
                "cardExpiryMonth" => $card_expiry_month,
                "cvv" => $cvv,
                "merchantRefNum" => $merchantRefNum,
                "amount" => number_format((float) $amount, 2, '.', ''),
                "currencyCode" => $currency,
                "language" => $language,
                "chargeItems" => $chargeItems,
                "paymentMethod" => "CARD",
                "description" => $description,
                "enable3DS" => true,
                "authCaptureModePayment" => $authCaptureModePayment,
                "returnUrl" => $calbackURL,
                "signature" => hash("sha256", $this->merchant_code .
                    $merchantRefNum .
                    md5($customer_id) .
                    'CARD' .
                    number_format((float) $amount, 2, '.', '') .
                    $card_number .
                    $card_expiry_year .
                    $card_expiry_month .
                    $cvv .
                    $calbackURL .
                    $this->merchant_key
                )
            ]
        );
    }

    /**
     * @param $reference_number
     * @param $refund_amount
     * @param null $reason
     * @return mixed
     */
    public function refund($reference_number, $refund_amount, $reason = null)
    {
        $url = $this->app_debug  ? 'https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/payments/refund' :
            'https://www.atfawry.com/ECommerceWeb/Fawry/payments/refund';
        return $this->post($url, [
                'merchantCode' => $this->merchant_code,
                'referenceNumber' => $reference_number,
                'refundAmount' => number_format((float) $refund_amount, 2, '.', ''),
                'reason' => $reason,
                'signature' => hash(
                    'sha256',
                    $this->merchant_code .
                    $reference_number .
                    number_format((float) $refund_amount, 2, '.', '').
                    $reason .
                    $this->merchant_key
                )
            ]
        );
    }

    /**
     * @param $merchantAccount
     * @param $orderReferenceNumber
     * @param string $lang
     * @return mixed
     */
    public function cancelUnpaidPayment($orderReferenceNumber, $lang = 'en-gb') {
        $url = $this->app_debug ? 'https://atfawry.fawrystaging.com/ECommerceWeb/api/orders/cancel-unpaid-order' :
            'https://www.atfawry.com/ECommerceWeb/api/orders/cancel-unpaid-order';
        return $this->post($url, [
                'merchantAccount' => $this->merchant_code,
                'orderRefNo' => $orderReferenceNumber,
                'lang' => $lang,
                'signature' => hash(
                    'sha256',
                    $this->merchant_code .
                    $orderReferenceNumber .
                    $lang .
                    $this->merchant_code
                )
            ]
        );
    }
}
