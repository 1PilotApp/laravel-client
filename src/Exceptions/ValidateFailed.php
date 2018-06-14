<?php

namespace CmsPilot\Client\Exceptions;

use Exception;

class ValidateFailed extends Exception
{

    public static function missingSignature()
    {
        return new static('The request did not contain a header named `HTTP_HASH`.');
    }

    public static function invalidSignature($signature)
    {
        return new static("The signature `{$signature}` found in the header is invalid");
    }

    public static function invalidTimestamp()
    {
        return new static("The timestamp found in the header is invalid");
    }

    public static function signingPrivateKeyNotSet()
    {
        return new static('The private key is not set. Make sure that the `cmspilot.private_key` config key is set.');
    }


    public function render($request)
    {
        return response([
            'message' => $this->getMessage(),
            'status'  => 400,
            'data'    => [],
        ], 400);
    }

}