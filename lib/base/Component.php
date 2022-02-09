<?php
namespace ConnectpxBooking\Lib\Base;

use ConnectpxBooking\Lib;

/**
 * Class Component
 * @package Bookly\Lib\Base
 */
abstract class Component extends Cache
{
    /**
     * Array of reflection objects of child classes.
     * @var \ReflectionClass[]
     */
    private static $reflections = array();

    /******************************************************************************************************************
     * Public methods                                                                                                 *
     ******************************************************************************************************************/

    /**
     * Render a template file.
     *
     * @param string $template
     * @param array  $variables
     * @param bool   $echo
     * @return void|string
     */
    public static function renderTemplate( $template, $variables = array(), $echo = true )
    {
        extract( array( 'self' => get_called_class() ) );
        extract( $variables );

        // Start output buffering.
        ob_start();
        ob_implicit_flush( 0 );

        include Lib\Plugin::pluginDir() . $template . '.php';

        if ( ! $echo ) {
            return ob_get_clean();
        }

        echo ob_get_clean();
    }

    /******************************************************************************************************************
     * Protected methods                                                                                              *
     ******************************************************************************************************************/

    /**
     * Verify CSRF token.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        return wp_verify_nonce( static::parameter( 'csrf_token' ), 'bookly' ) == 1;
    }

    /**
     * Get path to component directory.
     *
     * @return string
     */
    protected static function directory()
    {
        return dirname( static::reflection()->getFileName() );
    }

    /**
     * Check if there is a parameter with given name in the request.
     *
     * @param string $name
     * @return bool
     */
    protected static function hasParameter( $name )
    {
        return array_key_exists( $name, $_REQUEST );
    }

    /**
     * Get class reflection object.
     *
     * @return \ReflectionClass
     */
    protected static function reflection()
    {
        $class = get_called_class();
        if ( ! isset ( self::$reflections[ $class ] ) ) {
            self::$reflections[ $class ] = new \ReflectionClass( $class );
        }

        return self::$reflections[ $class ];
    }

    /**
     * Get request parameter by name.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected static function parameter( $name, $default = null )
    {
        return static::hasParameter( $name ) ? stripslashes_deep( $_REQUEST[ $name ] ) : $default;
    }

    /**
     * Get all request parameters.
     *
     * @return mixed
     */
    protected static function parameters()
    {
        return stripslashes_deep( $_REQUEST );
    }

    /**
     * Get all POST parameters.
     *
     * @return mixed
     */
    protected static function postParameters()
    {
        return stripslashes_deep( $_POST );
    }

    /**
     * Escape params for admin.php?page
     *
     * @param $page_slug
     * @param array $params
     * @return string
     */
    public static function escAdminUrl( $page_slug, $params = array() )
    {
        $path = 'admin.php?page=' . $page_slug;
        if ( ( $query = build_query( $params ) ) != '' ) {
            $path .= '&' . $query;
        }

        return admin_url( $path );
    }

    /**
     * Render page.
     */
    public static function pageSlug()
    {
        return static::$pageSlug;
    }
}