<?php

Route::get('onepilot/ping', 'OnePilot\Client\Controllers\PingController@index');
Route::post('onepilot/ping', 'OnePilot\Client\Controllers\PingController@index');

Route::get('onepilot/validate', 'OnePilot\Client\Controllers\VersionController@index');
Route::post('onepilot/validate', 'OnePilot\Client\Controllers\VersionController@index');

Route::post('onepilot/mail-tester', 'OnePilot\Client\Controllers\MailTesterController@send');
