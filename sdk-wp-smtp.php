<?php
if ( ! defined( 'ABSPATH' ) ) exit('We know what you are trying.'); // Exit 

/*
Plugin Name: SDK WP SMTP
Description: Standard Development Kit WP SMTP can help us to send emails via over the sky of SMTP instead of using the core php mail() function.
Version: 1.0.4
Author: Shiv Kumar Sharma
Author URI: https://profiles.wordpress.org/sdkwebmasters
Text Domain: sdk-wp-smtp
Domain Path: /lang
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*
 * The plugin was originally created by BoLiQuan, Yehuda Hassine.
 */

class SDK_WP_SMTP {

	private $wsOptions, $phpmailer_error;

	public function __construct() {
		$this->setup_vars();
		$this->hooks();
	}

	public function setup_vars(){
		$this->wsOptions = get_option( 'sdk_wp_smtp_options' );
	}

	public function hooks() {
		register_activation_hook( __FILE__ , array( $this,'sdk_wp_smtp_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'sdk_wp_smtp_deactivate' ) );

		add_filter( 'plugin_action_links', array( $this, 'sdk_wp_smtp_settings_link' ), 10, 2 );
		add_action( 'init', array( $this,'load_textdomain' ) );
		add_action( 'phpmailer_init', array( $this,'sdk_wp_smtp' ) );
		add_action( 'wp_mail_failed', array( $this, 'catch_phpmailer_error' ) );
		add_action( 'admin_menu', array( $this, 'sdk_wp_smtp_admin' ) );
	}

	function sdk_wp_smtp_activate(){
		$wsOptions = array();
		$wsOptions["from"] = "";
		$wsOptions["fromname"] = "";
		$wsOptions["host"] = "";
		$wsOptions["smtpsecure"] = "";
		$wsOptions["port"] = "";
		$wsOptions["smtpauth"] = "yes";
		$wsOptions["username"] = "";
		$wsOptions["password"] = "";
		$wsOptions["deactivate"] = "";
		/*
		* Updaaated by Shiv Kumar
		* Added extended feature.
		*/
		$wsOptions["exclude_from"] = "";
		$wsOptions["exclude_fromname"] = "";
		$wsOptions["exclude_reply"] = "";

		add_option( 'sdk_wp_smtp_options', $wsOptions );
	}

	function sdk_wp_smtp_deactivate() {
		if( $this->wsOptions['deactivate'] == 'yes' ) {
			delete_option( 'sdk_wp_smtp_options' );
		}
	}

	function load_textdomain() {
		load_plugin_textdomain( 'sdk-wp-smtp', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	}

	function sdk_wp_smtp( $phpmailer ) {

		if( ! is_email($this->wsOptions["from"] ) || empty( $this->wsOptions["host"] ) ) {
			return;
		}

		$phpmailer->Mailer = "smtp";
		
		if(!($this->wsOptions["exclude_from"] == "yes"))
		{
		$phpmailer->From = $this->wsOptions["from"];
		}
		
		if(!($this->wsOptions["exclude_fromname"] == "yes"))
		{
		$phpmailer->FromName = $this->wsOptions["fromname"];
		}
		
		$phpmailer->Sender = $phpmailer->From; //Return-Path
		
		if(!($this->wsOptions["exclude_from"] == "yes"))
		{
		    $phpmailer->AddReplyTo($phpmailer->From,$phpmailer->FromName);
		}
		
		$phpmailer->Host = $this->wsOptions["host"];
		$phpmailer->SMTPSecure = $this->wsOptions["smtpsecure"];
		$phpmailer->Port = $this->wsOptions["port"];
		$phpmailer->SMTPAuth = ($this->wsOptions["smtpauth"]=="yes") ? TRUE : FALSE;

		if( $phpmailer->SMTPAuth ){
			$phpmailer->Username = $this->wsOptions["username"];
			$phpmailer->Password = $this->wsOptions["password"];
		}
	}

	function catch_phpmailer_error( $error ) {
		$this->phpmailer_error = $error;
	}

	function sdk_wp_smtp_settings_link($action_links,$plugin_file) {
		if( $plugin_file == plugin_basename( __FILE__ ) ) {
			$ws_settings_link = '<a href="options-general.php?page=' . dirname( plugin_basename(__FILE__) ) . '/sdk-wp-smtp.php">' . __("Settings") . '</a>';
			array_unshift($action_links,$ws_settings_link);
		}

		return $action_links;
	}

	function sdk_wp_smtp_admin(){
		add_options_page('SDK WP SMTP Options', 'SDK WP SMTP','manage_options', __FILE__, array( $this, 'sdk_wp_smtp_page') );
	}

	function sdk_wp_smtp_page(){
		require_once __DIR__ . '/sdk_wp_smtp_admin.php';
	}
}

new SDK_WP_SMTP();
?>