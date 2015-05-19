<?php

namespace Perk\Alpha;

use Perk\ServiceProvider;

class AlphaServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register('alpha');
    }

    public function boot()
    {
        parent::boot('alpha');
    }
}
