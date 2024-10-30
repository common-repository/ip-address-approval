<?php
if(!defined('ABSPATH')) {
    exit;
}

class IP_APPROVAL_Utils {
    public $plugin_name;
    public $version;
    public $manage_options;
    public $api_file_exists = null;
    public $filepath = '';
    public $is_ip_id = false;
    public $is_api_key = false;
    public $is_api_secret = false;
    private $has_ug = array();
    private $options = null;
    private $settings = null;
    private $api_details = null;
    private $api_note = null;
    private $api_message;
    function __construct($name,$ver) {
        $this->plugin_name = $name;
        $this->version = $ver;
        $this->api_details = __('The IP Address Approval API file does not exist. Please visit the <a href="plugins.php">WP Admin Plugin Page</a> then uninstall and then reinstall the plugin, to continue.', 'ip-address-approval');
        $this->api_note = __('Note: This file is required to use the IP Address Approval Service.', 'ip-address-approval');
        $this->api_message = '<div class="ip_notice"><p>'.$this->api_details.' <BR>'.$this->api_note.'</p></div>';
        $object = new stdClass();
        $this->settings = $object;
        $this->api_file_exists();
        $this->autoload();
    }

    /*   CHECK FOR API
    --------------------------------------------------------------------*/
    public function api_file_exists() {
        if (is_null($this->api_file_exists)) {
          $this->filepath = plugin_dir_path(__FILE__).'api.php';
          if (file_exists($this->filepath)) {
              $this->api_file_exists = true;
              $this->api_message = '';
              return true;
          }
          $this->api_file_exists = false;
          return false;
        }
        return $this->api_file_exists;
    }


    /*   AUTOLOAD
    --------------------------------------------------------------------*/
    private function autoload() {
        add_action('admin_bar_menu', [$this, 'ip_approval_custom_adminbar'], 99999);
        add_action('admin_init', [$this, 'ip_approval_css_and_js']);
    }

    /*   AUTOLOAD SAVE CHANGES Button
    --------------------------------------------------------------------*/
    public function ip_approval_custom_adminbar($wp_admin_bar){
        $args = array(
           'id' => 'custom-button',
           'title' => __('Save Changes', 'ip-address-approval'),
           'href' => '#',
           'meta' => array(
           'html'     => '<span class="ip-save" title="'.__('Save Changes', 'ip-address-approval').'"></span>',
           'class' => 'custom-ip-save'
        ));
        $wp_admin_bar->add_node($args);
    }

    /*   AUTOLOAD CSS and JS
    --------------------------------------------------------------------*/
    public function ip_approval_css_and_js() {
        $deps = array();
        $fa_ver = '4.7.0';
        $in_footer = false;

        wp_register_style(
          'ip_approval_fa_css',
          'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css',
          $deps,
          $fa_ver,
          $in_footer
        );
        wp_register_style(
          'ip_approval_css',
          plugin_dir_url(__FILE__).'../assets/css/ip-approval-css.css',
          $deps,
          $this->version,
          $in_footer
        );
        wp_register_script(
          'ip_approval_filter_js',
          plugin_dir_url(__FILE__).'../assets/js/jquery.filtertable.min.js',
          $deps,
          $this->version,
          $in_footer
        );
        wp_register_script('ip_approval_sort_js',
          plugin_dir_url(__FILE__).'../assets/js/jquery.tablesorter.min.js',
          $deps,
          $this->version,
          $in_footer
        );
        wp_register_script(
          'ip_approval_func_js',
          plugin_dir_url(__FILE__).'../assets/js/ip-approval-js.js',
          $deps,
          $this->version,
          $in_footer
        );

        wp_enqueue_style('ip_approval_fa_css');
        wp_enqueue_style('ip_approval_css');
        wp_enqueue_script('ip_approval_filter_js');
        wp_enqueue_script('ip_approval_sort_js');
        wp_enqueue_script('ip_approval_func_js');
    }

