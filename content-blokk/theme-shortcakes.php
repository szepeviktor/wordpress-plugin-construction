<?php

add_action( 'init', 'dupplex_register_shortcodes' );
add_action( 'register_shortcode_ui', 'dupplex_gomb_shortcode_ui' );
add_action( 'register_shortcode_ui', 'dupplex_doboz_shortcode_ui' );

function dupplex_register_shortcodes() {

    add_shortcode( 'gomb', 'dupplex_gomb' );
    add_shortcode( 'doboz', 'dupplex_doboz' );
}

function dupplex_gomb( $attr, $content, $shortcode_tag ) {

    $attr = shortcode_atts( array(
        'post_id'    => 0,
        'buttontype' => 0,
        'target'     => 'false',
    ), $attr, $shortcode_tag );

    // No output without content
    if ( empty( $attr['post_id'] ) ) {
        return '';
    }

    // Set button type
    switch ( $attr['buttontype'] ) {
        case '1':
            $classes = 'arrow arrow-left';
            break;
        case '2':
            $classes = 'arrow arrow-right';
            break;
        case '0':
        default:
            $classes = 'btn btn-more';
            break;
    }

    // Set link
    $post = get_post( $attr['post_id'] );
    if ( $post instanceof WP_Post ) {
        $permalink = get_permalink( $post );
    } else {
        $permalink = '#';
    }

    $tag = sprintf( '<a href="%s" class="%s"%s>%s</a>',
        esc_attr( $permalink ),
        esc_attr( $classes ),
        ( 'true' === $attr['target'] ) ? ' target="_blank"' : '',
        esc_html( $content )
    );

    return $tag;
}

function dupplex_gomb_shortcode_ui() {

    $fields = array(
        array(
            'label'       => esc_html__( 'Gomb hivatkozása', 'dupplex' ),
            'description' => esc_html__( 'A cél Oldal megadása.', 'dupplex' ),
            'attr'        => 'post_id',
            'type'        => 'post_select',
            'query'       => array( 'post_type' => 'page' ),
            'meta'        => array(
                'required' => true,
            ),
        ),
        array(
            'label'       => esc_html__( 'Gomb típus', 'dupplex' ),
            'description' => esc_html__( 'Válassza ki a gomb típusát.', 'dupplex' ),
            'attr'        => 'buttontype',
            'type'        => 'radio',
            'options'     => array(
                '0'  => 'Akció gomb',
                '1'  => 'Balra nyíl',
                '2'  => 'Jobbra nyíl',
            ),
        ),
        array(
            'label'       => esc_html__( 'Megnyitása új ablakban', 'dupplex' ),
            'description' => esc_html__( 'A gombra kattintáskor új ablak nyílik.', 'dupplex' ),
            'attr'        => 'target',
            'type'        => 'checkbox',
        ),
    );

    $shortcode_ui_args = array(
        'label'         => esc_html__( 'Gomb', 'dupplex' ),
        'listItemImage' => 'dashicons-admin-links',
        'inner_content' => array(
            'label'        => esc_html__( 'Gomb felirat', 'dupplex' ),
            'description'  => esc_html__( 'Ez lesz a gombon olvasható.', 'dupplex' ),
            'value'        => __( 'Bővebben', 'dupplex' ),
        ),
        'post_type'     => array( 'page', 'blokk' ),
        'attrs'         => $fields,
    );

    shortcode_ui_register_for_shortcode( 'gomb', $shortcode_ui_args );
}

