<?php
if(!defined('ABSPATH')) {
    exit;
}
/* IPAbstractClass + Other Classes for Server Info
------------------------------------------------------------------------*/
abstract class IPAbstractClass {
    public function get_raw_results($report_name = '') {
        $pass_raw = '';
        $tabs_count = 0;
        $tabs_count++;
        $required_plugins_properties = [
              'Name',
              'Version',
              'URL',
              'Author',
        ];
        $required_plugins_properties = array_flip($required_plugins_properties);
        unset($required_plugins_properties['Name']);
        $indent = str_repeat("\t", $tabs_count - 1);
        $is_plugins = in_array($report_name, [
            'plugins',
            'network_plugins',
            'mu_plugins',
        ]);
        if (!$is_plugins) {
            $pass_raw .= PHP_EOL . $indent . '== ' . $this->get_title() . ' ==';
        }

        $pass_raw .= PHP_EOL;

        foreach ($this->get_fields() as $field_name => $field) {
            $method = 'get_'.$field_name;
            if (!method_exists($this, $method)) {
                $method = 'get_' .$field_name;
                //fallback:
                if (!method_exists($this, $method)) {
                    return new \WP_Error(sprintf("Getter method for the field '%s' wasn't found in %s.", $field_name, get_called_class()));
                }
            }
            $getfield = $this->$method();
            $sub_indent = str_repeat("\t", $tabs_count);
            if ($is_plugins) {
                $pass_raw .= "== {$this->get_title()} ==" . PHP_EOL;
                foreach ($getfield['value'] as $plugin_info) {
                    $plugin_properties = array_intersect_key($plugin_info, $required_plugins_properties);
                    $pass_raw .= $sub_indent . $plugin_info['Name'];
                    foreach ($plugin_properties as $property_name => $property) {
                       $pass_raw .= PHP_EOL . "{$sub_indent}\t{$property_name}: {$property}";
                    }
                    $pass_raw .= PHP_EOL . PHP_EOL;
                }
            } else {
                if ($field_name === 'curl' || $field_name === 'IP_Plugin') {
                    $label = $field;
                    if (!empty($label)) {
                        $label .= ': ';
                    }

                    $curl_installed = is_array($getfield) ? '' : 'No';
                    $pass_raw .= $sub_indent.$label.$curl_installed.PHP_EOL;
                    foreach ($getfield['value'] as $fieldkey => $fieldvalue) {
                       if (in_array($fieldkey, ['protocols', 'sections', 'pages'], true)) {
                           if (is_array($fieldvalue)) {
                               $pass_raw .= "{$sub_indent}{$sub_indent}{$fieldkey}: ";
                               foreach ($fieldvalue as $vkey => $vvalue) {
                                    $pass_raw .= $vvalue.', ';
                               }
                               $pass_raw .= PHP_EOL;
                           }
                           else {
                               $pass_raw .= "{$sub_indent}{$sub_indent}{$fieldkey}: {$fieldvalue}" . PHP_EOL;
                           }
                       }
                       else {
                           $pass_raw .= "{$sub_indent}{$sub_indent}{$fieldkey}: {$fieldvalue}".PHP_EOL;
                       }
                    }
                }
                else {
                    $label = $field;
                    if (!empty($label)) {
                        $label .= ': ';
                    }
                    $pass_raw .= "{$sub_indent}{$label}{$getfield['value']}" . PHP_EOL;
                }
            }
        }

        if (!empty($report['sub'])) {
            $this->get_raw_results($report['sub'], $template, true);
        }
        $tabs_count--;
        return $pass_raw;
    }

