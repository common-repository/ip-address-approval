<?php
 if(!defined('ABSPATH')) {
    exit;
 }
?>

<?php if (empty($this->api_message)) { ?>
  <?php if ($this->is_api_key AND $this->is_api_secret) { ?>
    <?php if ($this->is_ip_id) { ?>
      <div class="ipwrap" id="ip_approval_settings">
          <h2 class="ip_approval-title">
              <?php echo __('IP Address Approval Settings', 'ip-address-approval'); ?>
              <small>ver. <?php echo $this->options['version']; ?></small>
          </h2>
          <form action="" method="post" id="ip_approval_options_form" name="ip_approval_options_form">
               <?php wp_nonce_field('ip_approval-submit', 'ip_approval_nonce'); ?>
                <table class="form-table">
                    <tbody>
                        <tr valign="middle" class="ip-tr-colorbox do-not-ip-fold">
                            <th colspan="2"></th>
                        </tr>
                        <tr class="ip-accordion" data="1" valign="top"><td><?php echo __('WORDPRESS SITE SETTINGS', 'ip-address-approval'); ?><span class="ip-dashicons-arrow-down mobile"></span></td><td><span class="ip-dashicons-arrow-down"></span></td></tr>

                        <tr class="ip-fold" data="1" valign="top" style="display: none;">
                            <?php $enabled = $this->options['enabled']; ?>
                            <th>Enable / Disable</th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                <div id="ip_approval_enabled_radio">
                                <input type="radio" name="ip_approval_enabled" id="ip_approval_enabled" value="enabled" <?php checked($enabled,'enabled'); ?>><label for="ip_approval_enabled"> <?php echo __('Enabled', 'ip-address-approval'); ?></label>
                                </div>
                                <div id="ip_approval_enabled_radio">
                                <input type="radio" name="ip_approval_enabled" id="ip_approval_disabled" value="disabled" <?php checked($enabled,'disabled'); ?>><label for="ip_approval_disabled"> <?php echo __('Disabled', 'ip-address-approval'); ?></label>
                                </div>
                                <p class="description"><?php echo __('This feature will enable or disable the IP Approval Service on your website.', 'ip-address-approval'); ?></p>
                                </div>
                            </td>
                        </tr>


                        <tr class="ip-fold" data="1" valign="top" style="display: none;">
                            <th><?php echo __('Standard Pages', 'ip-address-approval'); ?> </th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                   <input type="checkbox" name="ippagesall" id="ippagesall" <?php checked($this->options['pagesall'],1); ?>> <label class="ip-cursor-default" for="ippagesall"><?php echo __('Restrict Access on', 'ip-address-approval'); ?> <strong><?php echo __('ALL', 'ip-address-approval'); ?></strong> <?php echo __('Pages', 'ip-address-approval'); ?></label><br>
                                   <div class="scroll-box" style="margin-top: 5px;">
                                   <?php
                                    if (!empty($this->has_ug[0])) {
                                        echo $this->has_ug['1'].' ';
                                        echo $this->has_ug['2'].'<BR>';
                                    }
                                    $ip_pages = get_pages();
                                    foreach($ip_pages as $key => $val) {
                                        if ($val->post_title == 'Access Denied') {
                                            $item = $ip_pages[$key];
                                            unset($ip_pages[$key]);
                                            array_push($ip_pages, $item);
                                        }
                                        if ($val->post_title == 'Banned') {
                                            $item = $ip_pages[$key];
                                            unset($ip_pages[$key]);
                                            array_push($ip_pages, $item);
                                        }
                                    }
                                    foreach ($ip_pages as $ip_page) {
                                            $ip_option = '';
                                            if ($ip_page->post_title == 'Access Denied') {
                                                $ip_option .= '<BR>';
                                            }
                                            $ip_option .= '<input type="checkbox" name="ippages[]" value="';
                                            $ip_option .= $ip_page->ID;
                                            $ip_option .= '"';
                                            if (in_array($ip_page->ID, $this->options['pages'])) {
                                                $ip_option .= ' checked="checked"';
                                            }
                                            if ($ip_page->post_title == 'Access Denied') {
                                                $ip_option .= ' data-page-type="Access"';
                                            }
                                            if ($ip_page->post_title == 'Banned') {
                                                $ip_option .= ' data-page-type="Banned"';
                                            }
                                            $ip_option .= $this->has_ug['0'];
                                            $ip_option .= '> <label for="ippages">'. $ip_page->post_title;
                                            if ($ip_page->post_title == 'Access Denied' OR $ip_page->post_title == 'Banned') {
                                                $ip_option .= ' <span style="color: #c56464;font-size: 20px;line-height: 14px;top: 6px;position: relative;">*</span>';
                                            }
                                            $ip_option .= '</label><br>';
                                            echo $ip_option;

                                    }
                                   ?>
                                   </div>
                                   <p class="description"><?php echo __('Select the standard pages you want the IP Approval Service to be active on.', 'ip-address-approval'); ?><BR><span style="color: #c56464;">*<?php echo __('Checking the box for the Access Denied and Banned Page will only log visits. Note: You MUST also manually check or uncheck these boxes.', 'ip-address-approval'); ?></span></p>
                                </div>
                            </td>
                        </tr> 

                        <tr class="ip-fold" data="1" valign="top" style="display: none;">
                          <th><?php echo __('Post Pages', 'ip-address-approval'); ?></th>
                           <td>
                                <div style="margin: 0 0 0 10px;">
                                    <input type="checkbox" id="ipposts" name="ipposts" <?php checked($this->options['posts'],1); ?>> <label class="ip-cursor-default" for="ipposts"><?php echo __('Restrict Access on ALL', 'ip-address-approval'); ?> <strong><u><?php echo __('Post', 'ip-address-approval'); ?></u></strong> <?php echo __('Pages', 'ip-address-approval'); ?></label><br>
                                </div>
                           </td>
                        </tr>

                        <tr class="ip-fold" data="1" valign="top" style="display: none;">
                          <th><?php echo __('Admin Login Page', 'ip-address-approval'); ?> <?php echo $this->has_ug['1'];?></th>
                           <td>
                                <div style="margin: 0 0 0 10px;">
                                    <input type="checkbox" id="iplogin_form" name="iplogin_form" <?php checked($this->options['login_form'],1); ?> <?php echo $this->has_ug['0'];?>> <label class="ip-cursor-default" for="iplogin_form"><?php echo __('Restrict Access on the Admin', 'ip-address-approval'); ?> <strong><u><?php echo __('Login', 'ip-address-approval'); ?></u></strong> <?php echo __('Page', 'ip-address-approval'); ?></label> <?php echo $this->has_ug['2'];?><br>
                                </div>
                           </td>
                        </tr>

                        <tr class="ip-accordion" data="2" valign="top"><td><?php echo __('IP APPROVAL SETTINGS', 'ip-address-approval'); ?><span class="ip-dashicons-arrow-down mobile"></span></td><td><span class="ip-dashicons-arrow-down"></span></td></tr>

                        <tr class="ip-fold" data="2" valign="top" style="display: none;">
                            <th>Open or Closed <?php echo $this->has_ug['1'];?></th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                <label for="ip_approval_o_c_unit"><?php echo __('The website is', 'ip-address-approval'); ?>:</label>
                                <input type="hidden" name="ip_approval_o_c_unit" value="<?php esc_attr_e($this->settings->open_closed); ?>" id="ip_approval_o_c_unit">
                                <ul class="font-size"> 
                                   <li id="Open" name="ip_approval_o_c_unit" class="<?php $this->active_class($this->settings->open_closed,'Open'); ?>"><?php echo __('Open', 'ip-address-approval'); ?></li>
                                   <li id="Closed" name="ip_approval_o_c_unit" class="<?php $this->active_class($this->settings->open_closed,'Closed'); ?>" <?php echo $this->has_ug['0'];?>><?php echo __('Closed', 'ip-address-approval'); ?></li>
                                </ul>
                                <br /><br />
                                <p class="description"><?php echo __('Use this feature to open or close your website.', 'ip-address-approval'); ?><br /><?php echo __('Note: If your website is "Closed" it will Remain Open for IPs listed in the "Remain Open for IPs" list.', 'ip-address-approval'); ?> <?php echo $this->has_ug['2'];?></p>
                                </div>
                            </td>
                        </tr>

                        <tr class="ip-fold" data="2" valign="top" style="display: none;">
                            <th><?php echo __('Allowed', 'ip-address-approval'); ?> <?php echo $this->has_ug['1'];?><br /></th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                <label for="ip_approval_allowed_unit"> <?php echo __('Remain Open for (One IP Address or Location Per Line)', 'ip-address-approval'); ?></label><br />
                                <div class="remain-open-input">
                                    <textarea name="ip_approval_allowed_unit" id="ip_approval_allowed_unit" <?php echo $this->has_ug['0'];?>><?php esc_attr_e($this->settings->allowed); ?></textarea>
                                </div>
                                <p class="description"><?php echo __('Your IP Address is', 'ip-address-approval'); ?> <strong><?php echo IP_APPROVAL_API::get_ip();?></strong></p>
                                <p class="description"><?php echo __('If you choose to have your website closed, you can allow your website to remain open for the IP addresses listed here.', 'ip-address-approval'); ?><br /><?php echo __('Note: You can use a single IP Address, use an IP Range or Geo Location.', 'ip-address-approval'); ?> <?php echo $this->has_ug['2'];?></p>
                                </div>
                            </td>
                        </tr>

                        <tr class="ip-fold" data="2" valign="top" style="display: none;">
                            <th><?php echo __('Access Denied Page', 'ip-address-approval'); ?> <?php echo $this->has_ug['1'];?><br /></th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                <input type="text" name="ip_approval_allowed_url_unit" <?php echo $this->has_ug['0'];?> value="<?php esc_attr_e($this->settings->allowed_url); ?>" id="ip_approval_allowed_url_unit"><label for="ip_approval_allowed_url_unit"> Use http://example.com or /page-title/</label>
                                <p class="description"><?php echo __('The Access Denied Page allows you to select where you would like the people to be directed to, who are denied access to your website.', 'ip-address-approval'); ?><br /><?php echo __('If you use the default Access Denied page, make sure you have a page titled "Access Denied".', 'ip-address-approval'); ?><br /><?php echo __('The URL of the page you create should look like this', 'ip-address-approval'); ?>: <strong>/access-denied/</strong> <?php echo $this->has_ug['2'];?></p>
                                </div>
                            </td>
                        </tr>

                        <tr class="ip-fold" data="2" valign="top" style="display: none;">
                            <th><?php echo __('Banned', 'ip-address-approval'); ?><br /></th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                <label for="ip_approval_banned_unit"> <?php echo __('Banned List (One IP Address or Location Per Line)', 'ip-address-approval'); ?></label><br />
                                <div class="banned-input">
                                    <textarea name="ip_approval_banned_unit" id="ip_approval_banned_unit"><?php esc_attr_e($this->settings->banned); ?></textarea>
                                </div>
                                <p class="description"><?php echo __('Your IP Address is', 'ip-address-approval'); ?> <strong><?php echo IP_APPROVAL_API::get_ip();?></strong></p>
                                <p class="description"><?php echo __('Whether your website is opened or closed, you can block specific IP addresses by adding them to the Banned IPs box.', 'ip-address-approval'); ?><br /><?php echo __('Note: You can use a single IP Address, use an IP Range or Geo Location.', 'ip-address-approval'); ?></p>
                                </div>
                            </td>
                        </tr>

                        <tr class="ip-fold" data="2" valign="top" style="display: none;">
                            <th><?php echo __("Banned IP's Page", 'ip-address-approval'); ?> <?php echo $this->has_ug['1'];?><br /></th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                <input type="text" name="ip_approval_banned_url_unit" <?php echo $this->has_ug['0'];?> value="<?php esc_attr_e($this->settings->banned_url); ?>" id="ip_approval_banned_url_unit"><label for="ip_approval_banned_url_unit"> Use http://example.com or /page-title/</label>
                                <p class="description"><?php echo __('The Banned IPs Page allows you to redirect visitors who have been banned from your website.', 'ip-address-approval'); ?><br /><?php echo __('If you use the default Banned page, make sure you have a page titled "Banned".', 'ip-address-approval'); ?><br /><?php echo __('The URL of the page you create should look like this', 'ip-address-approval'); ?>: <strong>/banned/</strong> <?php echo $this->has_ug['2'];?></p>
                                </div>
                            </td>
                        </tr>

                        <tr class="ip-fold" data="2" valign="top" style="display: none;">
                            <th>Proxy, VPN or Tor <?php echo $this->has_ug['1'];?></th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                <label for="ip_approval_proxy_unit"><?php echo __('Proxy, VPN or Tor Toggle', 'ip-address-approval'); ?>:</label><br><br>
                                <div class="main-switch">
                                  <input type="checkbox" name="ip_approval_proxy_unit" id="ip_approval_proxy_unit" <?php checked($this->settings->proxy,'1'); ?>>
                                  <span class="slider round" id="ip_approval_proxy_unit"><p>ON    OFF</p></span>
                                </div>
                                <br /><br />
                                <p class="description"><?php echo __('Toggle "on" to block Proxy, VPN or Tor visitor connections.', 'ip-address-approval'); ?> <?php echo $this->has_ug['2'];?></p>
                                </div>
                            </td>
                        </tr>

                        <tr class="ip-fold" data="2" valign="top" style="display: none;">
                            <th><?php echo __("Proxy, VPN or Tor Page", 'ip-address-approval'); ?> <?php echo $this->has_ug['1'];?><br /></th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                <input type="text" name="ip_approval_proxy_url_unit" <?php echo $this->has_ug['0'];?> value="<?php esc_attr_e($this->settings->proxy_url); ?>" id="ip_approval_proxy_url_unit"><label for="ip_approval_proxy_url_unit"> Use http://example.com or /page-title/</label>
                                <p class="description"><?php echo __('The Proxy, VPN or Tor Page allows you to redirect Proxy, VPN or Tor visits from your website.', 'ip-address-approval'); ?><br /><?php echo __('If you use the default Banned page, make sure you have a page titled "Banned".', 'ip-address-approval'); ?><br /><?php echo __('The URL of the page you create should look like this', 'ip-address-approval'); ?>: <strong>/banned/</strong><br /><?php echo __('NOTE: If you use a page titled "Proxy or VPN" and you want to log the redirects, make sure to check the box for the page in the Standard Pages setting.', 'ip-address-approval'); ?> <?php echo $this->has_ug['2'];?></p>
                                </div>
                            </td>
                        </tr>

                        <tr class="ip-fold" data="2" valign="top" style="display: none;">
                            <th>Hosting or Data Center <?php echo $this->has_ug['1'];?></th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                <label for="ip_approval_hosting_unit"><?php echo __('Hosting or Data Center Toggle', 'ip-address-approval'); ?>:</label><br><br>
                                <div class="main-switch">
                                  <input type="checkbox" name="ip_approval_hosting_unit" id="ip_approval_hosting_unit" <?php checked($this->settings->hosting,'1'); ?>>
                                  <span class="slider round" id="ip_approval_hosting_unit"><p>ON    OFF</p></span>
                                </div>
                                <br /><br />
                                <p class="description"><?php echo __('Toggle "on" to block Hosting or Data Center visitor connections.', 'ip-address-approval'); ?> <?php echo $this->has_ug['2'];?></p>
                                </div>
                            </td>
                        </tr>

                        <tr class="ip-fold" data="2" valign="top" style="display: none;">
                            <th><?php echo __("Hosting or Data Center Page", 'ip-address-approval'); ?> <?php echo $this->has_ug['1'];?><br /></th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                <input type="text" name="ip_approval_hosting_url_unit" <?php echo $this->has_ug['0'];?> value="<?php esc_attr_e($this->settings->hosting_url); ?>" id="ip_approval_hosting_url_unit"><label for="ip_approval_hosting_url_unit"> Use http://example.com or /page-title/</label>
                                <p class="description"><?php echo __('The Hosting or Data Center Page allows you to redirect Hosting or Data Center visits from your website.', 'ip-address-approval'); ?><br /><?php echo __('If you use the default Banned page, make sure you have a page titled "Banned".', 'ip-address-approval'); ?><br /><?php echo __('The URL of the page you create should look like this', 'ip-address-approval'); ?>: <strong>/banned/</strong><br /><?php echo __('NOTE: If you use a page titled "Hosting or Data Center" and you want to log the redirects, make sure to check the box for the page in the Standard Pages setting.', 'ip-address-approval'); ?> <?php echo $this->has_ug['2'];?></p>
                                </div>
                            </td>
                        </tr>

                        <?php if ($this->options['has_ug'] > 0):?>
                        <tr class="ip-fold" data="2" valign="top" style="display: none;">
                            <th><?php echo __('Do Not Log', 'ip-address-approval'); ?><br /></th>
                            <td>
                                <div style="margin: 0 0 0 10px;">
                                <label for="ip_approval_donotlog_unit"> <?php echo __('Do Not Log List (One IP Address or Location Per Line)', 'ip-address-approval'); ?></label><br />
                                <div class="donotlog-input">
                                    <textarea name="ip_approval_donotlog_unit" id="ip_approval_donotlog_unit"><?php esc_attr_e($this->settings->donotlog); ?></textarea>
                                </div>
                                <p class="description"><?php echo __('Your IP Address is', 'ip-address-approval'); ?> <strong><?php echo IP_APPROVAL_API::get_ip();?></strong></p>
                                <p class="description"><?php echo __('The Do Not Log List is a way for you to exclude visits from being logged in the Visitor Log.', 'ip-address-approval'); ?><br /><?php echo __('Note: You can use IP Addresses, IP Ranges, Geo Locations or Bots in this list.', 'ip-address-approval'); ?></p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <tr valign="middle" class="ip-tr-colorbox do-not-ip-fold">
                            <th colspan="2"><?php echo __('Like this plugin', 'ip-address-approval'); ?>? <a href="https://www.twitter.com/IP_Approval" target="_blank"><?php echo __('Follow us on Twitter', 'ip-address-approval'); ?></a>, <a href="https://www.facebook.com/IPAddressApproval/" target="_blank"><?php echo __('Like us on Facebook', 'ip-address-approval'); ?></a></th>
                        </tr>

                    </tbody>
                </table>
                <?php if ($this->manage_options) { ?>
                <p class="submit">
                    <input name="save" id="save" class="button button-primary" value="<?php echo __('Save Changes', 'ip-address-approval'); ?>" type="submit" />
                    <input name="clear" id="reset" class="button" value="<?php echo __('Reset WordPress Settings', 'ip-address-approval'); ?>" type="submit" />
                </p>
                <?php } ?>
          </form>
      </div>
<?php }}} ?>