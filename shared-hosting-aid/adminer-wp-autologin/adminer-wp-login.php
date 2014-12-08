<?php

/**
 * Automatic login to WordPress based on wp-config.php values.
 * @author Viktor Szépe
 */
class AdminerWPLogin {

    // from wp-cli
    private function replace_path_consts( $source, $path ) {
        $replacements = array(
            '__FILE__' => "'$path'",
            '__DIR__'  => "'" . dirname( $path ) . "'"
        );

        $old = array_keys( $replacements );
        $new = array_values( $replacements );

        return str_replace( $old, $new, $source );
    }

    // from wp-cli
    private function get_wp_config_code() {
        $wp_config_path = './wp-config.php';

        $wp_config_code = explode( "\n", file_get_contents( $wp_config_path ) );

        $found_wp_settings = false;

        $lines_to_run = array();

        foreach ( $wp_config_code as $line ) {
            if ( preg_match( '/^\s*require.+wp-settings\.php/', $line ) ) {
                $found_wp_settings = true;
                continue;
            }

            $lines_to_run[] = $line;
        }

        if ( !$found_wp_settings ) {
            die( 'Strange wp-config.php file: wp-settings.php is not loaded directly.' );
        }

        $source = implode( "\n", $lines_to_run );
        $source = $this->replace_path_consts( $source, $wp_config_path );
        return preg_replace( '|^\s*\<\?php\s*|', '', $source );
    }

    function credentials() {
        eval( $this->get_wp_config_code() );
        return array( DB_HOST, DB_USER, DB_PASSWORD );
    }

    function database() {
        return DB_NAME;
    }

    function loginForm() {
        ?>
<table cellspacing="0">
<tr><th>System<td><select name='auth[driver]'><option value="server" selected>MySQL</select>
<tr><th>Server<td><input name="auth[server]" value="" title="hostname[:port]" placeholder="localhost" autocapitalize="off">
<tr><th>Username<td><input name="auth[username]" id="username" value="" placeholder="WordPress" autocapitalize="off">
<tr><th>Password<td><input type="password" name="auth[password]" placeholder="WordPress">
<tr><th>Database<td><input name="auth[db]" value="" autocapitalize="off" placeholder="WordPress">
</table>
<p><input type="submit" value="<?php echo lang('Login'); ?>">
<?php
        echo checkbox("auth[permanent]", 1, $_COOKIE["adminer_permanent"], lang('Permanent login')) . "\n";
        return true;
    }

}
