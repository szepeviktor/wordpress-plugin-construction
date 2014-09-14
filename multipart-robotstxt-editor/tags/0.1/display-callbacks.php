<?php

function vs_html_attrs( $attributes = array() ) {

    foreach ( $attributes as $attribute => &$data ) {
        if ( empty( $data ) || true === $data ) {
            // empty attributes
            $data = esc_attr( $attribute );
        } else {
            $data = implode( ' ', (array) $data );
            $data = $attribute . '="' . esc_attr( $data ) . '"';
        }
    }

    return $attributes ? ' ' . implode( ' ', $attributes ) : '';
}

function vs_description_display( $description = '' ) {

	printf( '<p class="description">%s</p>',
		wp_kses_post( $description )
	);
}

function vs_display_text_field( $value, $setting, $args ) {

	// custom attributes
	if ( empty( $args['attributes'] ) ) {
	    $attribute_string = '';
	} else {
		$attribute_string = vs_html_attrs( (array) $args['attributes'] );
	}

	printf( '<input name="%s" id="%s" value="%s" class="regular-text" type="text" %s/>',
		esc_attr( $setting->get_field_name() ),
		esc_attr( $setting->get_field_id() ),
		esc_attr( $value ),
		esc_attr( $attribute_string )
	);

	// description
	if ( ! empty( $args['description'] ) )
		vs_description_display( $args['description'] );
}

function vs_display_dropdown( $value, $setting, $args ) {

	if( ! isset( $args['options'] ) )
		return;

	// custom attributes
	if ( empty( $args['attributes'] ) ) {
	    $attribute_string = '';
	} else {
		$attribute_string = vs_html_attrs( (array) $args['attributes'] );
	}

	// options
	$options = '';
	foreach( $args['options'] as $option_value => $option_text ) //TODO disabled, groups
		$options .= sprintf( '<option value="%s"%s>%s</option>',
			esc_attr( $option_value ),
			selected( $option_value, $value, false ),
			$option_text
		);

	printf( '<select id="%s" name="%s"%s>%s</select>',
		esc_attr( $setting->get_field_id() ),
		esc_attr( $setting->get_field_name() ),
		esc_attr( $attribute_string ),
		$options
	);

	// description
	if ( ! empty( $args['description'] ) )
		vs_description_display( $args['description'] );
}

function vs_display_textarea( $value, $setting, $args ) {

	// custom attributes
	if ( empty( $args['attributes'] ) ) {
		$attribute_string = '';
	} else {
		$attribute_string = vs_html_attrs( (array) $args['attributes'] );
	}

	printf( '<textarea name="%s" id="%s" rows="8" cols="80" class="large-text" type="textarea"%s>%s</textarea>',
		esc_attr( $setting->get_field_name() ),
		esc_attr( $setting->get_field_id() ),
		esc_attr( $attribute_string ),
		esc_html( $value )
	);

	// description
	if ( ! empty( $args['description'] ) )
		vs_description_display( $args['description'] );
}

function vs_display_checkbox( $value, $setting, $args ) {

	// custom attributes
	if ( empty( $args['attributes'] ) ) {
	    $attribute_string = '';
	} else {
		$attribute_string = vs_html_attrs( (array) $args['attributes'] );
	}

	printf( '<input name="%s" id="%s"%s value="1" type="checkbox" %s/>',
		esc_attr( $setting->get_field_name() ),
		esc_attr( $setting->get_field_id() ),
		checked( '1', $value, false ),
		esc_attr( $attribute_string )
	);

	// description
	if ( ! empty( $args['description'] ) )
		vs_description_display( $args['description'] );
}
