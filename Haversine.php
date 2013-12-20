<?php

    /*
     *  Calculate the distance between 2 points, in Laravel.
     *  @package Haversine
     *  @subpackage Models
     *  @version 0.0.1
     *  @author Douglas Grubba
     *  @access public
     *  @copyright 2012 Douglas Grubba
     *  @link http://douggdev.me
	*/
    class Haversine extends Eloquent {

        public function __construct()
        {
            parent::__construct();
        }

        /*
         *  find the n closest locations
         *  @param float $lat latitude of the point of interest
         *  @param float $lng longitude of the point of interest
         *  @return array
         */
        public function nearest( $table_name, $latitude, $longitude, $max_distance = 25, $max_locations = 10, $units = 'kilometers', $fields = false )
        {
            /*
             *  Allow for changing of units of measurement
             */
            switch ( $units ) {
                case 'miles':
                    //radius of the great circle in miles
                    $gr_circle_radius = 3959;
                break;
                case 'kilometers':
                    //radius of the great circle in kilometers
                    $gr_circle_radius = 6371;
                break;
            }

            /*
             *  Support the selection of certain fields
             */
            if( ! $fields ) {
                $fields = array( '*' );
            }

            /*
             *  Generate the select field for disctance
             */
            $distance_select = sprintf(
                "( %d * acos( cos( radians(%s) ) " .
                        " * cos( radians( latitude ) ) " .
                        " * cos( radians( longitude ) - radians(%s) ) " .
                        " + sin( radians(%s) ) * sin( radians( latitude ) ) " .
                    ") " . 
                ") " . 
                "AS distance",
                $gr_circle_radius,               
                $latitude,
                $longitude,
                $latitude
            );

            return DB::table( $table_name )
                ->select(DB::raw($distance_select))
                ->having( 'distance', '<', $max_distance )
                ->take( $max_locations )
                ->orderBy( 'distance', 'ASC' )
                ->get($fields);
        }

    }
