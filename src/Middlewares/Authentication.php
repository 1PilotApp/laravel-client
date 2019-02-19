<?php

namespace OnePilot\Client\Middlewares;

use Closure;
use OnePilot\Client\Exceptions\OnePilotException;

class Authentication
{
    public function handle($request, Closure $next)
    {
        $signature = $request->header('hash');
        $stamp = $request->header('stamp');

        if (!$signature) {
            throw OnePilotException::missingSignature();
        }

        if (!$this->isValidateTimeStamp($stamp)) {
            throw OnePilotException::invalidTimestamp();
        }

        if (!$this->isValidSignature($signature, $stamp)) {
            throw OnePilotException::invalidSignature($signature);
        }

        return $next($request);
    }

    protected function isValidSignature(string $signature, string $stamp)
    {
        $secret = config('onepilot.private_key');

        if (empty($secret)) {
            throw OnePilotException::signingPrivateKeyNotSet();
        }

        $computedSignature = hash_hmac('sha256', $stamp, $secret);

        return hash_equals($signature, $computedSignature);
    }

    /**
     * Validate timestamp. The meaning of this check is to enhance security by
     * making sure any token can only be used in a short period of time.
     *
     * @return boolean
     */
    private function isValidateTimeStamp($stamp)
    {
        if ($secret = config('onepilot.skip_time_stamp_validation')) {
            return true;
        }

        if (($stamp > time() - 360) && ($stamp < time() + 360)) {
            return true;
        }

        return false;
    }
}