<?php

namespace MWGuerra\AppDesign\Facades;

use Illuminate\Support\Facades\Facade;

class AppDesign extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'app-design';
    }
}
