<?php

namespace Omnipay\IPay88\Message;


use Omnipay\Common\Currency;

class CompletePurchaseRequest extends AbstractRequest
{
    protected $endpoint = 'https://www.mobile88.com/epayment/enquiry.asp';

    public function getData()
    {
        $this->guardParameters();

        if (empty($_POST)){
            $_POST = file_get_contents('php://input');
            $_POST = json_decode($_POST,true);
        }
        
        $data = $this->httpRequest->request->all();
        
        if (empty($data)) {
            $data = $_POST;
        }

        $data['ComputedSignature'] = $this->signature(
            $this->getMerchantKey(),
            $this->getMerchantCode(),
            $data['PaymentId'],
            $data['RefNo'],
            $data['Amount'],
            $data['Currency'],
            $data['Status']
        );

        return $data;
    }

    public function sendData($data)
    {
        $data['ReQueryStatus'] = $this->httpClient
            ->request('post', $this->endpoint.'?'.http_build_query([
                'MerchantCode' => $this->getMerchantCode(),
                'RefNo' => $data['RefNo'],
                'Amount' => $data['Amount']
                ]), [], json_encode([]))
            ->getBody()
            ->getContents();

        return $this->response = new CompletePurchaseResponse($this, $data);
    }

    protected function signature($merchantKey, $merchantCode, $paymentId, $refNo, $amount, $currency, $status)
    {
        $amount = str_replace([',', '.'], '', $amount);

        $paramsInArray = [$merchantKey, $merchantCode, $paymentId, $refNo, $amount, $currency, $status];

        return $this->createSignatureFromString(implode('', $paramsInArray));
    }
}
