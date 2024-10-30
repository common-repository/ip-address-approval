<?php
if(!defined('ABSPATH')) {
    exit;
}

/*  LOAD PLUGIN for ADMIN
--------------------------------------------------------------------*/
class IP_APPROVAL_Plugin {
    public $version;
    public $plugin_name;
    public $basename;
    private $options = null;
    function __construct($name,$ver,$bn) {
        $this->plugin_name = $name;
        $this->version = $ver;
        $this->basename = $bn;
        $this->load_options();
        if (wp_doing_ajax()) {
            add_action('wp_ajax_ip_approval_ajax_action', [$this, 'ip_approval_ajax_action']);
        }
        else {
            $this->autoload();
            $this->cred_check();
        }
    }

    /*   LOAD OPTIONS
    --------------------------------------------------------------------*/
    private function load_options() {
        if (is_null($this->options)) {
           $this->options = get_option($this->plugin_name);
        }
    }


    /*   Credentials Check
    --------------------------------------------------------------------*/
    private function disable_plugin_link( $plugin, $action = 'deactivate' ) {
        if ( strpos( $plugin, '/' ) ) {
            $plugin = str_replace( '\/', '%2F', $plugin );
        }
        $url = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
        $_REQUEST['plugin'] = $plugin;
        $url = wp_nonce_url( $url, $action . '-plugin_' . $plugin );
        return $url;
    }


    /*   Credentials Check
    --------------------------------------------------------------------*/
    public function cred_check_incorrect() {
        $link = 'admin.php?page='.$this->plugin_name.'&link=cred_check';
        echo '<div id="message" class="notice notice-error"><p><strong>IP Address Approval Credentials are incorrect.</strong></p><p>Please update your <a href="'.$link.'">IP Address Approval Credentials Here</a>, our service will not work correctly until they are updated.</p><p>If you do not want to use the IP Address Approval service, please <a href="'.$this->disable_plugin_link($this->basename).'">Deactivate the Plugin</a>.</p></div>';
    }


    /*   Credentials Check
    --------------------------------------------------------------------*/
    private function cred_check() {
        if ($this->options['enabled'] === 'enabled') {
           if (!array_key_exists("cred_check_date", $this->options)) {
               $this->options['cred_check_status'] = '';
               $this->options['cred_check_date'] = date('m/d/Y', strtotime("-1 days"));
               $this->options['cred_check_disabled'] = date('m/d/Y', strtotime("+5 days"));
               update_option($this->plugin_name, $this->options);
           }

           $check_date = date($this->options['cred_check_date']);
           $str_check_date = strtotime($check_date);

           $current_date = date('m/d/Y');
           $str_current_date = strtotime($current_date);

           $disabled_date = date($this->options['cred_check_disabled']);
           $str_disabled_date = strtotime($disabled_date);
           if ($str_current_date >= $str_disabled_date && !($str_check_date < strtotime('-4 days')) && $this->options['cred_check_status'] === 'Failed') {
               // Deactivate Plugin due to Incorrect Credentials for 5 days
               $this->options['cred_check_disabled'] = date('m/d/Y', strtotime("+5 days"));
               $this->options['enabled'] === 'disabled';
               update_option($this->plugin_name, $this->options);

               echo '<div id="message" class="notice notice-error is-dismissible"><p><strong>IP Address Approval Credentials are incorrect.</strong></p><p>The Plugin Setting has been disable on your website due to Incorrect Credentials for 5 days or more.</p></div>';
           }
           elseif($str_check_date < $str_current_date){
              require(plugin_dir_path(__FILE__).'api.php');
              $get_cred_check = IP_APPROVAL_API::ip_cred();
              if (is_object($get_cred_check)) {
                  if (property_exists($get_cred_check, "status")) {
                        echo '<div id="message" class="notice notice-error is-dismissible"><p><strong>'.$get_cred_check->status.'</strong></p></div>';
                        echo '<div id="message" class="notice notice-error is-dismissible"><p><strong>'.$get_cred_check->message.'</strong></p></div>';
                      $this->options['cred_check_status'] = $get_cred_check->status;
                      $this->options['cred_check_date'] = date('m/d/Y');
                      if ($get_cred_check->status === "Failed") {
                          add_action('init', [$this, 'cred_check_incorrect']);
                      }
                      if ($get_cred_check->status === "Success") {
                          $this->options['cred_check_disabled'] = date('m/d/Y', strtotime("+5 days"));
                      }
                      update_option($this->plugin_name, $this->options);
                  }
              } // END is_object
           }
           elseif ($this->options['cred_check_status'] === 'Failed') {
                        echo '<div id="message" class="notice notice-error is-dismissible"><p><strong>'.$this->options['cred_check_status'].'</strong></p></div>';
                   add_action('init', [$this, 'cred_check_incorrect']);
           }

        }
    }

