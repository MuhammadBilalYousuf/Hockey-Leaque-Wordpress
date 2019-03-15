<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * ScoresPro Free Scores
 * Soccer: https://free.scorespro.com/soccer.php
 * Basketball: https://free.scorespro.com/basketball.php
 * Hockey: https://free.scorespro.com/hockey.php
 * ...
 */

class LSFS_Scorespro_Free extends LSFS_Integration {

    /**
     * Used for displaying a documentation screen and button
     * If true, the method documentation() should be defined 
     * @var boolean
     */
    public $has_documentation = true;

    /**
     * Start the Integration
     */
    public function __construct() {
        
        $this->settings_id = 'scorespro_free';
        $this->title = __( 'Scorespro Free', 'live-scores-for-sportspress' );
        $this->desc = __( 'Free Scorespro live scores for SportsPress. Retrieve the Live Scores from Scorespro from their free service and show them on your site styled by SportsPress.', 'live-scores-for-sportspress' );
        $this->image = LSFS_URI . '/assets/images/integration-scorespro-free.png';

        parent::__construct();

        add_shortcode( 'live_scorespro_free', array( $this, 'shortcode' ) );
        add_action( 'lsfs_ajax_event_results', array( $this, 'event_results' ), 20, 2 );
    }

    /**
     * Set the fields
     */
    public function set_fields() {
        $this->fields = apply_filters( 'lsfs_settings_fields_' . $this->settings_id, array(
            'sport' => array(
                'type' => 'select',
                'name' => 'sport',
                'title' =>  __( 'Sport', 'live-scores-for-sportspress' ),
                'default' => 'soccer',
                'desc' => __( 'Select the default sport for Scorespro Live Scores', 'live-scores-for-sportspress' ),
                'options' => array(
                    'soccer'     => __( 'Soccer', 'live-scores-for-sportspress' ),
                    'basketball' => __( 'Basketball', 'live-scores-for-sportspress' ),
                    'hockey'     => __( 'Hockey', 'live-scores-for-sportspress' ),
                    'volleyball' => __( 'Volleyball', 'live-scores-for-sportspress' )
                )
            )
        ) );
    }

    /**
     * Documentation
     * @return void 
     */
    public function documentation() {
        include_once 'views/html-integration-scorespro-free.php';
    }

    /**
     * Shortcode output
     * @param  array $atts Shortcode arguments
     * @return string       
     */
    public function shortcode( $atts ) {

        $atts = shortcode_atts(
            array(
                'type' => $this->get_option( 'sport', 'soccer' ), 
            ), $atts, 'live_scorespro_free' );

        $output = '';

        $events = $this->get_events( $atts['type'] );
        $id = 'scorespro_free_' . $atts['type'];
        include 'views/html-integration-scorespro-free-events.php';

        ob_start();

        $output = ob_get_clean();
        return $output;
    }

    /**
     * Get Events
     * @param  string $type Type of the sport
     * @return array       
     */
    public function get_events( $type = '' ) {
        
        if( ! $type ) {
            $type = $this->get_option('sport');
        }

        if( ! method_exists( $this, 'get_events_' . $type ) ) {
            return '';
        }

        $events = $this->{ 'get_events_' . $type }();

        return $events;
    }

    /**
     * Get Soccer Events    
     * @return array 
     */
    public function get_events_soccer() {
      
        $response = wp_remote_get( 'https://free.scorespro.com/live_free.php');
        $response = wp_remote_get( 'https://www.scorespro.com/livescore/' );
        $output = '';
        ob_start();
            echo wp_remote_retrieve_body( $response );
        $output = ob_get_clean();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);

        $doc->loadHTML( $output );
        $node = $doc->getElementById('mainfeed');

        $scores = $node->getElementsByTagName('td')->item(0);
        $array = array();
        $division = '';

