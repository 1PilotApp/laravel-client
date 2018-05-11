<?php
/**
 * Created by PhpStorm.
 * User: PAV
 * Date: 11.05.2018
 * Time: 10:28
 */

namespace CmsPilot\LaravelClient\Middelwares;

use Closure;
use CmsPilot\Client\Exceptions\ValidateFailed;

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

        if ($this->isValidateTimeStamp()) {
            throw ValidateFailed::invalidTimestamp();
        }

        if (!$this->isValidSignature($signature, $stamp)) {
            throw ValidateFailed::invalidSignature($signature);
        }

        return $next($request);
    }

    protected function isValidSignature(string $signature, string $payload): bool
    {
        $secret = config('cmspilot.private_key');

        if (empty($secret)) {
            throw ValidateFailed::signingPrivateKeyNotSet();
        }

        $computedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($signature, $computedSignature);
    }

    /**
     * Validate timestamp. The meaning of this check is to enhance security by
     * making sure any token can only be used in a short period of time.
     *
     * @return boolean
     */
    private function isValidateTimeStamp()
    {
        if ($secret = config('cmspilot.skip_time_stamp_validation')) {
            return true;
        }

        if (($this->stamp > time() - 360) && ($this->stamp < time() + 360)) {
            return true;
        }

        return false;
    }
}