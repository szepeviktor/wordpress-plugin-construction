<?php
/**
 * One theme option page for themes
 *
 * Common render function, supports several field types, default values, description line, HTML classes
 * Please do *Sanizite input* and Escape output!
 *
 * @version 0.2.0
 * @link https://codex.wordpress.org/Data_Validation
 */

/**
 * One option page for themes
 */
class One_Theme_Options_Page {

    protected $page_slug = 'one-theme-menu-slug';
    protected $html_title = 'One theme options page';
    private $tabs = array();
    private $sections = array();

    public function __construct() {

        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
    }

    /**
     * Register one page
     */
    public function add_admin_menu() {

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
     * Register tabs
     */
    protected function add_settings_tab( $slug, $html_title, $title = '' ) {

// 1 page / tabs `&tab=` / sections / fields
// produces one option array per tab ?per section
// add_settings_field(page slug, section id, field id, type, sanitize, label, option, optional:args(elements,class/th,classes,default,description,rows,cols ...))
// $allowed_HTML_args = array('classes', 'row','cols','autofocus'...)
// output example as a shortcode [otop-example]: trim, esc_*

        if ( empty( $title ) ) {
            $tab_title = $html_title;
        } else {
            $tab_title = $title;
        }

        $this->tabs[ $slug ] = array(
            'title' => $tab_title,
            'html_title' => $html_title,
        );

        // @FIXME It is NOT complete!
        // To be removed
        $this->page_slug = $slug;
        $this->html_title = $html_title;
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
            // args(label_for,elements,class/th,classes,default,description,rows,cols ...)
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
     * Display option page, one tab of it
     */
    public function page_render() {

        printf( '<div class="wrap"><form method="post" action="options.php"><h1>%s</h1>',
            esc_html( $this->html_title )
        );
        /*
        if ( is_array( $this->tabs ) && count( $this->tabs ) > 1 ) {
            $this->nav_tab_render();
        }
        */
        settings_fields( $this->page_slug );
        do_settings_sections( $this->page_slug );
// TODO Reset button
        submit_button();
        print '</form></div>';
    }

    /**
     * Display navtab
     */
    public function nav_tab_render() {
//$this->tabs = array( 'custom-theme-menu-slug' => 'Tab One', 'custom-theme-menu-slug2' => 'Tab Two' );
//$this->current_tab = 'custom-theme-menu-slug';

        print '<h2 class="nav-tab-wrapper">';
        foreach ( $tabs as $slug => $title ) {
            $active = ( $current_tab === $slug ) ? ' nav-tab-active' : '';
            printf( '<a class="nav-tab%s" href="?page=%s">%s</a>',
                $active,
                $slug,
                esc_html( $title )
            );
        }
        print '</h2>';
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
     * Display any field
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
        // HTML classes: regular-text large-text small-text tiny-text code
        // See: wp-admin/css/forms.css
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
            // TODO New types: post_select with Query, Media, URL(html5), Media+link, loop[] field
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
}
