<?php
/*
Plugin Name: Custom settings for Simple History (MU)
Version: 1.0.0
Description: Hide sidebar on admin page.
Author: Viktor Szépe
License: GNU General Public License (GPL) version 2
*/

final class O1_simple_history_custom {

    public function __construct() {

        add_action( 'admin_enqueue_scripts', array( $this, 'hide_sidebar' ), 20 );
    }

    /**
     * Hide sidebar and remove GUI wrapper's margin
     */
    public function hide_sidebar( $hook ) {

        if ( 'dashboard_page_simple_history_page' !== $hook ) {
            return;
        }

        $style = '.dashboard_page_simple_history_page .SimpleHistoryGuiWrap{margin-right:0 !important;}
                  .dashboard_page_simple_history_page .SimpleHistory__pageSidebar{display:none !important;}';
        wp_add_inline_style( 'wp-admin', $style );
    }
}

new O1_simple_history_custom();
