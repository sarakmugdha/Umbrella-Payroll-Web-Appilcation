<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\forgotMail;

Route::get('/forgot-password-email', function ($data) {
    $hash = base64_encode($data);

    Mail::to($data)->send(new forgotMail($hash));

    return 'Email sent successfully!';
})->name('mail.forgot-pwd');

Route::get('/', function () {
    return view('pdf.invoice');
});
