<?php
/*
Plugin Name: WP Mail Options
Plugin URI: http://wordpress.org/extend/plugins/wp-mail-options/
Description: This plugin allows you to set almost all options of emails sent by WordPress. In fact, it just simply modified the value of the PHPMailer's member variables. Warning: This plugin is only for advanced users. You should know exactly what effect each option will have on the behavior of PHPMailer when you use this plugin.
Version: 0.2.2
Author: Soli
Author URI: http://www.cbug.org
Text Domain: wp-mail-options
Lincense: Copyright (c) 2010 Released under the GPL license http://www.gnu.org/licenses/gpl.txt
*/

//for gettext
//$notuse = __("This plugin allows you to set almost all options of emails sent by WordPress. In fact, it just simply modified the value of the PHPMailer's member variables. Warning: This plugin is only for advanced users. You should know exactly what effect each option will have on the behavior of PHPMailer when you use this plugin.");

if(!class_exists('WPMailOptions')) {
class WPMailOptions {

function is_str_and_not_empty($var) {
    if (!is_string($var))
        return false;

    if (empty($var))
        return false;

    if ($var=='')
        return false;

    return true;
}

function WPMailOptions_PHPMailer_Init(&$mailer) {
    $phpmailer = &$mailer;

    $wp_mail_options = maybe_unserialize(get_option('wp_mail_options'));

    /////////////////////////////////////////////////
    // PROPERTIES, PUBLIC
    /////////////////////////////////////////////////

    /**
    * Email priority (1 = High, 3 = Normal, 5 = low).
    * @var int
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_prior']))
    $phpmailer->Priority          =$wp_mail_options['wpmo_mail_prior'];

    /**
    * Sets the CharSet of the message.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_charset']))
    $phpmailer->CharSet           =$wp_mail_options['wpmo_mail_charset'];

    /**
    * Sets the Content-type of the message.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_content_type']))
    $phpmailer->ContentType       =$wp_mail_options['wpmo_mail_content_type'];

    /**
    * Sets the Encoding of the message. Options for this are "8bit",
    * "7bit", "binary", "base64", and "quoted-printable".
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_encoding']))
    $phpmailer->Encoding          =$wp_mail_options['wpmo_mail_encoding'];

    /**
    * Holds the most recent mailer error message.
    * @var string
    * /
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_error_info']))
    $phpmailer->ErrorInfo         =$wp_mail_options['wpmo_mail_error_info'];
*/
    /**
    * Sets the From email address for the message.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_from']))
    $phpmailer->From              =$wp_mail_options['wpmo_mail_from'];

    /**
    * Sets the From name of the message.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_from_name']))
    $phpmailer->FromName          =$wp_mail_options['wpmo_mail_from_name'];

    /**
    * Sets the Sender email (Return-Path) of the message.  If not empty,
    * will be sent via -f to sendmail or as 'MAIL FROM' in smtp mode.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_sender']))
    $phpmailer->Sender            =$wp_mail_options['wpmo_mail_sender'];

    /**
    * Sets the Subject of the message.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_subject']))
    $phpmailer->Subject           =$wp_mail_options['wpmo_mail_subject'];

    /**
    * Sets the Body of the message.  This can be either an HTML or text body.
    * If HTML then run IsHTML(true).
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_body']))
    $phpmailer->Body              =$wp_mail_options['wpmo_mail_body'];

    /**
    * Sets the text-only body of the message.  This automatically sets the
    * email to multipart/alternative.  This body can be read by mail
    * clients that do not have HTML email capability such as mutt. Clients
    * that can read HTML will view the normal Body.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_altbody']))
    $phpmailer->AltBody           =$wp_mail_options['wpmo_mail_altbody'];

    /**
    * Sets word wrapping on the body of the message to a given number of
    * characters.
    * @var int
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_word_wrap']))
    $phpmailer->WordWrap          =$wp_mail_options['wpmo_mail_word_wrap'];

    /**
    * Method to send mail: ("mail", "sendmail", or "smtp").
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_mailer']))
    $phpmailer->Mailer            =$wp_mail_options['wpmo_mail_mailer'];

    /**
    * Sets the path of the sendmail program.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_sendmail']))
    $phpmailer->Sendmail          =$wp_mail_options['wpmo_mail_sendmail'];

    /**
    * Path to PHPMailer plugins.  This is now only useful if the SMTP class
    * is in a different directory than the PHP include path.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_plugin_dir']))
    $phpmailer->PluginDir         =$wp_mail_options['wpmo_mail_plugin_dir'];

    /**
    * Holds PHPMailer version.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_phpmailer_ver']))
    $phpmailer->Version           =$wp_mail_options['wpmo_mail_phpmailer_ver'];

    /**
    * Sets the email address that a reading confirmation will be sent.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_confirm_reading_to']))
    $phpmailer->ConfirmReadingTo  =$wp_mail_options['wpmo_mail_confirm_reading_to'];

    /**
    * Sets the hostname to use in Message-Id and Received headers
    * and as default HELO string. If empty, the value returned
    * by SERVER_NAME is used or 'localhost.localdomain'.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_host_name']))
    $phpmailer->Hostname          =$wp_mail_options['wpmo_mail_host_name'];

    /**
    * Sets the message ID to be used in the Message-Id header.
    * If empty, a unique id will be generated.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_message_id']))
    $phpmailer->MessageID         =$wp_mail_options['wpmo_mail_message_id'];

    /////////////////////////////////////////////////
    // PROPERTIES FOR SMTP
    /////////////////////////////////////////////////

    /**
    * Sets the SMTP hosts.  All hosts must be separated by a
    * semicolon.  You can also specify a different port
    * for each host by using this format: [hostname:port]
    * (e.g. "smtp1.example.com:25;smtp2.example.com").
    * Hosts will be tried in order.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_smtp_host']))
    $phpmailer->Host              =$wp_mail_options['wpmo_mail_smtp_host'];

    /**
    * Sets the default SMTP server port.
    * @var int
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_smtp_port']))
    $phpmailer->Port              =$wp_mail_options['wpmo_mail_smtp_port'];

    /**
    * Sets the SMTP HELO of the message (Default is $Hostname).
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_smtp_helo']))
    $phpmailer->Helo              =$wp_mail_options['wpmo_mail_smtp_helo'];

    /**
    * Sets connection prefix.
    * Options are "", "ssl" or "tls"
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_smtp_secure']))
    $phpmailer->SMTPSecure          =$wp_mail_options['wpmo_mail_smtp_secure'];

    /**
    * Sets SMTP authentication. Utilizes the Username and Password variables.
    * @var bool
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_smtp_auth']))
    $phpmailer->SMTPAuth          =$wp_mail_options['wpmo_mail_smtp_auth'];

    /**
    * Sets SMTP username.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_smtp_username']))
    $phpmailer->Username          =$wp_mail_options['wpmo_mail_smtp_username'];

    /**
    * Sets SMTP password.
    * @var string
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_smtp_password']))
    $phpmailer->Password          =$wp_mail_options['wpmo_mail_smtp_password'];

    /**
    * Sets the SMTP server timeout in seconds. This function will not
    * work with the win32 version.
    * @var int
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_smtp_timeout']))
    $phpmailer->Timeout              =$wp_mail_options['wpmo_mail_smtp_timeout'];

    /**
    * Sets SMTP class debugging on or off.
    * @var bool
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_smtp_debug']))
    $phpmailer->SMTPDebug          =$wp_mail_options['wpmo_mail_smtp_debug'];

    /**
    * Prevents the SMTP connection from being closed after each mail
    * sending.  If this is set to true then to close the connection
    * requires an explicit call to SmtpClose().
    * @var bool
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_smtp_keep_alive']))
    $phpmailer->SMTPKeepAlive      =$wp_mail_options['wpmo_mail_smtp_keep_alive'];

    /**
    * Provides the ability to have the TO field process individual
    * emails, instead of sending to entire TO addresses
    * @var bool
    */
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_smtp_single_to']))
    $phpmailer->SingleTo          =$wp_mail_options['wpmo_mail_smtp_single_to'];

    /////////////////////////////////////////////////
    // PROPERTIES, PRIVATE
    /////////////////////////////////////////////////

    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_to']))
        $phpmailer->to              = explode(',', $wp_mail_options['wpmo_mail_to']);

    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_cc']))
        $phpmailer->cc              = explode(',', $wp_mail_options['wpmo_mail_cc']);

    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_bcc']))
        $phpmailer->bcc             = explode(',', $wp_mail_options['wpmo_mail_bcc']);

    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_replyto']))
        $phpmailer->ReplyTo        = explode(',', $wp_mail_options['wpmo_mail_replyto']);

    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_attachment']))
        $phpmailer->attachment        += $wp_mail_options['wpmo_mail_attachment'];

    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_custom_header'])) {
        $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $wp_mail_options['wpmo_mail_custom_header'] ) );
        
        if ( !empty( $tempheaders ) ) {
            foreach ( (array) $tempheaders as $header ) {
                $phpmailer->AddCustomHeader($header);
            }
        }
    }