    /*   GET Options
    --------------------------------------------------------------------*/
    public function get_options($refresh = '') {
        if (is_null($this->options) || $refresh === 'refresh') {
            $this->options = get_option($this->plugin_name);
            $this->is_api_key = (!empty($this->options['api_key']) || $this->options['api_key'] !== '0');
            $this->is_api_secret = (!empty($this->options['api_secret']) || $this->options['api_secret'] !== '0');
            $this->is_ip_id = (!empty($this->options['ip_id']) AND $this->options['ip_id'] !== '0');
        }
        return $this->options;
    }

    /*   LOAD Content
    --------------------------------------------------------------------*/
    public function load() {
        $this->manage_options = current_user_can('manage_options');
        // Set Options + IF IP Approval options have not been set, re-set them
        if ($this->get_options() === false) {
            $this->restore_config('reset');
        }
        // Check Version
        if (version_compare($this->options['version'], $this->version, '<')) {
            $this->options['version'] = $this->version;
            update_option($this->plugin_name, $this->options);
        }
        if ($this->api_file_exists) {
            $this->optionStatus();
        }
        $this->loadContent();
    }

    /*   LOAD HTML
    --------------------------------------------------------------------*/
    private function loadContent() {
        require(plugin_dir_path(__FILE__).'html/base.php');
    }

