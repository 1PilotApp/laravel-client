<?php

namespace OnePilot\Client\Exceptions;

use Exception;

class OnePilotException extends Exception
{
    public static function missingSignature()
    {
        return new static(
            'The request did not contain a header named `HTTP_HASH`.', 400
        );
    }

    public static function invalidSignature($signature)
    {
        return new static(
            "The signature `{$signature}` found in the header is invalid", 400
        );
    }

    public static function invalidTimestamp()
    {
        return new static(
            "The timestamp found in the header is invalid", 400
        );
    }

    public static function signingPrivateKeyNotSet()
    {
        return new static(
            'The private key is not set. Make sure that the `onepilot.private_key` config key is set.', 400
        );
    }

    public function render($request)
    {
        $httpCode = ($this->code >= 400 && $this->code < 600) ? $this->code : 500;

        $content = [
            'message' => $this->getMessage(),
            'status'  => $httpCode,
            'data'    => [],
        ];

        if (!empty($previous = $this->getPrevious())) {
            $content['data']['previous'] = [
                'message' => $previous->getMessage(),
            ];
        }

        return response($content, $httpCode);
    }

    public function report()
    {
        // disable reporting
    }
}