    /*   AUTOLOAD
    --------------------------------------------------------------------*/
    private function autoload() {
        register_activation_hook(__FILE__, [$this, 'ip_approval_on_activation']);
        add_action('admin_menu', [$this, 'ip_approval_register_custom_menu_page'], 0);
        add_filter('plugin_action_links_'.$this->basename, [$this, 'ip_approval_plugin_add_settings_link']);
        add_filter( 'plugin_row_meta', [ $this, 'ip_approval_plugin_add_meta' ], 10, 2 );
    }

    /*   AUTOLOAD META LINKS
    --------------------------------------------------------------------*/
    public function ip_approval_plugin_add_meta($plugin_meta, $base) {
        if ($base === 'ip-address-approval/ip-approval.php') {
            $text = __( 'WordPress IP Plugin Instructions', 'ip-address-approval');
            $link = 'https://www.ip-approval.com/wordpress/ip-approval/instructions';
            $text2 = __( 'Support', 'ip-address-approval');
            $link2 = 'https://www.ip-approval.com/support';
            $meta = [
              'wp_inst' => '<a href="'.$link.'" target="_blank">'.$text.'</a>',
              'ip_support' =>  '<a href="'.$link2.'" target="_blank">'.$text2.'</a>',
            ];
            $plugin_meta = array_merge($plugin_meta,$meta);
        }
        return $plugin_meta;
   }

    /*   AUTOLOAD SETTINGS LINK
    --------------------------------------------------------------------*/
    public function ip_approval_plugin_add_settings_link($links) {
        $text = __('Settings', 'ip-address-approval');
        $link = 'admin.php?page='.$this->plugin_name;
        $text2 = __( 'Upgrade', 'ip-address-approval');
        $link2 = 'https://www.ip-approval.com/upgrade?utm_source=wp-plugins&utm_campaign=ip_ug&utm_medium=wp-dash';
        $settings_link = '<a href="'.$link.'">'.$text.'</a>';
        array_unshift($links, $settings_link);
        if (is_array($this->options) AND !$this->options['has_ug']) {
            $links['ip_ug'] = '<a href="'.$link2.'" target="_blank" style="color:#46b450; font-weight:bold">'.$text2.'</a>';
        }

        return $links;
    }

    /*   AUTOLOAD ADMIN MENU
    --------------------------------------------------------------------*/
    public function ip_approval_register_custom_menu_page(){
        add_menu_page( 
            __('IP Approval Settings', 'ip-address-approval'),
            __('IP Approval', 'ip-address-approval'),
            'manage_options',
            $this->plugin_name,
            'ip_approval_settings',
            'data:image/svg+xml;base64,'.base64_encode('<?xml version="1.0" encoding="utf-8"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><g><path fill="white" d="M7.75,17.589c-0.575,0-1.118-0.252-1.466-0.688l-4.717-5.873c-0.613-0.764-0.453-1.852,0.356-2.43 C2.735,8.019,3.888,8.17,4.5,8.935l3.103,3.861l7.802-11.812c0.536-0.813,1.67-1.062,2.533-0.554 c0.861,0.505,1.127,1.577,0.59,2.389L9.313,16.771c-0.32,0.486-0.873,0.793-1.48,0.816C7.805,17.589,7.778,17.589,7.75,17.589 L7.75,17.589z"/></g></svg>')
        );
    }