    /*   LOAD Check Options, Save and Connect
    --------------------------------------------------------------------*/
    private function optionStatus() {
        // CLEAR = Reset Values
        if (!empty($_POST['clear'])) {
            $this->restore_config('reset');
            $this->notice('success', 'The WordPress Site Settings have been reset to their default values.');
        }

        // Save Values
        if (!empty($_POST['save'])) {
            // Check nonce and user ability
            if (check_admin_referer('ip_approval-submit', 'ip_approval_nonce') && $this->manage_options) {
                // Validate & Sanitize
                $this->options['enabled'] = sanitize_text_field($_POST['ip_approval_enabled']);
                $this->options['pagesall'] = isset($_POST['ippagesall']) ? 1 : 0;
                $this->options['posts'] = isset($_POST['ipposts']) ? 1 : 0;
                $this->options['login_form'] = isset($_POST['iplogin_form']) ? 1 : 0;
                unset($this->options['pages']);
                $ippagescheckboxes = isset($_POST['ippages']) ? $_POST['ippages'] : array();
                $ip_post_name = array();
                foreach ( $ippagescheckboxes as $ippageskey => $ippagesvalue ) {
                   $this->options['pages'][$ippageskey] = $ippagesvalue;
                   $post_name = get_post($ippagesvalue); 
                   $ip_post_name[] = $post_name->post_name;
                }
                $ip_post_name_string = '';
                if (!empty($ip_post_name)){
                    $ip_post_name_string .= implode(",",$ip_post_name);
                }
                if (!$ippagescheckboxes) {
                    $this->options['pages'] = array();
                }

                $this->settings->enabled = sanitize_text_field($this->options['enabled']);
                $this->settings->pages_checked = $ip_post_name_string;
                $this->settings->post_checked = $this->options['posts'];
                $this->settings->login_checked = $this->options['login_form'];
                $this->settings->open_closed = sanitize_text_field($_POST['ip_approval_o_c_unit']);
                $this->settings->allowed = sanitize_textarea_field($_POST['ip_approval_allowed_unit']);
                $this->settings->allowed_url = sanitize_text_field($_POST['ip_approval_allowed_url_unit']);
                $this->settings->banned = sanitize_textarea_field($_POST['ip_approval_banned_unit']);
                $this->settings->banned_url = sanitize_text_field($_POST['ip_approval_banned_url_unit']);
                $this->settings->proxy = isset($_POST['ip_approval_proxy_unit']) ? '1' : '0';
                $this->settings->proxy_url = sanitize_text_field($_POST['ip_approval_proxy_url_unit']);
                $this->settings->hosting = isset($_POST['ip_approval_hosting_unit']) ? '1' : '0';
                $this->settings->hosting_url = sanitize_text_field($_POST['ip_approval_hosting_url_unit']);
                if (isset($_POST['ip_approval_donotlog_unit'])) {
                    $this->settings->donotlog = sanitize_textarea_field($_POST['ip_approval_donotlog_unit']);
                }
                if ($this->is_api_key AND $this->is_api_secret) {
                    if ($this->is_ip_id) {
                           $remote_query_results = IP_APPROVAL_API::post_remote_data(
                                $this->settings->enabled,
                                $this->settings->pages_checked,
                                $this->settings->post_checked,
                                $this->settings->login_checked,
                                $this->settings->open_closed,
                                $this->settings->allowed,
                                $this->settings->allowed_url,
                                $this->settings->banned,
                                $this->settings->banned_url,
                                $this->settings->proxy,
                                $this->settings->proxy_url,
                                $this->settings->hosting,
                                $this->settings->hosting_url,
                                $this->settings->donotlog
                           );
                           if (is_object($remote_query_results) && $remote_query_results->status == "Success") {
                               $this->options['has_ug'] = $remote_query_results->upgrade;
                               if ($this->options['has_ug'] == 0) { $this->options['login_form'] = 0; }
                               update_option($this->plugin_name, $this->options);
                               $this->notice('success', $remote_query_results->message);
                           }
                           else {
                              if (is_object($remote_query_results) && property_exists($remote_query_results, "message")) {
                                  $this->notice('error', $remote_query_results->message);
                              }
                              else {
                                  $this->notice('error', 'An API error occurred.');
                              }
                           }
                           $this->ug_active($this->options['has_ug']);
                    }
                } // END if API KEY
            }
        }
        else {
            if ($this->is_ip_id) {
                $get_site_info = IP_APPROVAL_API::get_remote_data();
                if (is_object($get_site_info)) {
                   if (property_exists($get_site_info, "message") && $get_site_info->status == "Failed") {
                       $this->notice('error', $get_site_info->message);
                   }
                   $this->options['has_ug'] = $get_site_info->upgrade;
                   if ($this->options['has_ug'] == 0) { $this->options['login_form'] = 0; }
                   update_option($this->plugin_name, $this->options);
                   $this->get_options('refresh');
                   $this->settings->open_closed = $get_site_info->open_closed;
                   $this->settings->allowed = $get_site_info->allowed;
                   $this->settings->allowed_url = $get_site_info->allowed_url;
                   $this->settings->banned = $get_site_info->banned;
                   $this->settings->banned_url = $get_site_info->banned_url;
                   $this->settings->proxy = $get_site_info->proxy;
                   $this->settings->proxy_url = $get_site_info->proxy_url;
                   $this->settings->hosting = $get_site_info->hosting;
                   $this->settings->hosting_url = $get_site_info->hosting_url;
                   $this->settings->donotlog = $get_site_info->donotlog;
                }
                else {
                   $this->settings->open_closed = 'Error';
                   $this->settings->allowed = 'Error';
                   $this->settings->allowed_url = 'Error';
                   $this->settings->banned = 'Error';
                   $this->settings->banned_url = 'Error';
                   $this->settings->proxy = 'Error';
                   $this->settings->proxy_url = 'Error';
                   $this->settings->hosting = 'Error';
                   $this->settings->hosting_url = 'Error';
                   $this->settings->donotlog = 'Error';
                }
             $this->ug_active($this->options['has_ug']);
            }
        }

        if (isset($_POST['log_user_info_'])) {
          if (check_admin_referer('ip_approval-log_user_info_', 'ip_approval_log_user_info_nonce') && $this->manage_options ) {
                    $p_ip_approval_ip_id = sanitize_text_field($_POST['ip_approval_ip_id_input']);
                    $p_ip_approval_site_id = sanitize_text_field($_POST['ip_approval_site_id_input']);
                    $p_api_key = sanitize_text_field($_POST['ip_approval_api_key_input']);
                    $p_api_secret = sanitize_text_field($_POST['ip_approval_api_secret_input']);
                   if ($_POST['log_user_info_'] === 'Connect Account' OR $_POST['log_user_info_'] === 'RE-Connect Account') {
                       $remote_query_results = IP_APPROVAL_API::connect($p_api_key, $p_api_secret, $p_ip_approval_ip_id, $p_ip_approval_site_id);
                       if (is_object($remote_query_results) && $remote_query_results->status == "Success") {
                           $this->options['has_ug'] = $remote_query_results->upgrade;
                           $this->options['ip_id'] = $p_ip_approval_ip_id;
                           $this->options['ip_site_id'] = $remote_query_results->site_id;
                           $this->options['api_key'] = $p_api_key;
                           $this->options['api_secret'] = $p_api_secret;
                           $this->add_access_banned_page_init();
                           $this->options['first_run'] = 1;

                           $this->options['cred_check_status'] = 'Success';
                           $this->options['cred_check_date'] = date('m/d/Y', strtotime("1 days"));
                           $this->options['cred_check_disabled'] = date('m/d/Y', strtotime("+5 days"));

                           update_option($this->plugin_name, $this->options);
                           $this->get_options('refresh');
                           $this->ug_active($this->options['has_ug']);
                           if ($this->is_ip_id) {
                               $get_site_info = IP_APPROVAL_API::get_remote_data();
                               if (is_object($get_site_info)) {
                                  if (property_exists($get_site_info, "message") && $get_site_info->status == "Failed") {
                                      $this->notice('error', $get_site_info->message);
                                  }
                                  $this->options['has_ug'] = $get_site_info->upgrade;
                                  update_option($this->plugin_name, $this->options);
                                  $this->get_options('refresh');
                                  $this->settings->open_closed = $get_site_info->open_closed;
                                  $this->settings->allowed = $get_site_info->allowed;
                                  $this->settings->allowed_url = $get_site_info->allowed_url;
                                  $this->settings->banned = $get_site_info->banned;
                                  $this->settings->banned_url = $get_site_info->banned_url;
                                  $this->settings->proxy = $get_site_info->proxy;
                                  $this->settings->proxy_url = $get_site_info->proxy_url;
                                  $this->settings->hosting = $get_site_info->hosting;
                                  $this->settings->hosting_url = $get_site_info->hosting_url;
                               }
                               else {
                                  $this->settings->open_closed = 'Error';
                                  $this->settings->allowed = 'Error';
                                  $this->settings->allowed_url = 'Error';
                                  $this->settings->banned = 'Error';
                                  $this->settings->banned_url = 'Error';
                                  $this->settings->proxy = 'Error';
                                  $this->settings->proxy_url = 'Error';
                                  $this->settings->hosting = 'Error';
                                  $this->settings->hosting_url = 'Error';
                               }
                           }
                           $this->notice('success', $remote_query_results->message);
                       }
                       else {
                          if (is_object($remote_query_results) && property_exists($remote_query_results, "message")) {
                              $this->notice('error', $remote_query_results->message);
                          }
                          else {
                              $this->notice('error', 'An API error occurred.');
                          }                
                       }
                   } // END Connect Account
                   if ($_POST['log_user_info_'] === 'Deactivate') {
                       $deactivate = true;
                       $remote_query_results = IP_APPROVAL_API::connect($p_api_key, $p_api_secret, $p_ip_approval_ip_id, $p_ip_approval_site_id, $deactivate);
                       if (is_object($remote_query_results) && $remote_query_results->status == "Success") {
                           $this->restore_config('deactivate');
                           $this->notice('success', 'Account Deactivated Successfully.');
                       }
                       else {
                           $this->restore_config('deactivate');
                           if (is_object($remote_query_results) && property_exists($remote_query_results, "message")) {
                               $this->notice('error', $remote_query_results->message);
                           }
                           else {
                               $this->notice('error', 'An API error occurred.');
                           }
                       }
                   } // END Deactivate
          }
          echo '<script type="text/javascript">';
          echo 'jQuery(document).ready(function () {';
          echo '    jQuery("a#ip_approval-tab_ip_id").click(); jQuery(window).scrollTop(0);';
          echo '});';
          echo '</script>';
        }
    }

