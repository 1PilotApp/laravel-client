<?php

namespace CmsPilot\Client\Middelwares;

use Closure;
use CmsPilot\LaravelClient\Exceptions\ValidateFailed;

class Authentication
{
    public function handle($request, Closure $next)
    {
        return $next($request);

        $signature = $request->header('HTTP_HASH');
        $stamp = $request->header('HTTP_STAMP');

        if (!$signature) {
            throw ValidateFailed::missingSignature();
        }

        if ($this->isValidateTimeStamp($stamp)) {
            throw ValidateFailed::invalidTimestamp();
        }

        if (!$this->isValidSignature($signature, $stamp)) {
            throw ValidateFailed::invalidSignature($signature);
        }

        return $next($request);
    }

    protected function isValidSignature(string $signature, string $stamp)
    {
        $secret = config('cmspilot.private_key');

        if (empty($secret)) {
            throw ValidateFailed::signingPrivateKeyNotSet();
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
        if ($secret = config('cmspilot.skip_time_stamp_validation')) {
            return true;
        }

        if ((stamp > time() - 360) && (stamp < time() + 360)) {
            return true;
        }

        return false;
    }
}