    public function get_html_results($report_name = '') {
        $pass_html = '';
        $result = [];
        $pass_html .= '<table class="form-table system-info">';
          $pass_html .= '<tbody>';
            $pass_html .= '<tr valign="middle" class="ip-tr-colorbox"><th colspan="2"></th></tr>';
              $pass_html .= '<tr valign="top">';
                $pass_html .= '<th>'.$this->get_title().'</th>';
                $pass_html .= '<td>';
                  $pass_html .= '<table style="width: 100%;">';
                    $pass_html .= '<tbody>';

        foreach ($this->get_fields() as $field_name => $field_label) {
            $method = 'get_'.$field_name;

            if (!method_exists($this, $method)) {
                $method = 'get_' .$field_name;
                //fallback:
                if (!method_exists($this, $method)) {
                    return new \WP_Error(sprintf("Getter method for the field '%s' wasn't found in %s.", $field_name, get_called_class()));
                }
            }
            $field = $this->$method();

            if (in_array($report_name, ['plugins', 'network_plugins', 'mu_plugins'], true)) {
                foreach ($field['value'] as $plugin_info) {

                   $pass_html .= '<tr>';
                    $pass_html .= '<td>';
                      if ($plugin_info['PluginURI']) {
                          $plugin_name = "<a href='{$plugin_info['PluginURI']}' target='_blank'>{$plugin_info['Name']}</a>";
                      }
                      else {
                          $plugin_name = $plugin_info['Name'];
                      }

                      if ($plugin_info['Version']) {
                          $plugin_name .= ' - ' . $plugin_info['Version'];
                      }             
                  $pass_html .= $plugin_name;
                  $pass_html .= '</td>';
                  $pass_html .= '<td>';
                      if ($plugin_info['Author']) {
                        if ($plugin_info['AuthorURI']) {
                            $author = "<a href='{$plugin_info['AuthorURI']}' target='_blank'>{$plugin_info['Author']}</a>";
                        }
                        else {
                            $author = $plugin_info['Author'];
                        }
                        $pass_html .= "By $author";
                      }
                    $pass_html .= '</td>';
                    $pass_html .= '<td></td>';
                  $pass_html .= '</tr>';

                }
            }
            else {
                if ($field_name === 'curl' || $field_name === 'IP_Plugin') {
                    $warning_class = ! empty($field['warning']) ? ' class="ip-warning"' : '';
                    $log_label = ! empty($field_label) ? $field_label . ':' : '';
                    $pass_html .= '<tr'.$warning_class.'>';
                      $pass_html .= '<td>'.$log_label.'</td>';
                      $pass_html .= '<td>';
                      foreach ($field['value'] as $fieldkey => $fieldvalue) {
                        if (in_array($fieldkey, ['protocols', 'sections', 'pages'], true)) {
                            if (is_array($fieldvalue)) {
                                $pass_html .= $fieldkey.': ';
                                foreach ($fieldvalue as $vkey => $vvalue) {
                                         $pass_html .= $vvalue.', ';
                                }
                                $pass_html .= '<br>';
                            }
                            else {
                               $pass_html .= $fieldkey.': '.$fieldvalue.'<br>';
                            }
                        }
                        else {
                            $pass_html .= $fieldkey.': '.$fieldvalue.'<br>';
                        }
                      }
                      $pass_html .= '</td>';
                      $pass_html .= '<td>';
                        if (!empty($field['recommendation'])) {
                            $pass_html .= $field['recommendation'];
                        }
                      $pass_html .= '</td>';
                    $pass_html .= '</tr>';
                }
                else {
                    $warning_class = ! empty($field['warning']) ? ' class="ip-warning"' : '';
                    $log_label = ! empty($field_label) ? $field_label . ':' : '';
                    $pass_html .= '<tr'.$warning_class.'>';
                      $pass_html .= '<td>'.$log_label.'</td>';
                      $pass_html .= '<td>'.$field['value'].'</td>';
                      $pass_html .= '<td>';
                        if (!empty($field['recommendation'])) {
                            $pass_html .= $field['recommendation'];
                        }
                      $pass_html .= '</td>';
                    $pass_html .= '</tr>';
                }
            }

        }

                    $pass_html .= '</tbody>';
                  $pass_html .= '</table>';
                $pass_html .= '</td>';
              $pass_html .= '</tr>';
          $pass_html .= '</tbody>';
        $pass_html .= '</table>';
        return $pass_html;
    }
}

class IPUserInfo extends IPAbstractClass {
    private $user_info = null;
    function __construct() {
        if(is_null($this->user_info)) {
           $this->user_info = get_userdata(get_current_user_id());
        }
    }

    public function get_title() {
        return 'User';
    }

    public function get_fields() {
        return [
            'ID' => 'ID',
            'user_email' => 'Email',
            'user_login' => 'Username',
            'role' => 'Role',
            'locale' => 'WP Profile lang',
            'ip' => 'IP Address',
            'agent' => 'User Agent',
        ];
    }

