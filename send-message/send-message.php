<?php
/*
Plugin Name: Send message
Version: 0.1.0
Description: Send an email (subject and text message) to WordPress users.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: GPLv2 (or later)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'Break-in attempt detected: send_message_direct_access '
        . addslashes( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' )
    );
    ob_get_level() && ob_end_clean();
    if ( ! headers_sent() ) {
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.1 403 Forbidden', true, 403 );
        header( 'Connection: Close' );
    }
    exit;
}

final class O1_Send_Message {

    private $capability = 'list_users';
    private $from_name = '';

    public function __construct() {

        if ( ! is_admin() ) {
            return;
        }

        add_action( 'init', array( $this, 'init' ) );
    }

    public function init() {

        if ( ! current_user_can( $this->capability ) ) {
            return;
        }

        // @TODO Bulk action, multiple recipients
        add_filter( 'user_row_actions', array( $this, 'user_action' ), 0, 2 );

        if ( is_network_admin() ) {
            add_action( 'network_admin_menu', array( $this, 'register_submenu' ) );
        } else {
            add_action( 'admin_menu', array( $this, 'register_submenu' ) );
        }
    }

    public function user_action( $actions, $user ) {

        $actions['message'] = sprintf( '<a class="sendmessage" href="%s">%s</a>',
            wp_nonce_url( admin_url( 'users.php?page=sendmessage&user_id=' . $user->ID ), 'sendmessage' ),
            __( 'Send message', 'send-message' )
        );

        return $actions;
    }

    public function register_submenu() {

        add_submenu_page(
            'users.php',
            __( 'Page Title', 'send-message' ),
            __( 'Menu item', 'send-message' ),
            $this->capability,
            'sendmessage',
            array( $this, 'submenu_callback' )
        );
        // Hide menu
        remove_submenu_page( 'users.php', 'sendmessage' );
    }

    public function from_name( $old_name ) {

        return $this->from_name;
    }

    private function process_post( $user ) {

        if ( ! isset( $_POST['submit'] ) ) {
            return false;
        }

        // From
        $current_user = wp_get_current_user();
        $this->from_name = $current_user->display_name;
        add_filter( 'wp_mail_from_name', array( $this, 'from_name' ), 9999 );
        $from = get_option( 'admin_email' );

        // To
        $to = $user->user_email;

        // Subject
        if ( empty( $_POST['message_subject'] ) || empty( trim( $_POST['message_subject'] ) ) ) {
            $subject = __( 'Message from ', 'send-message' ) . get_bloginfo( 'name' );
        } else {
            $subject = $_POST['message_subject'];
        }

        // Message
        if ( empty( $_POST['message_text'] ) || empty( trim( $_POST['message_text'] ) ) ) {
            return array(
                'notice_class' => 'notice-warning',
                'notice_text'  => 'Empty message. Please enter some text.',
            );
        }
        $message = $_POST['message_text'] ;

        if ( wp_mail( $to, $subject, $message ) ) {
            return array(
                'notice_class' => 'notice-success',
                'notice_text'  => 'E-mail sent.',
            );
        }

    }

    public function submenu_callback() {

        /**
         * 1. Show empty form
         * 2. Send + Show sent form
         */

        // Protect form
        check_admin_referer( 'sendmessage' );

        // To
        $user = get_user_by( 'ID', intval( $_GET['user_id'] ) );
        if ( false === $user ) {
            wp_die(
                '<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
                '<p>' . __( 'There are no valid users selected for recipients.', 'send-message' ) . '</p>',
                403
            );
        }
        $to_name = $user->display_name;

        // Send email on submit
        $result = $this->process_post( $user );

        ?>
        <div class="wrap" id="send-message-page">
            <h1>Send Message</h1>

            <?php if ( is_array( $result ) ) : ?>
            <div class="notice <?php echo esc_attr( $result['notice_class'] ); ?> is-dismissible">
                <p><?php echo esc_html( $result['notice_text'] ); ?></p>
            </div>
            <?php endif; ?>

            <form id="send-message" method="post" novalidate="novalidate"
                action="<?php echo esc_url( self_admin_url( 'users.php?page=sendmessage&user_id=' . $user->ID ) ); ?>">
            <?php wp_nonce_field( 'sendmessage' ); ?>
            <table class="form-table">
            <tbody>
                <tr class="message-to-wrap">
                    <th><label for="message_to_name"><?php esc_html_e( 'To', 'send-message' ); ?></label></th>
                    <td><input name="to_name" id="to_name" value="<?php echo esc_attr( $to_name ); ?>"
                        class="regular-text" type="text" disabled></td>
                </tr>
                <tr class="message-subject-wrap">
                    <th><label for="message_subject"><?php esc_html_e( 'Subject', 'send-message' ); ?></label></th>
                    <td><input name="message_subject" id="message_subject" value=""
                        class="regular-text" type="text" autofocus></td>
                </tr>
                <tr class="message-text-wrap">
                    <th><label for="message_text"><?php esc_html_e( 'Message', 'send-message' ); ?></label></th>
                    <td><textarea name="message_text" id="message_text" value=""
                        rows="5" class="regular-text"></textarea></td>
                </tr>
            </tbody>
            </table>
            <p class="submit"><input name="submit" id="submit"
                class="button button-primary" value="<?php esc_attr_e( 'Send', 'send-message' ); ?>" type="submit"></p>
            </form>
        </div>
        <?php
    }
}

new O1_Send_Message();
