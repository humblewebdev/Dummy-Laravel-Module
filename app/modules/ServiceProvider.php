<?php
namespace Perk;

/**
 * ServiceProvider
 *
 * @abstract
 * @package
 * @version   1.0.0
 * @copyright 1997-2005 The PHP Group
 * @author    Thomas Veilleux Thomas@perk.com
 * @license
 */
abstract class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * boot
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        if ($module = $this->getModule(func_get_args())) {
            $this->package(
                'app/' . $module,
                $module,
                app_path() . '/modules/' . $module
            );
        }
    }

    /**
     * register
     *
     * @access public
     * @return void
     */
    public function register()
    {
        if ($module = $this->getModule(func_get_args())) {

            $this->app[ 'config' ]->package(
                'app/' . $module,
                app_path() . '/modules' . $module . '/config'
            );

            $routes = app_path() . '/modules/' . $module . '/routes.php';
            if (file_exists($routes)) {
                require $routes;
            }
        }
    }

    /**
     * getModule
     *
     * @param mixed $args
     *
     * @access public
     * @return void
     */
    public function getModule($args)
    {
        $module = ( isset( $args[ 0 ] ) and is_string($args[ 0 ]) ) ? $args[ 0 ] : null;

        return $module;
    }
}
