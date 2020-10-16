<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['alt_sso'])->group(function(){
  Route::namespace('Api')->group(function(){
    
    Route::apiResource('/tes', 'TesController');
    Route::apiResource('/tes2s', 'Tes2Controller');
    /* make:api New Route */
  });
});
