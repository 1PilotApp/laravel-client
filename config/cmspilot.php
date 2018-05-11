<?php


return [
    /*
     * cmsPilot will sign validation call using a secret. Please set a strong one
     */
    'private_key' => env('CMSPILOT_PRIVATE_KEY'),
    'skip_time_stamp_validation' => env('CMSPILOT_SKIP_TIME_STAMP_VALIDATION'),

];