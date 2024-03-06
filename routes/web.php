<?php

use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    return to_route('filament.admin.auth.login');
});