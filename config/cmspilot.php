<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CmsPilot - Private Key
    |--------------------------------------------------------------------------
    |
    | cmsPilot will sign validation call using this secret. Please set
    | a random generated string.
    |
    */

    'private_key' => env('CMSPILOT_PRIVATE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Disable timestamp verification
    |--------------------------------------------------------------------------
    |
    | Only do that if your server is not at time and you can't fix that
    |
    | Set this option to TRUE will considerably reduce the security
    |
    */

    'skip_time_stamp_validation' => env('CMSPILOT_SKIP_TIME_STAMP_VALIDATION'),

];
