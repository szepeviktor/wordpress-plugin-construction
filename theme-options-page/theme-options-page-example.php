<?php
/*
Snippet Name: Simple Settings API example with common render function
Description: Supports several field types, default values, description line, HTML classes
Version: 0.1.0

Please do *sanizite inputs*! https://codex.wordpress.org/Data_Validation
And Escape output.

HTML classes: regular-text large-text small-text tiny-text code, See: wp-admin/css/forms.css
*/

add_action( 'admin_menu', 'custom_theme_add_admin_menu' );
add_action( 'admin_init', 'custom_theme_settings_init' );

function custom_theme_sanitize_option( $value ) {

    if ( is_array( $value ) ) {
        // Sanitize a URL
        if ( isset( $value['custom_theme_text_field_0'] ) ) {
            $value['custom_theme_text_field_0'] = esc_url_raw( $value['custom_theme_text_field_0'] );
        }
    }

    return $value;
}

function custom_theme_add_admin_menu() {

    $page_slug = 'custom-theme-menu-slug';

    add_theme_page(
        'Page Title HTML/title',
        // WordPress core translates this
        __( 'Theme Options' ),
        'manage_options',
        $page_slug,
        'custom_theme_options_page_callback'
    );
}

function custom_theme_options_page_callback() {

    $page_slug = 'custom-theme-menu-slug';
    $page_title = __( 'Custom Theme Options Page H1', 'custom_theme_textdomain' );

    printf( '<div class="wrap"><form method="post" action="options.php"><h1>%s</h1>', esc_html( $page_title ) );
    settings_fields( $page_slug );
    do_settings_sections( $page_slug );
    submit_button();
    print '</form></div>';
}

function custom_theme_settings_init() {

    $page_slug = 'custom-theme-menu-slug';
    $section = 'custom_theme_page_section';
    $option = 'custom_theme_settings';

    register_setting( $page_slug, $option, 'custom_theme_sanitize_option' );
    add_settings_section(
        $section,
        __( 'Custom Theme Section description H2', 'custom_theme_textdomain' ),
        'custom_theme_settings_section_head_render',
        $page_slug
    );

    add_settings_field(
        'custom_theme_text_field_0',
        __( 'Text label', 'custom_theme_textdomain' ),
        'custom_theme_options_page_field_render',
        $page_slug,
        $section,
        array(
            'label_for' => 'custom_theme_text_field_0',
            'option' => $option,
            'field_id' => 'custom_theme_text_field_0',
            'type' => 'text',
            // 'class' is already in use
            'classes' => 'regular-text code',
            'description' => 'This is a nice descriptive line.',
            'default' => 'Some apples are red.',
        )
    );

    add_settings_field(
        'custom_theme_checkbox_field_1',
        __( 'Checkbox label', 'custom_theme_textdomain' ),
        'custom_theme_options_page_field_render',
        $page_slug,
        $section,
        array(
            'option' => $option,
            'field_id' => 'custom_theme_checkbox_field_1',
            'type' => 'checkbox',
            'checkbox' => array(
                'NameA',
            ),
            'classes' => 'some custom',
        )
    );

    add_settings_field(
        'custom_theme_checkbox_field_1m',
        __( 'Multi checkbox label', 'custom_theme_textdomain' ),
        'custom_theme_options_page_field_render',
        $page_slug,
        $section,
        array(
            'option' => $option,
            'field_id' => 'custom_theme_checkbox_field_1m',
            'type' => 'multi_checkbox',
            // Array keys will be 0, 1, 2 ...
            'checkbox' => array(
                'Name1',
                'Name2',
                'Name3',
            ),
            'description' => 'This is a nice descriptive line.',
            // Default array example
            'default' => array( '1', '0', '1' ),
        )
    );

    add_settings_field(
        'custom_theme_radio_field_2',
        __( 'Radio label', 'custom_theme_textdomain' ),
        'custom_theme_options_page_field_render',
        $page_slug,
        $section,
        array(
            'option' => $option,
            'field_id' => 'custom_theme_radio_field_2',
            'type' => 'radio',
            'radio' => array(
                'key_1' => 'Name4',
                'key_2' => 'Name5',
                'key_3' => 'Name6',
            ),
        )
    );

    add_settings_field(
        'custom_theme_select_field_3',
        __( 'Select label', 'custom_theme_textdomain' ),
        'custom_theme_options_page_field_render',
        $page_slug,
        $section,
        array(
            'label_for' => 'custom_theme_select_field_3',
            'option' => $option,
            'field_id' => 'custom_theme_select_field_3',
            'type' => 'select',
            'select' => array(
                'key_4' => 'Name4',
                'key_5' => 'Name5',
                'key_6' => 'Name6',
            ),
        )
    );

    add_settings_field(
        'custom_theme_textarea_field_4',
        __( 'Textarea label', 'custom_theme_textdomain' ),
        'custom_theme_options_page_field_render',
        $page_slug,
        $section,
        array(
            'label_for' => 'custom_theme_textarea_field_4',
            'option' => $option,
            'field_id' => 'custom_theme_textarea_field_4',
            'type' => 'textarea',
            'cols' => 40,
            'rows' => 5,
            'class' => 'large-text',
        )
    );
}

