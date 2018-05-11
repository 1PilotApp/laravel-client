<?php
/**
 * Created by PhpStorm.
 * User: PAV
 * Date: 11.05.2018
 * Time: 10:31
 */

namespace cmspilot\client\Exceptions;

use Exception;
use Illuminate\Http\Request;

class ValidateFailed extends Exception
{

    public static function missingSignature()
    {
        return new static('The request did not contain a header named `HTTP_HASH`.');
    }

    public static function invalidSignature($signature)
    {
        return new static("The signature `{$signature}` found in the header named `OhDear-Signature` is invalid. Make sure that the `ohdear-webhooks.signing_secret` config key is set to the value you found on the OhDear dashboard. If you are caching your config try running `php artisan clear:cache` to resolve the problem.");
    }

    public static function signingPrivateKeyNotSet()
    {
        return new static('The OhDear webhook signing secret is not set. Make sure that the `ohdear-webhooks.signing_secret` config key is set to the value you found on the Stripe dashboard.');
    }


    public function render($request)
    {
        return response(['error' => $this->getMessage()], 400);
    }

}