<?php

namespace Perk\Charlie;

use Perk\ServiceProvider;

class CharlieServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register('charlie');
    }

    public function boot()
    {
        parent::boot('charlie');
    }
}