function custom_theme_settings_section_head_render() {

    echo '<p>' . __( 'Custom Theme Section head p', 'custom_theme_textdomain' ) . '</p>';
}

function custom_theme_options_page_field_render( $args ) {

    if ( empty( $args['option'] )
        || empty( $args['field_id'] )
        || empty( $args['type'] )
    ) {
        print '<h1 style="color: red !important;">Invalid function usage</h1>';
        return;
    }

    $options = get_option( $args['option'] );
    if ( isset( $options[ $args['field_id'] ] ) ) {
        $option = $options[ $args['field_id'] ];
    } elseif ( isset( $args['default'] ) ) {
        // Default value
        $option = $args['default'];
    } else {
        $option = '';
    }
    // HTML classes
    $attrs = isset( $args['classes'] ) ? sprintf( ' class="%s"', $args['classes'] ) : '';

    switch ( $args['type'] ) {
        case 'text':
            printf(
                '<input id="%s" type="text" name="%s[%s]" value="%s"%s>',
                $args['field_id'],
                $args['option'],
                $args['field_id'],
                esc_attr( $option ),
                $attrs
            );
            break;
        case 'textarea':
            $attrs .= isset( $args['rows'] ) ? sprintf( ' rows="%s"', $args['rows'] ) : '';
            $attrs .= isset( $args['cols'] ) ? sprintf( ' cols="%s"', $args['cols'] ) : '';
            printf(
                '<textarea id="%s" type="text" name="%s[%s]"%s>%s</textarea>',
                $args['field_id'],
                $args['option'],
                $args['field_id'],
                $attrs,
                esc_html( $option )
            );
            break;
        case 'checkbox':
            printf( '<label><input id="%s" type="checkbox" name="%s[%s]" %s value="1"%s />
                %s</label>',
                $args['field_id'],
                $args['option'],
                $args['field_id'],
                checked( $option, 1, false ),
                $attrs,
                esc_html( $args['checkbox'][0] )
            );
            break;
        case 'multi_checkbox':
            print '<fieldset>';
            foreach ( $args['checkbox'] as $index => $checkbox ) {
                $this_value = ( is_array( $option ) && isset( $option[ $index ] ) ) ? $option[ $index ] : '0';
                printf( '<label><input id="%s_%s" type="checkbox" name="%s[%s][%s]" %s value="1"%s />
                        %s</label><br />',
                    $args['field_id'],
                    $index,
                    $args['option'],
                    $args['field_id'],
                    $index,
                    checked( $this_value, 1, false ),
                    $attrs,
                    esc_html( $checkbox )
                );
            }
            print '</fieldset>';
            break;
        case 'radio':
            print '<fieldset>';
            foreach ( $args['radio'] as $index => $radio ) {
                printf( '<label><input id="%s_%s" type="radio" name="%s[%s]" %s value="%s"%s />
                        %s</label><br />',
                    $args['field_id'],
                    $index,
                    $args['option'],
                    $args['field_id'],
                    checked( $option, $index, false ),
                    $index,
                    $attrs,
                    esc_html( $radio )
                );
            }
            print '</fieldset>';
            break;
        case 'select':
            printf( '<select id="%s" name="%s[%s]"%s>',
                $args['field_id'],
                $args['option'],
                $args['field_id'],
                $attrs
            );
            foreach ( $args['select'] as $index => $select ) {
                printf( '<option value="%s" %s />%s</option>',
                    $index,
                    selected( $option, $index, false ),
                    esc_attr( $select )
                );
            }
            print '</select>';
            break;
        default:
            print '<h1 style="color: red !important;">Invalid field type</h1>';
    }

    if ( isset( $args['description'] ) ) {
        printf( '<p class="description" id="%s-description">%s</p>', $args['field_id'], $args['description'] );
    }
}
