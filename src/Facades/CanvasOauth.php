<?php 
namespace xcesaralejandro\canvasoauth\Facades;

use Illuminate\Support\Facades\Facade;

class CanvasOauth extends Facade {
    protected static function getFacadeAccessor(){
        return 'canvas-oauth';
    }
}