    /* AUTOLOAD Initialize plugin on activation
    --------------------------------------------------------------------*/
    public function ip_approval_on_activation() {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
        check_admin_referer("activate-plugin_{$plugin}");
        if ($this->options === false) {
            // Initialize option values
            $ip_first_run =  0;
            $ip_id = '0';
            $ip_site_id = '0';
            $api_key = '0';
            $api_secret = '0';
            $has_ug = 0;
            $login_form = 0;
            $ip_pages_reset = array();
            $ip_get_pages_reset = get_pages();
            foreach ($ip_get_pages_reset as $ip_page) {
                if ($ip_page->post_title !== 'Access Denied' AND $ip_page->post_title !== 'Banned') {
                    $ip_pages_reset[] = $ip_page->ID;
                }
            }
            $cred_check_status = '';
            $cred_check_date = date('m/d/Y', strtotime("1 days"));
            $cred_check_disabled = date('m/d/Y', strtotime("+5 days"));
            $ip_sections = array();
            $ip_sections['1'] = 'no';
            $ip_sections['2'] = 'no';
            $ip_approval_vals = array(
                 'version'       => $this->version,
                 'first_run'     => $ip_first_run,
                 'ip_id'         => $ip_id,
                 'ip_site_id'    => $ip_site_id,
                 'api_key'       => $api_key,
                 'api_secret'    => $api_secret,
                 'has_ug'        => $has_ug,
                 'login_form'    => $login_form,
                 'enabled'       => 'disabled',
                 'posts'         => 1,
                 'pagesall'      => 1,
                 'pages'         => $ip_pages_reset,
                 'cred_check_status'   => $cred_check_status,
                 'cred_check_date'     => $cred_check_date,
                 'cred_check_disabled' => $cred_check_disabled,
                 'sections'      => $ip_sections
            );
            update_option($this->plugin_name, $ip_approval_vals);
         }
    }

