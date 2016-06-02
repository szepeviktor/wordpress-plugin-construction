<?php
/**
 * One theme option page for themes
 *
 * Common render and sanitization functions, supports several field types, default values, description, HTML classes
 * Please properly *Sanizite input* and *Escape output*!
 *
 * @version 0.2.0
 * @link https://codex.wordpress.org/Data_Validation
 */

/**
 * One option page for themes
 */
class One_Theme_Options_Page {

    private $page_slug = '';
    private $page_title = '';
    private $html_title = '';
    private $sections = array();
    private $allowed_html_attrs = array(
        'style',
        'id',
        'title',
        'disabled',
        'readonly',
        'required',
        'placeholder',
        'tabindex',
        'autofocus',
        'onclick',
        'onfocus',
        'size',
        'row',
        'cols',
    );

    /**
     * Register one page
     */
    protected function add_admin_menu( $slug, $title, $html_title = '' ) {

        if ( empty( $html_title ) ) {
            $html_title = $title;
        }

        $this->page_slug = $slug;
        $this->page_title = $title;
        $this->html_title = $html_title;

        // WordPress core translates 'Theme Options'
        add_theme_page(
            $this->html_title,
            __( 'Theme Options' ),
            'manage_options',
            $this->page_slug,
            array( $this, 'page_render' )
        );
    }

    /**
     * Register options
     */
    protected function register_option( $option ) {

        register_setting( $this->page_slug, $option, array( $this, 'sanitize_option' ) );
    }

    /**
     * Register sections
     */
    protected function add_settings_section( $id, $title, $description = '' ) {

        $this->sections[ $id ] = array(
            'title' => $title,
            'description' => $description,
        );

        add_settings_section(
            $id,
            $title,
            array( $this, 'settings_section_head_render' ),
            $this->page_slug
        );
    }

    /**
     * Register fields
     */
    protected function add_settings_field( $section, $id, $type, $sanitize, $label, $option, $args = array() ) {

        $this->sections[ $section ]['fields'][ $id ] = array(
            'type' => $type,
            'sanitize' => $sanitize,
            'label' => $label,
            'option' => $option,
            'args' => $args,
        );

        $extra_args = array(
            'option' => $option,
            'field_id' => $id,
            'type' => $type,
        );
        if ( array_key_exists( 'label_for', $args ) ) {
            $extra_args['label_for'] = $id;
        }
        if ( array_key_exists( 'required', $args ) ) {
            if ( array_key_exists( 'class', $args ) ) {
                $args['class'] .= ' required';
            } else {
                $extra_args['class'] = 'required';
            }
        }
        add_settings_field(
            $id,
            $label,
            array( $this, 'field_render' ),
            $this->page_slug,
            $section,
            array_merge( $args, $extra_args )
        );
    }

    /**
     * Display option page
     */
    public function page_render() {

        print '<div class="wrap">';
        do_action( 'otop_before_form' );

        printf( '<form method="post" action="options.php"><h1>%s</h1>',
            esc_html( $this->page_title )
        );
        settings_fields( $this->page_slug );
        do_settings_sections( $this->page_slug );
        // TODO Reset button
        submit_button( __( 'Save Settings' ), 'primary', 'submit', true, array( 'id' => 'one-theme-submit' ) );
        print '</form>';

        do_action( 'otop_after_form' );
        print '</div>';
    }

    /**
     * Display section description
     */
    public function settings_section_head_render( $section_args ) {

        $description = $this->sections[ $section_args['id'] ]['description'];

        if ( ! empty( $description ) ) {
            printf( '<p>%s</p>', esc_html( $description ) );
        }
    }

