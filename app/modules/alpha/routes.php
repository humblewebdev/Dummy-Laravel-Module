<?php

Route::get('alpha', [
    'as' => 'alpha.v1.alpha',
    'uses' => 'Perk\Alpha\Controller\v1\Alpha@alpha'
]);
