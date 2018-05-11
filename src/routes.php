<?php

Route::get('demo/test', function () {
return 'Test';
});

Route::get('/cmspilot/validate', 'CmsPilot\Client\VersionController@index');