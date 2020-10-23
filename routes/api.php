<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['alt_sso'])->group(function(){
  Route::namespace('Api')->group(function(){
    
    
    /* make:api New Route */
  });
});
