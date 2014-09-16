<?php

// Toggles descriptions on all admin pages.

class Hide_Descriptions {

    private $hide;

    public function __construct() {

        // add the checkbox to "Screen Options"
        add_filter( 'screen_settings', array( $this, 'checkbox' ), 10, 2 );
        // hide descriptions by CSS
        add_action( 'admin_print_styles', array( $this, 'style' ) );
        // update option on checkbox state change
        add_action( 'wp_ajax_o1_toggle_descriptions', array( $this, 'ajax_receiver' ) );
    }

    public function checkbox( $settings, $obj ) {

        $nonce = wp_create_nonce( 'o1_disable_description' );

        //FIXME script needs to go to its place
        $hide_checkbox = sprintf( '<div class="screen-options-hide"><label for="o1-disable-description"><input
            id="o1-disable-description" name="o1-disable-description" type="checkbox" %s />%s</label><script>
            jQuery(function ($) {
                var disableDesc = $("#o1-disable-description");
                disableDesc.click(function () {
                    $("#wpbody .description").slideToggle();
                    disableDesc.prop("disabled", true);
                    $.post(ajaxurl, {
                            action: "o1_toggle_descriptions",
                            nonce: "%s",
                            state: disableDesc.prop("checked")
                        },
                        function () {
                            disableDesc.prop("disabled", false);
                        }
                    );
                });
            });
            </script></div>',
            checked( $this->hide, true, false ),
            __( 'Hide all descriptions', 'hidedescriptions' ),
            $nonce
        );

        return $hide_checkbox . $settings;
    }

    public function style() {

        // it is enough to set it here (at admin_print_styles)
        $this->hide = get_option( 'hide_descriptions' );
        if ( $this->hide )
            printf( '<style type="text/css">#wpbody .description { display:none; }</style>' );
    }

    public function ajax_receiver() {

        check_ajax_referer( 'o1_disable_description', 'nonce' );

        $hide = ( 'true' === $_POST['state'] );
        update_option( 'hide_descriptions', $hide );
        // something to respond
        wp_die( json_encode( $hide ) );
    }
}

new Hide_Descriptions();
