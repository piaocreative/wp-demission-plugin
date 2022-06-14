<?php

/**
 * The code in this file runs when a plugin is uninstalled from the WordPress dashboard.
 */

/* If uninstall is not called from WordPress, exit. */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) { 
    exit(); 
}

/* Uninstall code */
function demis_drop_db_tables( $table_names = array() ) {
    global $wpdb;
    foreach ( $table_names as $tn ) {
        $sql = 'DROP TABLE IF EXISTS  ' . $wpdb->prefix . $tn;
        $wpdb->query( $sql );
    }
}

demis_drop_db_tables( array( 'demis_entries' ) );