/*
    if($this->is_str_and_not_empty($wp_mail_options['wpmo_mail_boundary']))
        $phpmailer->boundary     = $wp_mail_options['wpmo_mail_boundary'];

    $phpmailer->smtp            = NULL;
    $phpmailer->message_type    = '';
    $phpmailer->language        = array();
    $phpmailer->error_count     = 0;
    $phpmailer->LE              = "\n";
    $phpmailer->sign_cert_file  = "";
    $phpmailer->sign_key_file   = "";
    $phpmailer->sign_key_pass   = "";
 */
}

/**
 * Registers additional links for the plugin on the WP plugin configuration page
 *
 * Registers the links if the $file param equals to the plugin
 * @param $links Array An array with the existing links
 * @param $file string The file to compare to
 */
function RegisterPluginLinks($links, $file) {
//    load_plugin_textdomain( 'wp-mail-options', false, dirname( plugin_basename( __FILE__ ) ) . "/lang" );
    $base = plugin_basename(__FILE__);
    if ($file ==$base) {
        $links[] = '<a href="options-general.php?page=wp-mail-options">' . __('Settings','wp-mail-options') . '</a>';
        $links[] = '<a href="http://www.cbug.org/category/wp-mail-options">' . __('FAQ','wp-mail-options') . '</a>';
    }
    return $links;
}