    /* ADMIN NOTICE
    --------------------------------------------------------------------*/
    public function notice($args, $message) {
        // $args = info, success, error, warning
        $message = __($message, 'ip-address-approval');
        echo '<div id="message" class="notice notice-'.esc_attr($args).' is-dismissible"><p>'.$message.'</p></div>';
    }

    /* CHECK/ADD ACTIVE CLASS - ECHO 'active' if true 
    --------------------------------------------------------------------*/
    public function active_class($classes, $item) {
        if ($classes === $item){
            echo 'active';
        }
    }

    /* SET UG VALUES 
    --------------------------------------------------------------------*/
    public function ug_active($ug) {
        if ($ug > 0){
            $has_ug_v['0'] = '';
            $has_ug_v['1'] = '';
            $has_ug_v['2'] = '';
            $this->has_ug = $has_ug_v;
            return true; 
        }
        $ip_paid_sub_req = __('Paid Subscription Required', 'ip-address-approval');
        $has_ug_v['0'] = 'readonly title="'.$ip_paid_sub_req.'"';
        $has_ug_v['1'] = '<span class="ip_warning_span" title="'.$ip_paid_sub_req.'"></span>';
        $has_ug_v['2'] = '<span style="color: #c56464;">('.$ip_paid_sub_req.')</span>';
        $this->has_ug = $has_ug_v;
        return true;
    }

