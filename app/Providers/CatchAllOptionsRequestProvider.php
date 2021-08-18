<?php 

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CatchAllOptionsRequestProvider extends ServiceProvider{

    public function register(){
        $request = app('request');
        if($request->isMethod('OPTIONS')){
            app()->options($request->path(), function (){ return response('',200);});
        }
    }
}