        foreach ( $node->childNodes as $childNode ){
        
            if( $childNode->hasAttribute( 'class' ) && false !== strpos( $childNode->getAttribute( 'class' ), 'compgrp' ) ) {
                
                $tableNodes = $childNode->childNodes;
                $count = 0; 
                foreach ( $childNode->childNodes as $tableNode ) {
                    $count++;
                    if( ! $tableNode->childNodes ) {
                        continue;
                    }
                    foreach ( $tableNode->childNodes as $tbody ) {
                        
                        foreach ( $tbody->childNodes as $trNode ) {
                            $count_td = 0; 
                            
                            if( $count === 1 ) {
                                    // Only the first table is a division.
                                    $division = htmlentities( $trNode->textContent, null, 'utf-8' );
                                    $division = trim( str_replace( '&nbsp;','', $division ) );
                                    $array[ $division ][] = $_array;
                                    if ( ! isset( $array[ $division ] ) ) {
                                        $array[ $division ] = array();
                                    }
                            } else {
                                $_array = array();
                                foreach ( $trNode->childNodes as $tdNode ) {
                                     if( $tdNode->nodeName === 'td' ) {
                                        $count_td++;
                                        switch ( $count_td ) {
                                            case 1:
                                                $key = 'time';
                                                break;
                                            case 2:
                                                $key = 'status';
                                                break;
                                            case 3:
                                                $key = 't1';
                                                break;
                                            case 4:
                                                $key = 'score';
                                                break;
                                            case 5:
                                                $key = 't2';
                                                break;
                                            default:
                                                $key = $count;
                                                break;
                                        }

                                        $_array[ $key ] = $tdNode->textContent;
                                    }
                                }
                                $_id = md5( $_array['time'] . $_array['t1'] . $_array['t2'] );
                                $_array['id'] = $_id;
                                $array[ $division ][] = $_array;
                            }
                        }
                    }
                }
                
            }
            
        }  
        return $array;
    }

    /**
     * Get Basketball Events    
     * @return array 
     */
    public function get_events_basketball() {
      
        $response = wp_remote_get( 'https://www.scorespro.com/livescore/' );
        
        $output = '';
        ob_start();
            echo wp_remote_retrieve_body( $response );
        $output = ob_get_clean();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);

        $doc->loadHTML( $output );
        $node = $doc->getElementById('mainfeed-basketball'); 

        $scores = $node->getElementsByTagName('td')->item(0);
        $array = array();
        $division = '';
        
        foreach ( $node->childNodes as $childNode ){
            
            if( $childNode->hasAttribute( 'class' ) && false !== strpos( $childNode->getAttribute( 'class' ), 'compgrp' ) ) {
                
                $tableNodes = $childNode->childNodes;
                $count = 0; 
                foreach ( $childNode->childNodes as $tableNode ) {
                    
                    
                    if( ! $tableNode->childNodes ) {
                        continue;
                    }
                    $count++;

                    foreach ( $tableNode->childNodes as $tbody ) {

                        if( ! $tbody->childNodes ) {
                            continue;
                        }
                        
                        $count_tr = 0;
                        $_array = array();
                        foreach ( $tbody->childNodes as $trNode ) {
                            
                            if( ! $trNode->childNodes ) {
                                continue;
                            }

                            $count_tr++;
                            $count_td = 0; 
                            
                            if( $count === 1 ) {
                                    // Only the first table is a division.
                                    $division = htmlentities( $trNode->textContent, null, 'utf-8' );
                                    $division = trim( str_replace( '&nbsp;','', $division ) );
                                    if ( ! isset( $array[ $division ] ) ) {
                                        $array[ $division ] = array();
                                    }
                            } else {
                                 
                                foreach ( $trNode->childNodes as $tdNode ) {
                                     if( $tdNode->nodeName === 'td' ) {
                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'datetime' ) ) {
                                            $key = 'time';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                            
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'status' ) ) {
                                            $key = 'status';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'hometeam' ) ) {
                                            $key = 't1';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'ts_setB' ) ) {
                                            $key = 'score';
                                            if ( ! isset( $_array[ $key ] ) ) {
                                                $_array[ $key ] = trim( $tdNode->textContent ) . ' - ';
                                            } else {
                                                $_array[ $key ] .= trim( $tdNode->textContent );
                                            }
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'awayteam' ) ) {
                                            $key = 't2';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                        }
                                        
                                        $count_td++;
                                    }
                                }
                               
                            }
                        }
                        if ( $_array ) {
                            $_id = md5( $_array['time'] . $_array['t1'] . $_array['t2'] );
                            $_array['id'] = $_id;
                            $array[ $division ][] = $_array;
                        }
                         
                    }
                }
                
            }
            
        }  
         
        return $array;
    }

    /**
     * Get Volleyball Events    
     * @return array 
     */
    public function get_events_volleyball() {
      
        $response = wp_remote_get( 'https://www.scorespro.com/livescore/' );
        
        $output = '';
        ob_start();
            echo wp_remote_retrieve_body( $response );
        $output = ob_get_clean();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);

        $doc->loadHTML( $output );
        $node = $doc->getElementById('mainfeed-volleyball'); 

        $scores = $node->getElementsByTagName('td')->item(0);
        $array = array();
        $division = '';
        
        foreach ( $node->childNodes as $childNode ){
            
            if( $childNode->hasAttribute( 'class' ) && false !== strpos( $childNode->getAttribute( 'class' ), 'compgrp' ) ) {
                
                $tableNodes = $childNode->childNodes;
                $count = 0; 
                foreach ( $childNode->childNodes as $tableNode ) {
                    
                    
                    if( ! $tableNode->childNodes ) {
                        continue;
                    }
                    $count++;

                    foreach ( $tableNode->childNodes as $tbody ) {

                        if( ! $tbody->childNodes ) {
                            continue;
                        }
                        
                        $count_tr = 0;
                        $_array = array();
                        foreach ( $tbody->childNodes as $trNode ) {
                            
                            if( ! $trNode->childNodes ) {
                                continue;
                            }

                            $count_tr++;
                            $count_td = 0; 
                            
                            if( $count === 1 ) {
                                    // Only the first table is a division.
                                    $division = htmlentities( $trNode->textContent, null, 'utf-8' );
                                    $division = trim( str_replace( '&nbsp;','', $division ) );
                                    if ( ! isset( $array[ $division ] ) ) {
                                        $array[ $division ] = array();
                                    }
                            } else {
                                 
                                foreach ( $trNode->childNodes as $tdNode ) {
                                     if( $tdNode->nodeName === 'td' ) {
                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'datetime' ) ) {
                                            $key = 'time';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                            
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'status' ) ) {
                                            $key = 'status';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'hometeam' ) ) {
                                            $key = 't1';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'ts_setB' ) ) {
                                            $key = 'score';
                                            if ( ! isset( $_array[ $key ] ) ) {
                                                $_array[ $key ] = trim( $tdNode->textContent ) . ' - ';
                                            } else {
                                                $_array[ $key ] .= trim( $tdNode->textContent );
                                            }
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'awayteam' ) ) {
                                            $key = 't2';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                        }
                                        
                                        $count_td++;
                                    }
                                }
                               
                            }
                        }
                        if ( $_array ) {
                            $_id = md5( $_array['time'] . $_array['t1'] . $_array['t2'] );
                            $_array['id'] = $_id;
                            $array[ $division ][] = $_array;
                        }
                         
                    }
                }
                
            }
            
        }  
        
        return $array;
    }

    /**
     * Get Hockey Events    
     * @return array 
     */
    public function get_events_hockey() {
      
        $response = wp_remote_get( 'https://www.scorespro.com/livescore/' );
        
        $output = '';
        ob_start();
            echo wp_remote_retrieve_body( $response );
        $output = ob_get_clean();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);

        $doc->loadHTML( $output );
        $node = $doc->getElementById('mainfeed-hockey'); 

        $scores = $node->getElementsByTagName('td')->item(0);
        $array = array();
        $division = '';
        
        foreach ( $node->childNodes as $childNode ){
            
            if( $childNode->hasAttribute( 'class' ) && false !== strpos( $childNode->getAttribute( 'class' ), 'compgrp' ) ) {
                
                $tableNodes = $childNode->childNodes;
                $count = 0; 
                foreach ( $childNode->childNodes as $tableNode ) {
                    
                    
                    if( ! $tableNode->childNodes ) {
                        continue;
                    }
                    $count++;

                    foreach ( $tableNode->childNodes as $tbody ) {

                        if( ! $tbody->childNodes ) {
                            continue;
                        }
                        
                        $count_tr = 0;
                        $_array = array();
                        foreach ( $tbody->childNodes as $trNode ) {
                            
                            if( ! $trNode->childNodes ) {
                                continue;
                            }

                            $count_tr++;
                            $count_td = 0; 
                            
                            if( $count === 1 ) {
                                    // Only the first table is a division.
                                    $division = htmlentities( $trNode->textContent, null, 'utf-8' );
                                    $division = trim( str_replace( '&nbsp;','', $division ) );
                                    if ( ! isset( $array[ $division ] ) ) {
                                        $array[ $division ] = array();
                                    }
                            } else {
                                 
                                foreach ( $trNode->childNodes as $tdNode ) {
                                     if( $tdNode->nodeName === 'td' ) {
                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'datetime' ) ) {
                                            $key = 'time';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                            
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'status' ) ) {
                                            $key = 'status';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'hometeam' ) ) {
                                            $key = 't1';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'ts_setB' ) ) {
                                            $key = 'score';
                                            if ( ! isset( $_array[ $key ] ) ) {
                                                $_array[ $key ] = trim( $tdNode->textContent ) . ' - ';
                                            } else {
                                                $_array[ $key ] .= trim( $tdNode->textContent );
                                            }
                                        }

                                        if ( $tdNode->hasAttribute( 'class' ) && false !== strpos( $tdNode->getAttribute( 'class' ), 'awayteam' ) ) {
                                            $key = 't2';
                                            $_array[ $key ] = trim( $tdNode->textContent );
                                        }
                                        
                                        $count_td++;
                                    }
                                }
                               
                            }
                        }
                        if ( $_array ) {
                            $_id = md5( $_array['time'] . $_array['t1'] . $_array['t2'] );
                            $_array['id'] = $_id;
                            $array[ $division ][] = $_array;
                        }
                         
                    }
                }
                
            }
            
        }  
        
        return $array;
    }

    /**
     * Events Results
     * Used mostly on AJAX
     * @return array 
     */
    public function event_results( $live_results, $list ) {

        // We have some of our lists
        if( false !== strpos( $list, 'scorespro_free') ) {

            switch ( $list ) {
                case 'scorespro_free_soccer':
                    $soccer_events = $this->get_events_soccer();
                    $events = array();
                    foreach ( $soccer_events as $league => $_events ) {

                        foreach ( $_events as $e ) {
                            $status = trim( $e['status'] );

                            if( is_numeric( $status ) ) {
                                $status .= '<span class="minute">\'</span>';
                            }

                            $status = '<span class="lsfs-event-live-status">' . $status . '</span>';
                            $events[ $e['id'] ] = array(
                                'results' => $e['score'],
                                'status'  => $status
                            );
                        }
                         
                    }
                    $live_results = $events;
                    break;
                case 'scorespro_free_basketball':
                    $basketball_events = $this->get_events_basketball();
                    $events = array();
                    foreach ( $basketball_events as $league => $_events ) {

                        foreach ( $_events as $e ) {
                            $status = trim( $e['status'] );

                            if( is_numeric( $status ) ) {
                                $status .= '<span class="minute">\'</span>';
                            }

                            $status = '<span class="lsfs-event-live-status">' . $status . '</span>';
                            $events[ $e['id'] ] = array(
                                'results' => $e['score'],
                                'status'  => $status
                            );
                        }
                        
                    }
                    $live_results = $events;
                    break;
                case 'scorespro_free_volleyball':
                    $volleyball_events = $this->get_events_volleyball();
                    $events = array();
                    foreach ( $volleyball_events as $league => $_events ) {

                        foreach ( $_events as $e ) {
                            $status = trim( $e['status'] );

                            if( is_numeric( $status ) ) {
                                $status .= '<span class="minute">\'</span>';
                            }

                            $status = '<span class="lsfs-event-live-status">' . $status . '</span>';
                            $events[ $e['id'] ] = array(
                                'results' => $e['score'],
                                'status'  => $status
                            );
                        }
                        
                    }
                    $live_results = $events;
                    break;
                case 'scorespro_free_hockey':
                    $hockey_events = $this->get_events_hockey();
                    $events = array();
                    foreach ( $hockey_events as $league => $_events ) {

                        foreach ( $_events as $e ) {
                            $status = trim( $e['status'] );

                            if( is_numeric( $status ) ) {
                                $status .= '<span class="minute">\'</span>';
                            }

                            $status = '<span class="lsfs-event-live-status">' . $status . '</span>';
                            $events[ $e['id'] ] = array(
                                'results' => $e['score'],
                                'status'  => $status
                            );
                        }
                        
                    }
                    $live_results = $events;
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $live_results;
    }
}

add_filter( 'lsfs_integrations', 'lsfs_add_scorespro_free_integration' );

/**
 * Adding Scorespro Free integration
 * @param  array $integrations 
 * @return array               
 */
function lsfs_add_scorespro_free_integration( $integrations ) {
    $integrations['scorespro_free'] = 'LSFS_Scorespro_Free';
    return $integrations;
}