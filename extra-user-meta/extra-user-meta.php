<?php
/*
Plugin Name: Extra User Meta
Description: Adds an extra field to user profiles. <code>define( 'EXTRA_USER_META' , 'wc_extra_price_list|Price list|css_class|Price list code (0-7)' );</code>
Version: 0.1.0
*/

class O1_User_Meta {

    public function __construct() {
        add_action( 'show_user_profile', array( $this, 'add_user_meta' ), 9 );
        add_action( 'edit_user_profile', array( $this, 'add_user_meta' ), 9 );

        add_action( 'personal_options_update', array( $this, 'save_user_meta' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_user_meta' ) );
    }

    public function add_user_meta( $user ) {
        if ( ! current_user_can( 'edit_user' ) || ! defined( 'EXTRA_USER_META' ) ) {
            return;
        }

        $fields = explode( '|', EXTRA_USER_META );

            ?>
            <h3>Extra</h3>
            <table class="form-table">
                <tr>
                    <th>
                        <label for="<?php echo esc_attr( $fields[0] ); ?>"
                        ><?php echo esc_html( $fields[1] ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="<?php echo esc_attr( $fields[0] ); ?>"
                        id="<?php echo esc_attr( $fields[0] ); ?>"
                        value="<?php echo esc_attr( get_user_meta( $user->ID, $fields[0], true ) ); ?>"
                        class="<?php echo ( ! empty( $fields[2] ) ? $fields[2] : 'regular-text' ); ?>" />

                        <p class="description"><?php echo wp_kses_post( $fields[3] ); ?></p>
                    </td>
                </tr>
            </table>
            <?php
    }

    public function save_user_meta( $user_id ) {
        $fields = explode( '|', EXTRA_USER_META );

        if ( isset( $_POST[ $fields[0] ] ) ) {
            update_user_meta( $user_id, $fields[0], sanitize_text_field( $_POST[ $fields[0] ] ) );
        }
    }

}

new O1_User_Meta();