/**
 * Handled the plugin activation on installation
 */
function ActivatePlugin() {
    $optfile = trailingslashit(dirname(__FILE__)) . "options.txt";
    $options = file_get_contents($optfile);
    add_option("wp_mail_options", $options, null, 'no');
}

/**
 * Handled the plugin deactivation
 */
function DeactivatePlugin() {
    $optfile = trailingslashit(dirname(__FILE__)) . "options.txt";
    file_put_contents($optfile, get_option("wp_mail_options"));
    delete_option("wp_mail_options");
}

} // end of class WPMailOptions
} // end of if(!class_exists('WPMailOptions'))

//load_plugin_textdomain( 'wp-mail-options', false, dirname( plugin_basename( __FILE__ ) ) . "/lang" );

if(class_exists('WPMailOptions')) {

    $wpmailoptions = new WPMailOptions();

    if(isset($wpmailoptions)) {
        register_activation_hook(__FILE__, array(&$wpmailoptions, 'ActivatePlugin'));
        register_deactivation_hook(__FILE__, array(&$wpmailoptions, 'DeactivatePlugin'));

        //Additional links on the plugin page
        add_filter('plugin_row_meta', array(&$wpmailoptions, 'RegisterPluginLinks'),10,2);

        add_action('phpmailer_init', array(&$wpmailoptions, 'WPMailOptions_PHPMailer_Init'));
    }
}

/* Options Page */
require_once(trailingslashit(dirname(__FILE__)) . "wp-mail-options-page.php");

if(class_exists('WPMailOptionsPage')) {
    $wpmailoptions_page = new WPMailOptionsPage();

    if(isset($wpmailoptions_page)) {
        add_action('admin_menu', array(&$wpmailoptions_page, 'WPMailOptions_Menu'), 1);

        /*
         * wp_mail() is defined in wp-includes/pluggable.php.
         * This file is loaded after the plugins are loaded,
         * but before the hook plugins_loaded has been fired.
         *
         * So, we need this trick to send test mail using wp_mail().
         * */
        add_action('plugins_loaded', array(&$wpmailoptions_page, 'WPMailOptions_TestMail'), 1);
    }
}