function dupplex_doboz( $attr, $content, $shortcode_tag ) {

    $attr = shortcode_atts( array(
        'post_id' => 0,
        'title'   => '',
        'h1'      => 'false',
        'columns' => 'col-md-4',
        'bgcolor' => '',
    ), $attr, $shortcode_tag );
    $post_content = '';

    // No output without content
    if ( empty( $attr['post_id'] ) ) {
        return '';
    }

    // Set header type (H1/H2)
    if ( 'true' === $attr['h1'] ) {
        $header = 'h1';
    } else {
        $header = 'h2';
    }

    // Set title
    if ( empty( $attr['title'] ) ) {
        $title_html = '';
    } else {
        $title_html = sprintf( '<%s>%s</%1$s>', $header, esc_html( $attr['title'] ) );
    }

    // Set content
    $post = get_post( $attr['post_id'] );
    if ( $post instanceof WP_Post ) {
        // FIXME Raw content for images = "doboz" without title
        $post_content = ( '' === $title_html ) ? $post->post_content : apply_filters( 'the_content', $post->post_content );
    } elseif( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        $post_content = '<span style="color: red;">WARNING: Invalid blokk ID/slug!</span>';
    }

    // Background color wrapper
    if ( empty( $attr['bgcolor'] ) ) {
        $bgcolor_html_open = '';
        $bgcolor_html_close = '';
    } else {
        $bgcolor_html_open = sprintf( '<div class="%s">', esc_attr( $attr['bgcolor'] ) );
        $bgcolor_html_close = '</div>';
    }

    $html = sprintf( '<div class="%s">%s%s%s%s</div>',
        esc_attr( $attr['columns'] ),
        $bgcolor_html_open,
        $title_html,
        $post_content,
        $bgcolor_html_close
    );

    return $html;
}

function dupplex_doboz_shortcode_ui() {

    $fields = array(
        array(
            'label'       => esc_html__( 'Doboz cím', 'dupplex' ),
            'description' => esc_html__( 'A NAGYBETŰS címet csak nagy Kezdőbetűvel írja be.', 'dupplex' ),
            'attr'        => 'title',
            'type'        => 'text',
            'meta'        => array(
                'placeholder' => 'Nagybetűs doboz cím',
                'required'    => true,
            ),
        ),
        array(
            'label'       => esc_html__( 'Főcím', 'dupplex' ),
            'description' => esc_html__( 'Az első doboznál állítson be főcímet.', 'dupplex' ),
            'attr'        => 'h1',
            'type'        => 'checkbox',
        ),
        array(
            'label'       => esc_html__( 'Tartalmi blokk', 'dupplex' ),
            'description' => esc_html__( 'Válassza ki melyik tartalom jelenjen meg a dobozban.', 'dupplex' ),
            'attr'        => 'post_id',
            'type'        => 'post_select',
            'query'       => array( 'post_type' => 'blokk' ),
        ),
        array(
            'label'       => esc_html__( 'Doboz szélesség', 'dupplex' ),
            'description' => esc_html__( 'A főoldalon például 8 és 4 széles dobozok vannak.', 'dupplex' ),
            'attr'        => 'columns',
            'type'        => 'radio',
            'options'     => array(
                'col-md-3'  => '3 tizenketted széles',
                'col-md-4'  => '4 tizenketted széles',
                'col-md-6'  => '6 tizenketted széles',
                'col-md-8'  => '8 tizenketted széles',
                'col-md-12' => '12 tizenketted széles',
            ),
        ),
        array(
            'label'       => esc_html__( 'Háttérszín', 'dupplex' ),
            'description' => esc_html__( 'Válassza ki a doboz hátterszínét.', 'dupplex' ),
            'attr'        => 'bgcolor',
            'type'        => 'radio',
            'options'     => array(
                ''         => esc_html__( 'Átlátszó (képhez)', 'dupplex' ),
                'blue-bg'  => esc_html__( 'Sötétkék', 'dupplex' ),
                'green-bg' => esc_html__( 'Türkiz', 'dupplex' ),
                'white-bg' => esc_html__( 'Fehér', 'dupplex' ),
            ),
        ),
    );

    $shortcode_ui_args = array(
        'label'         => esc_html__( 'Doboz', 'dupplex' ),
        'listItemImage' => 'dashicons-editor-table',
        'post_type'     => array( 'page' ),
        'attrs'         => $fields,
    );

    shortcode_ui_register_for_shortcode( 'doboz', $shortcode_ui_args );
}
