<?php
/*
Plugin Name: Machine Language
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Toggles human and machine language (aka IDs) on admin pages.
Version: 0.3
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
Text Domain: machinelanguage
*/

class Machine_Language {

    private $option = 'machine_language';
    private $nonce = 'machine_language';
    private $machine;
    private $js_template = '<script type="text/javascript" id="machine-lang">
        jQuery(function ($) {
            var postdata,
                disableDesc = $("#machine-language"),
                spinner = disableDesc.siblings(".spinner")
                    .css("float", "right")
                    .css("margin-top", "5px"),
                machineLang = %s,
                noContent = "\u2205";

            function translate(machine) {
                $("label[for]:not(:has(*))").each(function (i,e) {
                    if (machine) {
                        $(e).prop("data-machine-lang", $(e).html());
                        $(e).html($(e).prop("for"));
                    } else {
                        $(e).html($(e).prop("data-machine-lang"));
                    }
                });
                $("label[for]:has(input)").each(function (i,e) {
                    var elem = $(e).contents()
                        .filter(function () {
                            return this.nodeType == 3 && $.trim(this.nodeValue) != "";
                        })
                        .first();
                    if (!elem.length) return;
                    if (machine) {
                        $(e).prop("data-machine-lang", elem[0].textContent);
                        elem[0].textContent = $(e).prop("for");
                    } else {
                        elem[0].textContent = $(e).prop("data-machine-lang");
                    }
                });
                $("label:not([for]):has(input[type=radio])").each(function (i,e) {
                    var value, name,
                        elem = $(e).contents()
                        .filter(function () {
                            return this.nodeType == 3 && $.trim(this.nodeValue) != "";
                        })
                        .first();
                    if (!elem.length) {console.info("bailout");return;}
                    if (machine) {
                        $(e).prop("data-machine-lang", elem[0].textContent);
                        name = $(e).find("input[type=radio]").prop("name");
                        value = $(e).find("input[type=radio]").prop("value");
                        if ($.trim(value) == "") value = noContent;
                        elem[0].textContent = name + "|" + value;
                    } else {
                        console.error( $(e).prop("data-machine-lang") );
                        elem[0].textContent = $(e).prop("data-machine-lang");
                    }
                });
                $("label:not([for]):has(input[type=checkbox])").each(function (i,e) {
                    var elem = $(e).contents()
                        .filter(function () {
                            return this.nodeType == 3 && $.trim(this.nodeValue) != "";
                        })
                        .first();
                    if (!elem.length) return;
                    if (machine) {
                        $(e).prop("data-machine-lang", elem[0].textContent);
                        value = $(e).find("input[type=radio]").prop("value");
                        if ($.trim(value) == "") value = noContent;
                        elem[0].textContent = value;
                    } else {
                        elem[0].textContent = $(e).prop("data-machine-lang");
                    }
                });
                $("select:has(option)").each(function (i,e) {
                    var title = [],
                        options = $(e).find("option");

                    if (machine) {
                        options.each(function (n,o) {
                            var value = $(o).val();
                            title.push(value);
                            $(o).prop("data-machine-lang", $(o).text());
                            $(o).text($(o).text() + "|" + value);
                        });
                        $(e).prop("data-machine-lang", $(e).prop("title"));
                        $(e).prop("title", title.join(", "));
                    } else {
                        options.each(function (n,o) {
                            $(o).text($(o).prop("data-machine-lang"));
                        });
                        $(e).prop("title", $(e).prop("data-machine-lang"));
                    }
                });
            }

            function onClick() {
                var state;

                disableDesc.prop("disabled", true);
                spinner.css("display", "block");
                state = disableDesc.prop("checked");
                $("#wpbody p.description,#wpbody span.description").slideToggle();
                translate(state);
                postdata = {
                    action: "o1_toggle_descriptions",
                    _nonce: "%s",
                    state: state
                };
                $.post(
                    ajaxurl,
                    postdata,
                    function (result) {
                        if (result=="1") {
                            disableDesc.prop("disabled", false);
                            spinner.css("display", "none");
                        }
                    }
                );
            };

            if (machineLang) translate(true);
            disableDesc.click(onClick);
        });
</script>
    ';

    public function __construct() {

        // add the checkbox to "Screen Options"
        add_filter( 'screen_settings', array( $this, 'checkbox' ), 99, 2 );
        // hide descriptions by CSS
        add_action( 'admin_print_styles', array( $this, 'style' ) );
        // Javascript for the checkbox
        add_action( 'admin_head', array( $this, 'script' ) );
        // update user option on checkbox state change
        add_action( 'wp_ajax_o1_toggle_descriptions', array( $this, 'ajax_receiver' ) );
    }

    public function checkbox( $settings, $obj ) {

        $machine_language_checkbox = sprintf( '<div class="screen-options-machine-lang">
            <label for="machine-language" style="font-style: italic;"><input id="machine-language"
            name="machine-language" type="checkbox"%s />%s<span class="spinner"></span>
            </label></div>',
            checked( $this->machine, true, false ),
            __( 'Machine language', 'machinelanguage' )
        );

        return $settings . $machine_language_checkbox;
    }

    public function style() {

        // early enough to set it here (@admin_print_styles)
        $this->machine = get_user_option( $this->option, get_current_user_id() );

        if ( $this->machine )
            printf( '<style type="text/css">#wpbody p.description,#wpbody span.description {display:none;}</style>' );
    }

    public function script() {

        $nonce = wp_create_nonce( $this->nonce );

        printf( $this->js_template,
            $this->machine ? 'true' :  'false',
            $nonce
        );
    }

    public function ajax_receiver() {

        check_ajax_referer( $this->nonce, '_nonce' );

        $machine = ( 'true' === $_POST['state'] );
        update_user_option( get_current_user_id(), $this->option, $machine, true );
        wp_die( 1 );
    }
}

new Machine_Language();