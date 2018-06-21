<?php

Route::get('cmspilot/ping', 'CmsPilot\Client\Controllers\PingController@index');

Route::get('cmspilot/validate', 'CmsPilot\Client\Controllers\VersionController@index');
