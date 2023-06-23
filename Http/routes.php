<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\TrustedLogin\Http\Controllers'], function()
{
    Route::get('/', 'TrustedLoginController@index');
    Route::get('/mailbox/{mailbox_id}/trustedlogin', ['uses' => 'TrustedLoginController@mailboxSettings', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']])->name('mailboxes.trustedlogin');
    Route::post('/mailbox/{mailbox_id}/trustedlogin', ['uses' => 'TrustedLoginController@saveMailboxSettings', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']])->name('mailboxes.trustedlogin');

});