    /* Restore built-in defaults, overwriting existing values
    --------------------------------------------------------------------*/
    public function restore_config($type=false) {
        // Make sure the current user can manage options
        if (!$this->manage_options) {
            return;
        }

        if (empty($this->options) || 'reset' == $type || 'deactivate' == $type) {
            // Initialize option values
            $ip_id =  '0';
            $ip_site_id =  '0';
            $api_key =  '0';
            $api_secret =  '0';
            $has_ug =  '0';
            $cred_check_status = '';
            $cred_check_date = date('m/d/Y', strtotime("1 days"));
            $cred_check_disabled = date('m/d/Y', strtotime("+5 days"));
            if (!empty($this->options) && 'deactivate' !== $type) {
                $ip_id =  $this->options['ip_id'];
                $ip_site_id =  $this->options['ip_site_id'];
                $api_key =  $this->options['api_key'];
                $api_secret =  $this->options['api_secret'];
                $has_ug =  $this->options['has_ug'];
                $cred_check_status = $this->options['cred_check_status'];
                $cred_check_date = $this->options['cred_check_date'];
                $cred_check_disabled = $this->options['cred_check_disabled'];
            }

            $ip_pages_reset = array();
            $ip_get_pages_reset = get_pages();
            foreach ( $ip_get_pages_reset as $ip_page ) {
                if ($ip_page->post_title !== 'Access Denied' AND $ip_page->post_title !== 'Banned') {
                    $ip_pages_reset[] = $ip_page->ID;
                }
            }


            $ip_sections = array();
            $ip_sections['1'] = 'no';
            $ip_sections['2'] = 'no';

            $ip_approval_default_vals = array(
                'version'       => $this->version,
                'first_run'     => 1,
                'ip_site_id'    => $ip_site_id,
                'ip_id'         => $ip_id,
                'api_key'       => $api_key,
                'api_secret'    => $api_secret,
                'has_ug'        => $has_ug,
                'login_form'    => 0,
                'enabled'       => 'disabled',
                'posts'         => 1,
                'pagesall'      => 1,
                'pages'         => $ip_pages_reset,
                'cred_check_status'   => $cred_check_status,
                'cred_check_date'     => $cred_check_date,
                'cred_check_disabled' => $cred_check_disabled,
                'sections'      => $ip_sections
            );
            $this->options = $ip_approval_default_vals;
        }
        update_option($this->plugin_name, $this->options);
        $this->get_options('refresh');
    }