    /* AUTOLOAD AJAX
    ------------------------------------------------------------------------*/
    public function ip_approval_ajax_action() {
        $ip_approval_vals = $this->options;
        $ip_id = $ip_approval_vals['ip_id'];
        $nonce = $_POST['security'];
        if (!wp_verify_nonce($nonce, $ip_id)) {
            // This nonce is not valid.
            wp_send_json_error(array('status' => 'Failed', 'errorMessage' => 'Security failed verification'));
            die();
        }
        if (isset($_REQUEST['do_action'])) {
            $action = $_REQUEST['do_action'];
            if (!empty($action)) {
               switch($action) {
                   case "update_sections":
                       $ip_sections = array();
                       $ip_sections['1'] = 'no';
                       $ip_sections['2'] = 'no';
                       if ($_POST['one']) { 
                          if ($_POST['one'] !== $ip_sections['1']){
                              $ip_sections['1'] = 'yes';
                          }
                       }
                       if ($_POST['two']) { 
                          if ($_POST['two'] !== $ip_sections['2']){
                              $ip_sections['2'] = 'yes';
                          }
                       }
                       $ip_approval_vals['sections'] = $ip_sections;
                       update_option($this->plugin_name, $ip_approval_vals);
                     break;
                   case "get_visitors":
                       require(plugin_dir_path(__FILE__).'api.php');
                       $page=1;
                       if ($_POST['page']) { 
                          if (!empty($_POST['page'])){
                              $page = $_POST['page'];
                          }
                        }
                        $per_page=50;
                        if ($_POST['per_page']) { 
                           if (!empty($_POST['per_page'])){
                               $per_page = $_POST['per_page'];
                           }
                        }
                        wp_send_json_success(IP_APPROVAL_API::ip_visitors($page, $per_page));
                     break;
                   case "delete_visitors":
                        require(plugin_dir_path(__FILE__).'api.php');
                        $page=1;
                        if ($_POST['page']) { 
                           if (!empty($_POST['page'])){
                               $page = $_POST['page'];
                           }
                        }
                        $per_page=50;
                        if ($_POST['per_page']) { 
                           if (!empty($_POST['per_page'])){
                               $per_page = $_POST['per_page'];
                           }
                        }
                        $delete_list = '';
                        if ($_POST['list']) { 
                           if (!empty($_POST['list'])){
                               $delete_list = $_POST['list'];
                           }
                        }
                        wp_send_json_success(IP_APPROVAL_API::ip_visitors($page, $per_page, $delete_list));
                     break;
                   case "sys_info":
                       require(plugin_dir_path(__FILE__).'api.php');
                       require(plugin_dir_path(__FILE__).'sys_info_class.php');
                       $get_all_html = '';
                       $get_all_raw = '';
                       $ip_server_info = new IPServerInfo();
                       $get_all_html .= $ip_server_info->get_html_results();
                       $get_all_raw .= $ip_server_info->get_raw_results();

                       $ip_wp_info = new IPWordPressInfo();
                       $get_all_html .= $ip_wp_info->get_html_results();
                       $get_all_raw .= $ip_wp_info->get_raw_results();

                       $ip_user_info = new IPUserInfo();
                       $get_all_html .= $ip_user_info->get_html_results();
                       $get_all_raw .= $ip_user_info->get_raw_results();

                       $ip_theme_info = new IPThemeInfo();
                       $get_all_html .= $ip_theme_info->get_html_results();
                       $get_all_raw .= $ip_theme_info->get_raw_results();

                       $ip_plugin_info = new IPPluginInfo();
                       $get_all_html .= $ip_plugin_info->get_html_results();
                       $get_all_raw .= $ip_plugin_info->get_raw_results();

                       $ip_wp_plugins = new IPWPPlugins();
                       $get_all_html .= $ip_wp_plugins->get_html_results('plugins');
                       $get_all_raw .= $ip_wp_plugins->get_raw_results('plugins');

                       $ip_nwplugin_info = new IPNetworkPlugins();
                       if ($ip_nwplugin_info->is_enabled()) {
                           $get_all_html .= $ip_nwplugin_info->get_html_results('network_plugins');
                           $get_all_raw .= $ip_nwplugin_info->get_raw_results('network_plugins');
                       }

                       $ip_mu_info = new IPMUPlugins();
                       if ($ip_mu_info->is_enabled()) {
                           $get_all_html .= $ip_mu_info->get_html_results('mu_plugins');
                           $get_all_raw .= $ip_mu_info->get_raw_results('mu_plugins');
                       }
                       $response = array(
                          'html' => $get_all_html,
                          'raw' => $get_all_raw
                       );
                       wp_send_json_success($response);
                     break;
                   default:
                     // no action taken
                     exit();
               }
            }
        }
    }

    /*  IS IP ADMIN SETTINGS? ADD ADMIN FUNCTIONS?
    --------------------------------------------------------------------*/
    public function IsSettingsPage() {
        $is_ip_settings['query'] = false;
        $is_ip_settings = parse_url($_SERVER["REQUEST_URI"]);
        if (isset($is_ip_settings['query']) && $is_ip_settings['query'] === "page=".$this->plugin_name) {
            return true;
        }
        if (isset($is_ip_settings['query']) && $is_ip_settings['query'] === "page=".$this->plugin_name."&link=cred_check") {
            return true;
        }
        return false;
    }

}

$IP_APP_obj = new IP_APPROVAL_Plugin($ip_approval_plugin_name,$ip_approval_version,$ip_approval_pb);
if ($IP_APP_obj->IsSettingsPage()) {
    require(plugin_dir_path(__FILE__).'utils.php');
}
?>