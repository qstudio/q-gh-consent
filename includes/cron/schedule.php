<?php

// namespace ##
namespace Q_Meet_Our_Students\Cron;

use Q_Meet_Our_Students\Core\Helper as Helper;

/**
 * CRON Schedules
 *
 * @package   Q_Meet_Our_Students
 */
class Schedule {

    /**
     * Construct
     *
     * @since       0.2
     * @return      void
     */
    public function __construct()
    {

        // add new schedule to cron -- filter need to run on all page loads ##
        add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );

    }


    /**
     * Filter Cron Schedules to allow for shorter delay
     *
     * @since       0.4
     * @param       Array    $schedules
     * @return      Array
     * @link        http://codex.wordpress.org/Function_Reference/wp_get_schedules
     */
    function cron_schedules( $schedules )
    {

        // Adds once weekly to the existing schedules.
        $schedules['thirty'] = array(
            'interval' => 1800,
            'display' => __( 'Each Thirty Minutes' )
        );

        return $schedules;

    }


    /**
     * Setup a cron task - called via plugin activation hook
     *
     * @since 0.2
     * @return void
     */
    public function wp_schedule_event( $schedule = null, $handle = null )
    {

        // log ##
        Helper::log( 'Schedule CRON task: '.$handle );

        // sanity check ##
        if ( is_null( $handle ) || is_null( $schedule ) ) { return false; }

        if ( ! wp_next_scheduled( $handle ) ) {

            wp_schedule_event( time(), $schedule, $handle );

        }

    }


    /**
     * Clear a defined task - called via plugin deactivation hook
     *
     * @since 0.2
     * @return void
     */
    function wp_clear_scheduled_hook( $handle = null )
    {

        // sanity check ##
        if ( is_null( $handle ) ) { return false; }

        // log ##
        Helper::log( 'Remove CRON task: '.$handle );

        // clear poll ##
        wp_clear_scheduled_hook( $handle );

    }


}
