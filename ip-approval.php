<?php
/*
Plugin Name: IP Address Approval
Description: The IP Address Approval system provides an easy way for you to Allow or Block access to your website to protect your site from unwanted visitors.
Author: IP Address Approval
Text Domain: ip-address-approval
Author URI: https://www.ip-approval.com
Plugin URI: https://www.ip-approval.com/wordpress/ip-approval/
Version: 1.9.2
License: EULA
License URI: https://www.ip-approval.com/terms-of-service#EULA
*/

if(!defined('ABSPATH')) {
    exit;
}
$ip_approval_version = "1.9.2";
$ip_approval_plugin_name = "ip_approval";

if (is_admin()) {
    $ip_approval_pb = plugin_basename( __FILE__ );
    require(plugin_dir_path(__FILE__).'includes/is-admin.php');
} 
else {
   function ip_approval_checker_process($is_login = false) {
      $protocol = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'https://' : 'http://';
      $uri = $_SERVER['REQUEST_URI'];
      $host = $_SERVER['HTTP_HOST'];
      $url = $protocol . $host . $uri;
      $api = plugin_dir_path(__FILE__).'includes/api.php';
      if (file_exists($api)) {
          require($api);
          $get_ip_return_info = IP_APPROVAL_API::ip_checker($uri,$url,$is_login);
          if (is_object($get_ip_return_info) && $get_ip_return_info->status == "Success") {
              if ($get_ip_return_info->redirect !== '0') {
                  if (substr($get_ip_return_info->redirect, 0, 8)=='https://' OR substr($get_ip_return_info->redirect, 0, 7)=='http://') {
                      $ip_redirect_to = $get_ip_return_info->redirect;
                  }
                  else {
                      $ip_redirect_to = get_site_url() . $get_ip_return_info->redirect;
                  }
                  if ($url !== $ip_redirect_to) {
                      wp_redirect($ip_redirect_to); // page to divert to if banned ip
                      exit();
                  }
              }
          }
      } // END if file_exists
   }

   /*  Add IP Approval IP Checker to website/blog
   --------------------------------------------------------------------*/
   add_action('get_header', 'ip_approval_checker_action');
   function ip_approval_checker_action(){
      global $ip_approval_plugin_name;
      $ip_approval_vals = get_option($ip_approval_plugin_name);
      if ($ip_approval_vals['enabled'] === 'enabled' && !is_admin()) { 
          $active_ip_element = false;
          if (is_page()){
             if (in_array(get_the_ID(), $ip_approval_vals['pages'])) {
                 $active_ip_element = true;
             }
          }
          else {
             if ($ip_approval_vals['posts']) {
                 $active_ip_element = true;
             }
          }
          if ($active_ip_element) {
              ip_approval_checker_process();
          }
      }
   }

   /*  Add IP Approval IP Checker to login
   --------------------------------------------------------------------*/
   add_filter('login_init', 'ip_approval_checker_login_action');
   function ip_approval_checker_login_action() {
      global $ip_approval_plugin_name;
      $ip_approval_vals = get_option($ip_approval_plugin_name);
      if ($ip_approval_vals['enabled'] === 'enabled') { 
          $active_ip_element = false;
          if ($ip_approval_vals['login_form']) {
              $active_ip_element = true;
          }
          if ($active_ip_element) {
              ip_approval_checker_process(true);
          }
      }
   }
}
?>