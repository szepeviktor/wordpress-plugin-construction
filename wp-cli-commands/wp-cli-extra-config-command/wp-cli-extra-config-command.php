<?php

/**
 * Manage extra (non wp-cli) config options.
 *
 * ## OPTIONS
 *
 * [--format=<format>]
 * : Accepted values: json, shell, var_export. Default: var_export.
 *
 * ## EXAMPLES
 *
 *     wp yaml get client
 */
class WP_CLI_Extra_Config extends WP_CLI_Command {

	/**
	 * Display a value, in various formats
	 *
	 * @param mixed $value
	 * @param array $assoc_args
	 */
	private function print_value( $value, $assoc_args = array() ) {
		if ( ! isset( $assoc_args['format'] ) )
			$assoc_args['format'] = 'var_export';

		switch ( $assoc_args['format'] ) {
		case 'json':
			$output = json_encode( $value );
			break;

		case 'shell':
			if ( is_array( $value ) ) {
				$output = '';

				foreach ( $value as $key => $val )
					if ( ! is_scalar( $val ) ) {
						$val = json_encode( $val );

					$value[$key] = str_replace( '"', '\"', $val );
				}
				$output = '( "' . implode( '" "', $value ) . '" )';
			} else {
				$output = '"' . str_replace( '"', '\"', $value ) . '"';
			}
			break;

		case 'var_export':
		default:
			$output = var_export( $value );
			break;
		}

		print $output . PHP_EOL;
	}

	/**
	 * Get a config option.
	 *
	 * @synopsis <key> [--format=<format>]
	 *
	 * @when before_wp_load
	 */
	public function get( $args, $assoc_args ) {
		list( $key ) = $args;

		$extra_config = WP_CLI::get_runner()->extra_config;
		if ( isset( $extra_config[$key] ) )
			$this->print_value( $extra_config[$key], $assoc_args );
	}

}

WP_CLI::add_command( 'econfig', 'WP_CLI_Extra_Config' );