    /**
     * Display field
     */
    public function field_render( $args ) {

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

        // HTML class and attributes
        $attrs = isset( $args['classes'] ) ? sprintf( ' class="%s"', $args['classes'] ) : '';
        foreach ( $args as $attr => $value ) {
            if ( in_array( $attr, $this->allowed_html_attrs ) ) {
                $attrs .= sprintf( ' %s', esc_attr( $attr ) );
                if ( true !== $value ) {
                    $attrs .= sprintf( '="%s"', esc_attr( $value ) );
                }
            }
        }

        switch ( $args['type'] ) {
            case 'text':
                printf(
                    '<input id="%s" type="text" name="%s[%s]" value="%s"%s />',
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
                    esc_html( $args['elements'][0] )
                );
                break;
            case 'multi_checkbox':
                print '<fieldset>';
                foreach ( $args['elements'] as $index => $checkbox ) {
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
                foreach ( $args['elements'] as $index => $radio ) {
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
                foreach ( $args['elements'] as $index => $select ) {
                    printf( '<option value="%s" %s />%s</option>',
                        $index,
                        selected( $option, $index, false ),
                        esc_attr( $select )
                    );
                }
                print '</select>';
                break;
            case 'multiselect':
                printf( '<select multiple id="%s" name="%s[%s][]"%s>',
                    $args['field_id'],
                    $args['option'],
                    $args['field_id'],
                    $attrs
                );
                foreach ( $args['elements'] as $index => $select ) {
                    printf( '<option value="%s"%s />%s</option>',
                        $index,
                        ( is_array( $option ) && in_array( $index, $option ) ) ? ' selected="selected"' : '',
                        esc_html( $select )
                    );
                }
                print '</select>';
                break;
                // TODO New types
                // -nothing-selected- select option, static html, number, email, password
                // Google Analytics, YouTube, Vimeo, Wistia, LatLong/maps, Google|font|names
                // date, time, date-time, timestamp, css color/hex,rbg,rgba,name, css size/px,em...
                // post_select with Query, tax, user, Media(id,title,alt,desc+preview)
                // Gallery, wp-link-input, URL(html5), Media+URL, loop[] field
                // https://codex.wordpress.org/Javascript_Reference/wp.media
            default:
                $action = 'otop_render_field_type_' . $args['type'];
                if ( has_action( $action ) ) {
                    /**
                     * Custom field type
                     */
                    do_action( $action, $args, $attrs );
                } else {
                    print '<h1 style="color: red !important;">Invalid field type</h1>';
                }
        }

        if ( isset( $args['description'] ) ) {
            printf( '<p class="description" id="%s-description">%s</p>', $args['field_id'], $args['description'] );
        }
    }

    /**
     * Sanitize input
     */
    public function sanitize_option( $value ) {

        if ( is_array( $value ) ) {
            // $value does not contain section ID :(
            foreach ( $this->sections as $section ) {
                foreach ( $section['fields'] as $field_id => $field_data ) {
                    if ( ! array_key_exists( $field_id, $value ) ) {
                        // Could be an unset checkbox or radio button
                        continue;
                    }
                    if ( ! array_key_exists( 'sanitize', $field_data ) ) {
                        error_log( sprintf( 'Field definition error (%s)', $field_id ) );
                        continue;
                    }
                    if ( array_key_exists( 'required', $field_data['args'] ) && empty( $value[ $field_id ] ) ) {
                        // User is cheating: empty value for required field
                        wp_die(
                            '<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
                            '<p>' . __( 'You are not allowed to delete this item.' ) . '</p>',
                            403
                        );
                    }
                    switch ( $field_data['sanitize'] ) {
                        case 'fullhtml':
                            // Any content is allowed
                            break;
                        case 'htmltext':
                            // No HTML tags, only entities
                            $value[ $field_id ] = wp_strip_all_tags( $value[ $field_id ] );
                            break;
                        case 'url':
                            // URL
                            $value[ $field_id ] = esc_url_raw( $value[ $field_id ] );
                            break;
                        case 'one':
                            // '1' only for checkbox
                            // Loose comparison
                            if ( 1 != $value[ $field_id ] ) {
                                unset( $value[ $field_id ] );
                            }
                            break;
                        case 'arrayone':
                            // array( '1', '1' ) only for checkboxes
                            if ( ! is_array( $value[ $field_id ] ) ) {
                                $value[ $field_id ] = array( $value[ $field_id ] );
                            }
                            foreach ( $value[ $field_id ] as $index => $one ) {
                                // Loose comparison
                                if ( 1 != $one
                                    || ! array_key_exists( $index, $field_data['args']['elements'] )
                                ) {
                                    unset( $value[ $field_id ][ $index ] );
                                }
                            }
                            break;
                        case 'elements':
                            // Only 'elements' indices
                            if ( ! array_key_exists( $value[ $field_id ], $field_data['args']['elements'] ) ) {
                                $value[ $field_id ] = '';
                            }
                            break;
                        case 'arrayelements':
                            // Only an aray of 'elements' indices
                            if ( ! is_array( $value[ $field_id ] ) ) {
                                $value[ $field_id ] = array( $value[ $field_id ] );
                            }
                            foreach ( $value[ $field_id ] as $index => $element ) {
                                if ( ! array_key_exists( $element, $field_data['args']['elements'] ) ) {
                                    unset( $value[ $field_id ][ $index ] );
                                }
                            }
                            break;
                        default:
                            error_log( sprintf( 'Invalid sanitization type (%s).', $field_data['sanitize'] ) );
                            $value[ $field_id ] = '';
                    }
                }
            }

            /**
             * Custom sanitization
             */
            $value = apply_filters( 'otop_sanitize_option', $value );
        }

        return $value;
    }

    public function inline_style() {

        // Asterisk for required fields
        $style = '.wrap tr.required label:after { content: "*"; color: crimson; vertical-align: top; margin-left: 2px; }';
        $style = apply_filters( 'otop_inline_style', $style );

        wp_add_inline_style( 'wp-admin', $style );
    }
}
