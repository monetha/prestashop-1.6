<?php

namespace Monetha\Services;

class HttpService
{
    public static function callApi($uri, $method, $body = null, $headers)
    {
        $chSign = curl_init();

        $options = [
            CURLOPT_URL => $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER =>  array_merge($headers, array(
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            )),
        ];

        if ($method !== 'GET' && $body) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }

        curl_setopt_array($chSign, $options);

        $res = curl_exec($chSign);
        $error = curl_error($chSign);
        $resStatus = curl_getinfo($chSign, CURLINFO_HTTP_CODE);

        if (($resStatus >= 400)
            && isset($res)
            && isset(json_decode($res)->code)) {
            if(json_decode($res)->code == 'AMOUNT_TOO_BIG') {
                return 'AMOUNT_TOO_BIG';
            }
            if(json_decode($res)->code == 'AMOUNT_TOO_SMALL') {
                return 'AMOUNT_TOO_SMALL';
            }
            if(json_decode($res)->code == 'INVALID_PHONE_NUMBER') {
                return 'INVALID_PHONE_NUMBER';
            }
            if(json_decode($res)->code == 'AUTH_TOKEN_INVALID') {
                return 'AUTH_TOKEN_INVALID';
            }
            if(json_decode($res)->code == 'INTERNAL_ERROR') {
                return 'INTERNAL_ERROR';
            }
            if(json_decode($res)->code == 'UNSUPPORTED_CURRENCY') {
                return 'UNSUPPORTED_CURRENCY';
            }
            if(json_decode($res)->code == 'PROCESSOR_MISSING') {
                return 'PROCESSOR_MISSING';
            }
            if(json_decode($res)->code == 'INVALID_PHONE_COUNTRY_CODE') {
                return 'INVALID_PHONE_COUNTRY_CODE';
            }
        }

        if ($error) {
            return 'INTERNAL_ERROR';
        }

        if ($resStatus < 200 || $resStatus >= 300) {
            return 'INTERNAL_ERROR';
        }

        $resJson = json_decode($res);

        curl_close($chSign);

        return $resJson;
    }
}