    public function get_ID() {
        return [
           'value' =>  $this->user_info->data->ID,
        ];
    }

    public function get_user_email() {
        return [
           'value' =>  $this->user_info->data->user_email,
        ];
    }

    public function get_user_login() {
        return [
           'value' =>  $this->user_info->data->user_login,
        ];
    }

    public function get_role() {
        $role = null;
        if (!empty($this->user_info->roles)) {
            $role = $this->user_info->roles[0];
        }
        return [
           'value' => $role,
        ];
    }

    public function get_locale() {
        return [
            'value' => get_locale(),
        ];
    }

    public function get_ip() {
         return [
            'value' => IP_APPROVAL_API::get_ip(),
         ];
    }

    public function get_agent() {
         return [
            'value' => esc_html($_SERVER['HTTP_USER_AGENT']),
         ];
    }
}

class IPServerInfo extends IPAbstractClass {
    public function get_title() {
        return 'Server Environment';
    }

    public function get_fields() {
        return (object) [
            'os' => 'Operating System',
            'software' => 'Software',
            'mysql_version' => 'MySQL version',
            'php_version' => 'PHP Version',
            'php_sapi' => 'PHP Sapi',
            'php_max_input_vars' => 'PHP Max Input Vars',
            'php_max_post_size' => 'PHP Max Post Size',
            'max_execution_time' => 'Max Execution Time',
            'zip_installed' => 'ZIP Installed',
            'curl' => 'cURL Installed',
        ];
    }

    public function get_os() {
        return [
            'value' => PHP_OS,
        ];
    }

    public function get_software() {
        return [
            'value' => $_SERVER['SERVER_SOFTWARE'],
        ];
    }

    public function get_mysql_version() {
        global $wpdb;
        $db_server_version = $wpdb->get_results("SHOW VARIABLES WHERE `Variable_name` IN ('version_comment', 'innodb_version')", OBJECT_K);
        return [
            'value' => $db_server_version['version_comment']->Value . ' v' . $db_server_version['innodb_version']->Value,
        ];
    }

    public function get_php_version() {
        $result = [
            'value' => PHP_VERSION,
        ];
        if (version_compare($result['value'], '5.4', '<')) {
            $result['recommendation'] = _x('We recommend to use php 5.4 or higher', 'System Info', 'ip-address-approval');
            $result['warning'] = true;
        }
        return $result;
    }

    public function get_php_sapi() {
        return [
            'value' => php_sapi_name(),
        ];
    }

    public function get_php_max_input_vars() {
            return [
            'value' => ini_get('max_input_vars'),
        ];
    }

    public function get_php_max_post_size() {
        return [
            'value' => ini_get('post_max_size'),
        ];
    }

    public function get_max_execution_time() {
        return [
            'value' => ini_get('max_execution_time'),
        ];
    }

    public function get_zip_installed() {
        $zip_installed = extension_loaded('zip');
        return [
            'value' => $zip_installed ? 'Yes' : 'No',
            'warning' => ! $zip_installed,
        ];
    }

    public function get_curl() {
        $result = false;
        if (in_array('curl', get_loaded_extensions())) {
            return [
                'value' => curl_version(),
            ];
        }
        else {
            if (function_exists('curl_version')) {
                return [
                    'value' => curl_version(),
                ];
            }
            else {
                $result['recommendation'] = _x('cURL Must be Installed. If you are not sure how to make these changes, please contact your hosting provider.', 'System Info', 'ip-address-approval');
                $result['warning'] = true;
            }
        }
        return $result;
    }
}

class IPWordPressInfo extends IPAbstractClass {
    private $owner_info = null;
    function __construct() {
        if(is_null($this->owner_info)) {
           $BlogOwner = get_users('role=Administrator');
           $this->owner_info = $BlogOwner['0'];
        }
    }

    public function get_title() {
        return 'WordPress Environment';
    }

