<?php
 if(!defined('ABSPATH')) {
    exit;
 }

echo '<h2 class="nav-tab-wrapper">';
  if (empty($this->api_message)) {
    if ($this->is_api_key AND $this->is_api_secret) {
       if ($this->is_ip_id) {
           echo '<a href="#ip_approval_settings" class="nav-tab nav-tab-active" id="ip_approval-tab">';
             echo __('General Settings', 'ip-address-approval');
           echo '</a>';
            echo '<a href="#ip_approval_page_visits" class="nav-tab" id="ip_approval-tab_page_visits">';
              echo __('Site Visitors', 'ip-address-approval');
            echo '</a>';
        }
    }
  }
  echo '<a href="#ip_approval_changelogs" class="nav-tab" id="ip_approval-tab_logs">';
    echo __('Change Logs', 'ip-address-approval');
  echo '</a>';
  if (empty($this->api_message)) {
      echo '<a href="#ip_approval_ip_id" class="nav-tab" id="ip_approval-tab_ip_id">';
        echo __('Credentials', 'ip-address-approval');
      echo '</a>';
      if ($this->is_api_key AND $this->is_api_secret) {
         if ($this->is_ip_id) {
             echo '<a href="#ip_approval_sys_info" class="nav-tab" id="ip_approval-tab_sys_info" style="display: none;">';
               echo __('System Info', 'ip-address-approval');
             echo '</a>';
         }
      }
      echo '<a href="https://www.ip-approval.com/geo-location/" class="nav-tab" target="_blank">';
        echo '<i class="fa fa-globe fa-lg" title="'.__("Geo Location", "ip-address-approval").'" style="font-size: 1.7em;"></i>';
      echo '</a>';
      echo '<a href="https://www.ip-approval.com/wordpress/ip-approval/instructions" class="nav-tab" target="_blank">';
        echo '<i class="fa fa-support fa-lg" title="'.__("Link to WordPress IP Editor Instructions page", "ip-address-approval").'" style="font-size: 1.4em;"></i>';
      echo '</a>';
  }
echo '</h2>';
?>