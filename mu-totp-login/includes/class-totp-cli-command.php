<?php
/**
 * TOTP CLI command.
 */
class Totp_cli extends WP_CLI_Command {

    /**
     * Generete secret code for user.
     *
     * ## OPTIONS
     *
     * <user>
     * : The user login, user email, or user ID of the user to delete metadata from.
     *
     * ## EXAMPLES
     *
     *     wp totp add Newman
     */
    function add( $args, $assoc_args ) {

        $fetcher = new \WP_CLI\Fetchers\User;
        $user = $fetcher->get_check( $args[0] );

        require_once dirname( __FILE__ ) . '/OtpInterface.php';
        require_once dirname( __FILE__ ) . '/Otp.php';
        require_once dirname( __FILE__ ) . '/GoogleAuthenticator.php';
        require_once dirname( __FILE__ ) . '/Base32.php';

        $secret_code = Otp\GoogleAuthenticator::generateRandom( 32 );

        $meta_added = update_user_meta( $user->ID, '_totp_login_secret_code', $secret_code );
        if ( $meta_added ) {

            // otpauth://TYPE/LABEL?PARAMETERS
            // https://github.com/google/google-authenticator/wiki/Key-Uri-Format
            $url_params = array(
                'chs'  => '300x300',
                'chld' => 'M|0',
                'cht'  => 'qr',
                'chl'  => urlencode( sprintf( 'otpauth://totp/%s:%1$s?secret=%s&issuer=%1$s',
                    $user->data->user_login,
                    $secret_code
                ) ),
            );
            $qr_url = 'https://chart.googleapis.com/chart?' . build_query( $url_params );

            WP_CLI::success( $secret_code );
            WP_CLI::log( $qr_url );
        } else {
            WP_CLI::error( 'Failed to add secret code to user ' . $user->ID );
        }
    }
}

WP_CLI::add_command( 'totp', 'Totp_cli' );
