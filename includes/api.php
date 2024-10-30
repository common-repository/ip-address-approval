<?php
if(!defined('ABSPATH')) {
    exit;
}
if(!class_exists('IP_APPROVAL_API')) {
class IP_APPROVAL_API {
    static $url = 'https://www.ip-approval.com/api/v1';

    /* Escape USER AGENT
    ------------------------------------------------------------------------*/
    public function NonEscapedEscape($str) {
        $re = '/(?<!\\\\)((?:\\\\\\\\)*)"/';
        $re2 = '/(?<!\\\\)((?:\\\\\\\\)*)\'/';
        $str = preg_replace($re, '$1\\"', $str);
        $str = preg_replace($re2, '$1\\\'', $str);
        return $str;
    }

    /* GET IP ADDRESS
    ------------------------------------------------------------------------*/
    public static function get_ip() {
       if (filter_var(getenv('HTTP_X_FORWARDED_FOR'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
           return getenv('HTTP_X_FORWARDED_FOR');
       }
       if (filter_var(getenv('REMOTE_ADDR'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
           return getenv('REMOTE_ADDR');
       }
       return $_SERVER['REMOTE_ADDR'];
    }

    /* GET VISITORS
    ------------------------------------------------------------------------*/
    public static function ip_visitors($page, $per_page, $delete_list = false) {
       $endpoint = '/wordpress/ip/wp_ip_visitors';
       $args = self::userinfo();
       $args['body']['page'] = $page;
       $args['body']['per_page'] = $per_page;
       if ($delete_list !== false) {
           $args['body']['list'] = $delete_list;
       }
       $communicate = self::communicate($endpoint, $args);
       return $communicate;
    }

    /* IP CHECKER
    ------------------------------------------------------------------------*/
    public static function ip_checker($ip_page_uri, $ip_page_url, $is_login = false) {
       $endpoint = '/wordpress/ip/wp_ip_checker';
       $args = self::userinfo();
       $args['body']['page_uri'] = $ip_page_uri;
       $args['body']['page_url'] = $ip_page_url;
       if ($is_login === true) {
           $args['body']['is_login'] = true;
       }
       $communicate = self::communicate($endpoint, $args);
       return $communicate;
    }

    /* GET DATA
    ------------------------------------------------------------------------*/
    public static function get_remote_data() {
       $endpoint = '/wordpress/ip/wp_ip_get';
       $args = self::userinfo();
       $communicate = self::communicate($endpoint, $args);
       return $communicate;
    }

    /* POST DATA
    ------------------------------------------------------------------------*/
    public static function post_remote_data($enabled, $pages_checked, $post_checked, $login_checked, $open_closed, $allowed, $allowed_url, $banned, $banned_url, $proxy, $proxy_url, $hosting, $hosting_url, $donotlog) {
       $endpoint = '/wordpress/ip/wp_ip_post';
       $args = self::userinfo();
       $args['body']['enabled'] = $enabled;
       $args['body']['pages_checked'] = $pages_checked;
       $args['body']['post_checked'] = $post_checked;
       $args['body']['login_checked'] = $login_checked;
       $args['body']['open_closed'] = $open_closed;
       $args['body']['allowed'] = $allowed;
       $args['body']['allowed_url'] = $allowed_url;
       $args['body']['banned'] = $banned;
       $args['body']['banned_url'] = $banned_url;
       $args['body']['proxy'] = $proxy;
       $args['body']['proxy_url'] = $proxy_url;
       $args['body']['hosting'] = $hosting;
       $args['body']['hosting_url'] = $hosting_url;
       $args['body']['donotlog'] = $donotlog;
       $communicate = self::communicate($endpoint, $args);
       return $communicate;
    }

    /* CHECK SUBSCRIPTION
    ------------------------------------------------------------------------*/
    public static function ip_lc() {
       $endpoint = '/wordpress/ip/wp_ip_lc';
       $args = self::userinfo();
       $communicate = self::communicate($endpoint, $args);
       return $communicate;
    }

    /* CHECK CREDENTIALS
    ------------------------------------------------------------------------*/
    public static function ip_cred() {
       $endpoint = '/wordpress/ip/wp_ip_cred';
       $args = self::userinfo();
       $communicate = self::communicate($endpoint, $args);
       return $communicate;
    }

    /* CONNECT ACCOUNT
    ------------------------------------------------------------------------*/
    public static function connect($api_key, $api_secret, $ip_approval_ip_id, $ip_approval_site_id, $deactivate = false) {
       $endpoint = '/wordpress/ip/wp_ip_connect';
       $args = self::userinfo($api_key, $api_secret, $ip_approval_ip_id, $ip_approval_site_id, $deactivate);
       $communicate = self::communicate($endpoint, $args);
       return $communicate;
    }

    /* API CALL
    ------------------------------------------------------------------------*/
    private static function communicate($endpoint, $args) {
        $options = array(
            'timeout' => $args['timeout'],
            'connect_timeout' => $args['connect_timeout'],
            'useragent' => $args['body']['useragent'],
            'blocking' => $args['blocking']
        );

        try {
            $response = WpOrg\Requests\Requests::request(self::$url.$endpoint,$args['headers'],$args['body'],$args['method'],$options);
        } catch (WpOrg\Requests\Exception $e) {
            $remote_query = "Error: {$e->getMessage()}";
            return $remote_query;
        }

        if ($response->status_code !== 200 && $response->status_code !== 401) {
            $parts = explode("\n", $response->raw);
            $remote_query = "{$response->status_code}: {$parts['0']}";
            return $remote_query;
        }
        else {
            $remote_query = (object) json_decode($response->body, true);
            return $remote_query;
        }
        return false;
    }

    /* API USER INFO
    ------------------------------------------------------------------------*/
    private static function userinfo($api_key = false, $api_secret = false, $ip_approval_ip_id = false, $ip_approval_site_id = false, $deactivate = false) {
        $IP = self::get_ip();
        $password = 'not_needed';

        $user_agent = 'User Agent Not Provided';
        if (isset($_SERVER["HTTP_USER_AGENT"])){
            $user_agent = ! empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'User Agent Not Provided';
        }
        $user_agent = (new IP_APPROVAL_API)->NonEscapedEscape($user_agent);

        $wp_version = get_bloginfo('version');
        global $ip_approval_version;
        global $ip_approval_plugin_name;
        $BlogOwner = get_users('role=Administrator');
        $user_id = $BlogOwner['0']->ID;
        $blog_id = get_current_blog_id();
        $name = '';
        if (!empty($BlogOwner['0']->display_name)) {
            $name = $BlogOwner['0']->display_name;
        }
        $user_login = '';
        if (!empty($BlogOwner['0']->user_login)) {
            $user_login = $BlogOwner['0']->user_login;
        }
        $user_email = '';
        if (!empty($BlogOwner['0']->user_email)) {
            $user_email = $BlogOwner['0']->user_email;
        }
        $current_theme = wp_get_theme();
        $current_theme_ = esc_html($current_theme->get('TextDomain'));
        if (empty($current_theme_)) {
            $current_theme_ = esc_html($current_theme->get('Name'));
        }
        if (empty($current_theme_)) {
            $current_theme_ = 'Theme Name Not Provided';
        }
        $current_domain = self::clean_basedomain();

        $timeout = 2;
        $connect_timeout = 1;

        $body = array(
           'blog_id' => $blog_id,
           'user_id' => $user_id,
           'name' => $name,
           'user_name' => $user_login,
           'user_email' => $user_email,
           'ip' => $IP,
           'useragent' => $user_agent,
           'domain_url' => $current_domain,
           'current_theme' => $current_theme_,
           'version' => $ip_approval_version,
           'wp_version' => $wp_version
        );

        $ip_approval_vals = get_option($ip_approval_plugin_name);
        $body['api_key'] = $ip_approval_vals['api_key'];
        if ($api_key !== false) {
            $body['api_key'] = $api_key;
        }

        $body['api_secret'] = $ip_approval_vals['api_secret'];
        if ($api_secret !== false) {
            $body['api_secret'] = $api_secret;
        }

        $body['ip_id'] = $ip_approval_vals['ip_id'];
        if ($ip_approval_ip_id !== false) {
            $body['ip_id'] = $ip_approval_ip_id;
        }

        $body['site_id'] = $ip_approval_vals['ip_site_id'];
        if ($ip_approval_site_id !== false) {
            $body['site_id'] = $ip_approval_site_id;
        }

        if ($deactivate === true) {
            $body['deactivate'] = true;
        }

        $args = array(
           'body' => $body,
           'headers' => array(
              'Authorization' => 'Basic '.base64_encode($user_login.':'.$password),
           ),
           'method' => 'GET',
           'useragent' => $user_agent,
           'timeout' => $timeout,
           'connect_timeout' => $connect_timeout,
           'redirection' => '2',
           'httpversion' => '1.0',
           'blocking' => true
        );
        return $args;
    }

    /* GET DOMAIN URL
    ------------------------------------------------------------------------*/
    private static function clean_basedomain() {
        $domain = preg_replace('|https?://|', '', site_url());
        if ($slash = strpos($domain, '/')) {
            $domain = substr( $domain, 0, $slash );
        }
        return $domain;
    }
}
}
?>