    /* Add Access Denied and Banned Pages
    --------------------------------------------------------------------*/
    public function add_access_banned_page_init() {
        $allow_post_title = 'Access Denied';
        $allow_post_name = 'access-denied';
        $banned_post_title = 'Banned';
        $banned_post_name = 'banned';
        $ip_access = true;
        $ip_banned = true;
        $ip_pages = get_pages();
        foreach ($ip_pages as $ip_page) {
           if ($ip_page->post_title === $allow_post_title OR $ip_page->post_name === $allow_post_name) {
               $ip_access = false;
           }
           if ($ip_page->post_title === $banned_post_title OR $ip_page->post_name === $banned_post_name) {
               $ip_banned = false;
           }
        }
        if ($ip_access) {
            $new_access_page_id = wp_insert_post(array(
                'post_title'     => $allow_post_title,
                'post_type'      => 'page',
                'post_name'      => $allow_post_name,
                'guid'           => '',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_content'   => '<!-- wp:heading {"align":"center"} --><h2 style="text-align:center">ACCESS DENIED</h2><!-- /wp:heading --><!-- wp:paragraph {"align":"center"} --><p style="text-align:center">Oops, looks like you do not have access to that page.<br></p><!-- /wp:paragraph -->',
                'post_status'    => 'publish',
                'post_author'    => get_user_by('id', 1)->ID,
                'menu_order'     => 0
            ));
            if ($new_access_page_id && ! is_wp_error($new_access_page_id)){
                update_post_meta($new_access_page_id, '_wp_page_template', 'template-blog.php' );
                $this->removePageFromMenus($new_access_page_id);
            }
        }
        if ($ip_banned) {
            $new_banned_page_id = wp_insert_post(array(
                'post_title'     => $banned_post_title,
                'post_type'      => 'page',
                'post_name'      => $banned_post_name,
                'guid'           => '',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_content'   => '<!-- wp:heading {"align":"center"} --><h2 style="text-align:center">YOUR ACCESS HAS BEEN REVOKED</h2><!-- /wp:heading --><!-- wp:paragraph {"align":"center"} --><p style="text-align:center">Sorry, you do not have access to view that page.<br></p><!-- /wp:paragraph -->',
                'post_status'    => 'publish',
                'post_author'    => get_user_by('id', 1)->ID,
                'menu_order'     => 0
            ));
            if ($new_banned_page_id && ! is_wp_error($new_banned_page_id)){
                update_post_meta($new_banned_page_id, '_wp_page_template', 'template-blog.php');
                $this->removePageFromMenus($new_banned_page_id);
            }
        }
        if ($ip_banned AND $ip_access) {
            $this->notice('success', 'Successfully create an Access Denied and Banned page.');
        }
        else if ($ip_access) {
            $this->notice('success', 'Successfully create an Access Denied page.');
        }
        else if ($ip_banned) {
            $this->notice('success', 'Successfully create a Banned page.');
        }
    }
    private function removePageFromMenus($pageID=-1){
        if ($pageID > 0) {
            $locations = wp_get_nav_menus();
            if (is_array($locations)) foreach ($locations as $location => $menuID) {
                $this->rPFMnow($menuID,$pageID);
            }
            else {
                $this->rPFMnow($locations,$pageID);
            }
            return true;
        }
        return false;
    }
    private function rPFMnow($menuID=null,$pageID=-1) {
        if (!is_null($menuID) AND $pageID > 0) {
            $menu = wp_get_nav_menu_object($menuID);
            $pagesItem = wp_get_nav_menu_items($menu->term_id, array("object"=>"page"));
            if (is_array($pagesItem)) {
              foreach ($pagesItem as $page) {
                if ($page->object_id == $pageID) {
                    wp_delete_post($page->ID);
                }
              }
            }
        }
    }
}

$IP_APP_Auto = new IP_APPROVAL_Utils($ip_approval_plugin_name,$ip_approval_version);
if ($IP_APP_Auto->api_file_exists) {
    require($IP_APP_Auto->filepath);
}


/* Load Settings
--------------------------------------------------------------------*/
function ip_approval_settings() {
    global $IP_APP_Auto;
    $IP_APP_Auto->load();
}
?>