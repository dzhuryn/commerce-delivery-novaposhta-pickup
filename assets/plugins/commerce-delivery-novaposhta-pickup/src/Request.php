<?php
namespace CommerceDeliveryNpPickup;


class Request
{
    private $apiKey;

    public function __construct($apiKey)
    {

        $this->apiKey = $apiKey;
    }

    public function request($calledMethod,$modelName = 'Address'){
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.novaposhta.ua/v2.0/json/",
            CURLOPT_RETURNTRANSFER => True,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "content-type: application/json"
            ],
        ]);

        $postData = [
            'apiKey' => $this->apiKey,
            'modelName' => $modelName,
            'calledMethod' => $calledMethod,
        ];

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
        $response = curl_exec($curl);
        $response = json_decode($response,true);
        curl_close($curl);

        return $response;
    }

}