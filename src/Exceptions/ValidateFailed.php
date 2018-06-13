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
        return new static("The signature `{$signature}` found in the header s invalid");
    }

    public static function signingPrivateKeyNotSet()
    {
        return new static('The private key is not set. Make sure that the `cmspilot.private_key` config key is set.');
    }


    public function render($request)
    {
        return response(['error' => $this->getMessage()], 400);
    }

}