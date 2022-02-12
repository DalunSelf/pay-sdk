<?php

namespace Ryan\Desktop\Facades;

use Illuminate\Support\Facades\Facade;
use Laravel\Socialite\Contracts\Factory;

/**
 * @method static \Laravel\Socialite\Contracts\Provider driver(string $driver = null)
 *
 * @see \Laravel\Socialite\SocialiteManager
 */
class Desktop extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
