<?php

Route::get('onepilot/ping', 'OnePilot\Client\Controllers\PingController@index');

Route::get('onepilot/validate', 'OnePilot\Client\Controllers\VersionController@index');
