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
function vs_display_text_field( $value, $setting, $args ) {

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

	if ( empty( $args['description'] ) )
		return;

	printf( '<p class="description">%s</p>',
		wp_kses_post( $args['description'] )
	);
}

function vs_display_dropdown( $value, $setting, $args ) {
	if( ! isset( $args['options'] ) ) {
		print '<p class="error">An options argument is required in the <code>$args</code> array to use <code>vs_display_dropdown()</code></p>';
		return;

	} else {
		printf( '<select id="%s" name="%s">',
			esc_attr( $setting->get_field_id() ),
			esc_attr( $setting->get_field_name() )
		);
		foreach( $args['options'] as $option_value => $option_text )
			printf( '<option value="%s"%s>%s</option>',
				esc_attr( $option_value ),
				selected( $option_value, $value, false ),
				$option_text
			);
		print '</select>';

		if( empty( $args['description'] ) )
			return;
		printf( '<p class="description">%s</p>',
			wp_kses_post( $args['description'] )
		);
	}
}

function vs_display_textarea( $value, $setting, $args ) {

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

	if ( empty( $args['description'] ) )
		return;
	printf( '<p class="description">%s</p>',
		wp_kses_post( $args['description'] )
	);
}

function vs_display_checkbox( $value, $setting, $args ) {
	printf( '<input name="%s" id="%s"%s value="1" type="checkbox" />',
		esc_attr( $setting->get_field_name() ),
		esc_attr( $setting->get_field_id() ),
		checked( '1', $value, false )
	);

	if ( empty( $args['description'] ) )
		return;
	printf( '<p class="description">%s</p>',
		wp_kses_post( $args['description'] )
	);
}