    public function get_fields() {
        return (object) [
            'version' => 'WP Version',
            'is_multisite' => 'WP Multisite',
            'memory_limit' => 'Memory limit',
            'debug_mode' => 'Debug Mode',
            'blog_id' => 'Blog ID',
            'site_url' => 'Site URL',
            'home_url' => 'Home URL',
            'permalink_structure' => 'Permalink Structure',
            'language' => 'Language',
            'timezone' => 'Timezone (or UTC number)',
            'admin_ID' => 'Admin ID',
            'admin_email' => 'Admin Email',
            'admin_login' => 'Admin Username',
        ];
    }

    public function get_version() {
        return [
            'value' => get_bloginfo('version'),
        ];
    }

    public function get_is_multisite() {
        return [
            'value' => is_multisite() ? 'Yes' : 'No',
        ];
    }

    public function get_memory_limit() {
        $result = [
            'value' => ini_get('memory_limit'),
        ];
        $min_recommended_memory = '64M';
        $memory_limit_bytes = wp_convert_hr_to_bytes($result['value']);
        $min_recommended_bytes = wp_convert_hr_to_bytes($min_recommended_memory);
        if ($memory_limit_bytes < $min_recommended_bytes) {
            $result['recommendation'] = sprintf(
            /* translators: 1: Minimum recommended_memory, 2: Codex URL */
            _x('We recommend setting memory to at least %1$s. For more information, read about <a href="%2$s" target="_blank">how to Increase memory allocated to PHP</a>.', 'System Info', 'ip-address-approval'),
                $min_recommended_memory,
                'https://wordpress.org/support/article/editing-wp-config-php/#increasing-memory-allocated-to-php'
            );
            $result['warning'] = true;
        }
        return $result;
    }

    public function get_debug_mode() {
        return [
            'value' => WP_DEBUG ? 'Active' : 'Inactive',
        ];
    }

    public function get_blog_id() {
        return [
            'value' => get_current_blog_id(),
        ];
    }

    public function get_site_url() {
        return [
            'value' => get_site_url(),
        ];
    }

    public function get_home_url() {
        return [
            'value' => get_home_url(),
        ];
    }

    public function get_permalink_structure() {
        global $wp_rewrite;
        $structure = $wp_rewrite->permalink_structure;
        if (!$structure) {
            $structure = 'Plain';
        }
        return [
            'value' => $structure,
        ];
    }

    public function get_language() {
        return [
            'value' => get_bloginfo('language'),
        ];
    }

    public function get_timezone() {
        $timezone = get_option('timezone_string');
        if (!$timezone) {
            $timezone = get_option('gmt_offset');
        }
        return [
            'value' => $timezone,
        ];
    }

    public function get_admin_ID() {
        $ID = '';
        if (!empty($this->owner_info->ID)) {
            $ID = $this->owner_info->ID;
        }
        return [
            'value' => '1',
        ];
    }

    public function get_admin_email() {
        $admin_email = '';
        if (!empty($this->owner_info->user_email)) {
            $admin_email = $this->owner_info->user_email;
        }
        return [
            'value' => $admin_email,
        ];
    }

    public function get_admin_login() {
        $admin_login = '';
        if (!empty($this->owner_info->user_login)) {
            $admin_login = $this->owner_info->user_login;
        }
        return [
            'value' => $admin_login,
        ];
    }
}

class IPThemeInfo extends IPAbstractClass {
    public $theme = null;
    public function get_title() {
        return 'Theme';
    }

    public function get_fields() {
        $fields = [
            'name' => 'Name',
            'version' => 'Version',
            'author' => 'Author',
            'is_child_theme' => 'Child Theme',
        ];
        if ($this->get_parent_theme()) {
            $parent_fields = [
                'parent_name' => 'Parent Theme Name',
                'parent_version' => 'Parent Theme Version',
                'parent_author' => 'Parent Theme Author',
            ];
            $fields = (object) array_merge($fields, $parent_fields);
        }
        return $fields;
    }

    public function get_parent_theme() {
        return $this->_get_theme()->parent();
    }

    public function _get_theme() {
        if (is_null($this->theme)) {
            $this->theme = wp_get_theme();
        }
        return $this->theme;
    }

    public function get_name() {
        return [
            'value' => $this->_get_theme()->get('Name'),
        ];
    }

    public function get_version() {
        return [
            'value' => $this->_get_theme()->get('Version'),
        ];
    }

