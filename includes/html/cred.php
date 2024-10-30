<?php
 if(!defined('ABSPATH')) {
    exit;
 }
 if (empty($this->api_message)) { ?>
      <div class="ipwrap" id="ip_approval_ip_id" style="display:none">
        <h2 class="ip_approval-title"><?php echo __('IP Address Approval Credentials', 'ip-address-approval'); ?></h2>
        <form action="" method="post" id="ip_approval_ip_id_form" name="ip_approval_ip_id_form">
               <?php wp_nonce_field('ip_approval-log_user_info_', 'ip_approval_log_user_info_nonce'); ?>
                <table class="form-table">
                    <tbody>
                        <tr valign="middle" class="ip-tr-colorbox">
                            <th colspan="2"></th>
                        </tr>
                        <tr valign="top">
                            <th>
                              <?php echo __('Connect User Account', 'ip-address-approval'); ?>
                              <?php
                               if ($this->is_ip_id) {
                                   echo '<BR><BR><button id="serv-sys-info" class="button button-primary">'.__('System Info', 'ip-address-approval').'</button>';
                                   echo '<p class="description">'.__('System Info can be used to see some of your Server and WordPress Environment, and it is here if you would ever need it.', 'ip-address-approval').'</p>';
                               }
                              ?>  
                            </th>
                            <td>
                             <?php
                             if (!empty($this->options['ip_site_id']) || $this->options['ip_site_id'] !== '0') {
                                 $form_site_id= $this->options['ip_site_id'];
                                 $form_site_id_value = $this->options['ip_site_id'];
                             } else {
                                 $form_site_id = __('Enter Site ID Here', 'ip-address-approval');
                                 $form_site_id_value = '';
                             }
                             if (!empty($this->options['ip_id']) || $this->options['ip_id'] !== '0') {
                                 $form_ip_id = $this->options['ip_id'];
                                 $form_ip_id_value = $this->options['ip_id'];
                             } else {
                                 $form_ip_id = __('Enter User ID Here', 'ip-address-approval');
                                 $form_ip_id_value = '';
                             }
                             if ($this->is_api_key && !$cred_check_error) {
                                 $form_api_key = $this->options['api_key'];
                                 $form_api_key_value = $this->options['api_key'];
                             } else {
                                 $form_api_key = __('Enter API Key Here', 'ip-address-approval');
                                 $form_api_key_value = '';
                             }
                             if ($this->is_api_secret && !$cred_check_error) {
                                 $form_api_secret = $this->options['api_secret'];
                                 $form_api_secret_value = $this->options['api_secret'];
                             } else {
                                 $form_api_secret = __('Enter API Secret Here', 'ip-address-approval');
                                 $form_api_secret_value = '';
                             }
                             ?>

                              <p class="description"><?php echo __('Please enter your', 'ip-address-approval'); ?> <strong>User ID</strong>, <strong>Site ID</strong>, <strong>API Key</strong> <?php echo __('and', 'ip-address-approval'); ?> <strong>API Secret</strong> <?php echo __('here', 'ip-address-approval'); ?>. <strong></strong></p>
                              <label for="ip_approval_ip_id_input">User ID:    </label>
                              <input type="text" name="ip_approval_ip_id_input" value="<?php echo $form_ip_id_value;?>" id="ip_approval_ip_id_input" placeholder="<?php echo $form_ip_id;?>" size="70" pattern="[0-9].{0,}" title="<?php echo __('MUST be one or more number characters', 'ip-address-approval'); ?>" required><BR>
                              <label for="ip_approval_ip_id_input">Site ID:    </label>
                              <input type="text" name="ip_approval_site_id_input" value="<?php echo $form_site_id_value;?>" id="ip_approval_site_id_input" placeholder="<?php echo $form_site_id;?>" size="70" pattern="[1-9]|10|M.{0,}" title="<?php echo __('MUST be a number, 1 though 10', 'ip-address-approval'); ?>" required><BR>
                              <label for="ip_approval_api_key_input">API Key:   </label>
                              <input type="text" name="ip_approval_api_key_input" value="<?php echo $form_api_key_value;?>" id="ip_approval_api_key_input" placeholder="<?php echo $form_api_key;?>" size="70" pattern="[0-9].{7,}" title="<?php echo __('MUST be 7 or more number characters', 'ip-address-approval'); ?>" required><BR>
                              <label for="ip_approval_api_secret_input">API Secret:</label>
                              <input type="text" name="ip_approval_api_secret_input" value="<?php echo $form_api_secret_value;?>" id="ip_approval_api_secret_input" placeholder="<?php echo $form_api_secret;?>" size="70" pattern="[a-z0-9].{20,}" title="<?php echo __('MUST be more number and letter characters', 'ip-address-approval'); ?>" required><BR><BR>

                              <?php
                               if (!$this->is_ip_id) {
                                   echo '<input name="log_user_info_" id="log_user_info_" class="button button-primary" value="'.__("Connect Account", 'ip-address-approval').'" type="submit" />';
                               }
                               if ($this->is_ip_id) {
                                   echo '<input name="log_user_info_" id="log_user_info_" class="button button-primary button-deactivate" value="'.__("Deactivate", 'ip-address-approval').'" type="submit" />';
                               }
                               if ($cred_check_error) {
                                   echo '&nbsp;<input name="log_user_info_" id="log_user_info_" class="button button-primary" value="'.__("RE-Connect Account", 'ip-address-approval').'" type="submit" />';
                               }
                              ?>

                              <BR><BR>
                              <p class="description"><strong>User ID, Site ID, API Key <?php echo __('and', 'ip-address-approval');?> API Secret;</strong><BR><?php echo __('You can find your User ID, API Key and API Secret on the account page here', 'ip-address-approval');?>: <a href="https://www.ip-approval.com/account" target="_blank">https://www.ip-approval.com/account</a><BR><?php echo __('The', 'ip-address-approval');?> Site ID, <?php echo __('is numbered 1 though 10 on the', 'ip-address-approval');?> <a href="https://www.ip-approval.com/main" target="_blank">IP Editor</a> <?php echo __('page', 'ip-address-approval');?>.</p>
                              <?php if (empty($this->options['has_ug']) || $this->options['has_ug'] === '0') { ?>
                                 <p class="description"><strong><?php echo __('To Purchase Paid Subscription', 'ip-address-approval');?>;</strong> <?php echo __('Please Login to your IP Approval account, on our website, and visit', 'ip-address-approval');?> <a href="https://www.ip-approval.com/upgrade" target="_blank">https://www.ip-approval.com/upgrade</a></p>
                              <?php } ?>
                              <p class="description ip_introjs_exp_description"><strong><?php echo __('Explicit and Authorized Consent', 'ip-address-approval');?>;</strong> <?php echo __('By clicking the "Connect Account" button you explicitly agree to provide the Blog Owners Name, Username, Email, Blog ID, Current Theme Name, Domain URL, WordPress Version and Plugin Version to the IP Address Approval service. Note that this helps us troubleshoot any issues you may encounter.', 'ip-address-approval');?><BR><?php echo __('To learn about our privacy policy, please visit:', 'ip-address-approval');?> <a href="https://www.ip-approval.com/privacy-policy" target="_blank">https://www.ip-approval.com/privacy-policy</a></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
        </form>
      </div>
<?php } ?>