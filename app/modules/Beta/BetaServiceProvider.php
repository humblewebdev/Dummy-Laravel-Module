<?php

namespace Perk\Beta;

use Perk\ServiceProvider;

class BetaServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register('Beta');
    }

    public function boot()
    {
        parent::boot('Beta');
    }
}