    public function get_author() {
        return [
            'value' => $this->_get_theme()->get('Author'),
        ];
    }

    public function get_is_child_theme() {
        $is_child_theme = is_child_theme();
        $result = [
            'value' => $is_child_theme ? 'Yes' : 'No',
        ];
        if (!$is_child_theme) {
            $result['recommendation'] = sprintf(
            /* translators: %s: Codex URL */
            _x('Learn More at: <a href="%s" target="_blank">Child Themes</a>.', 'System Info', 'ip-address-approval'),
                'https://developer.wordpress.org/themes/advanced-topics/child-themes/'
            );
        }
        return $result;
    }

    public function get_parent_name() {
        return [
            'value' => $this->get_parent_theme()->get('Name'),
        ];
    }

    public function get_parent_version() {
        return [
            'value' => $this->get_parent_theme()->get('Version'),
        ];
    }

    public function get_parent_author() {
        return [
            'value' => $this->get_parent_theme()->get('Author'),
        ];
    }
}

class IPWPPlugins extends IPAbstractClass {
    public $plugins;
    public function get_title() {
        return 'Active Plugins';
    }

    public function get_fields() {
        return (object)[
            'active_plugins' => 'Active Plugins',
        ];
    }

    public function get_active_plugins() {
        return [
            'value' => $this->get_plugins(),
        ];
    }

    public function get_plugins() {
        if (!$this->plugins) {
            // Ensure get_plugins function is loaded
            if (!function_exists('get_plugins')) {
                include ABSPATH . '/wp-admin/includes/plugin.php';
            }
            $active_plugins = get_option('active_plugins');
            $this->plugins  = array_intersect_key(get_plugins(), array_flip($active_plugins));
        }
        return $this->plugins;
    }

    public function is_enabled() {
        return ! ! $this->get_plugins();
    }
}

class IPPluginInfo extends IPAbstractClass {
    public $plugin;
    public function get_title() {
        return 'IP Address Approval Plugin';
    }

    public function get_fields() {
        return (object)[
            'IP_Plugin' => 'Plugin Options',
        ];
    }

    public function get_IP_Plugin() {
        if ($this->is_enabled()) {
            $result = [
              'value' => $this->plugin,
            ];
        }
        else {
           $result['value'] = _x('Plugin Disabled?', 'System Info', 'ip-address-approval');
           $result['recommendation'] = _x('This should be displaying your IP Approval Options but WordPress sees the Plugin as disabled.', 'System Info', 'ip-address-approval');
            $result['warning'] = true;
        }
        return $result;
    }

    public function is_enabled() {
        return ! ! $this->get_plugin();
    }

    public function get_plugin() {
        if (!$this->plugin) {
            global $ip_approval_plugin_name;
            $this->plugin = get_option($ip_approval_plugin_name);
        }
        return $this->plugin;
    }
}

class IPNetworkPlugins extends IPAbstractClass {
    protected $plugins;
    public function get_title() {
        return 'Network Plugins';
    }

    public function get_fields() {
        return (object)[
            'network_active_plugins' => 'Network Plugins',
        ];
    }

    public function get_network_active_plugins() {
        return [
            'value' => $this->get_network_plugins(),
        ];
    }

    protected function get_network_plugins() {
        if (!$this->plugins) {
            $active_plugins = get_site_option('active_sitewide_plugins');
            if (!$active_plugins) {
                return false;
            }
            $this->plugins = array_intersect_key(get_plugins(), $active_plugins);
        }
        return $this->plugins;
    }

    public function is_enabled() {
        if (!is_multisite()) {
            return false;
        };
        return true;
    }
}

class IPMUPlugins extends IPAbstractClass {
    private $plugins;
    public function get_title() {
        return 'Must-Use Plugins';
    }

    public function get_fields() {
        return (object)[
            'must_use_plugins' => 'Must-Use Plugins',
        ];
    }

    public function get_must_use_plugins() {
        return [
            'value' => $this->get_mu_plugins(),
        ];
    }

    private function get_mu_plugins() {
        if (!$this->plugins) {
            $this->plugins = get_mu_plugins();
        }
        return $this->plugins;
    }

    public function is_enabled() {
        return ! ! $this->get_mu_plugins();
    }
}
?>