<?php
 if(!defined('ABSPATH')) {
    exit;
 }
?>

      <div class="ipwrap" id="ip_approval_changelogs" style="display:none">
        <h2 class="ip_approval-title"><?php echo __('IP Address Approval Change Logs', 'ip-address-approval'); ?></h2>
                <table class="form-table">
                    <tbody>
                        <tr valign="middle" class="ip-tr-colorbox">
                            <th colspan="2"></th>
                        </tr>
                        <tr valign="top">
                            <th><?php echo __('Change Logs', 'ip-address-approval'); ?></th>
                            <td>

<?php
function IP_getVersion($str) {
    preg_match("/(?:version|v)\s*((?:[0-9]+\.?)+)/i", $str, $matches);
    return $matches[1];
}
function IP_PropagateLog() {
  $file_pointer = WP_PLUGIN_DIR .'/ip-address-approval/changelog.txt';
  if (file_exists($file_pointer)) {
    $lines = file($file_pointer);
    // Trim ChangeLog Line
    $tlines = array();
    foreach($lines as $line) {
       $tlines[] = trim($line);
    }
    $start = array_search('== Changelog ==', $tlines);
    $end = count($lines);

    for($i = $start+1; $i<=$end-1; $i++) {
        $store[] = $lines[$i];
    }
    // Foreach ChangeLog Line
    $previousType = null;
    foreach($store as $stored) {
       if(strpos($stored, '*') === false) {
          $previousType = null;
          echo '<h3 class="ip-log-ver">';
          $str = explode("-",$stored);
          $version_num = IP_getVersion(trim($str[1]));
          echo __('Version', 'ip-address-approval').' '.$version_num; 
          $old_date = explode('.', trim($str[0])); 
          $new_data = $old_date[1].'/'.$old_date[2].'/'.$old_date[0];
          echo ' ('.$new_data.')';
          echo '</h3>';
       }
       if(strpos($stored, '*') !== false) {
          $prefix = ' * ';
          if (substr($stored, 0, strlen($prefix)) == $prefix) {
              $str = substr($stored, strlen($prefix));
          }
          $type = 'Changes';
          $class = 'changes';
          if(strpos($str, ':') !== false) {
             $exp = explode(":",$str);
             if ($exp[0] !== $previousType) {
                if ($exp[0] === 'Changes') {
                    $previousType = 'Changes';
                    $type = 'Changes';
                    $class = 'changes';
                }
                if ($exp[0] === 'New Features') {
                    $previousType = 'New Features';
                    $type = 'New Features';
                    $class = 'newfeatures';
                }
                if ($exp[0] === 'Bug Fixes') {
                    $previousType = 'Bug Fixes';
                    $type = 'Bug Fixes';
                    $class = 'bugfixes';
                }
                if ($exp[0] === 'Fixed') {
                    $previousType = 'Bug Fixes';
                    $type = 'Bug Fixes';
                    $class = 'bugfixes';
                }
                if ($exp[0] === 'Improved') {
                    $previousType = 'Improved';
                    $type = 'Improved';
                    $class = 'improve';
                }
                if ($exp[0] === 'Removed') {
                    $previousType = 'Removed';
                    $type = 'Removed';
                    $class = 'removed';
                }
                echo '<div class="ip-log-type '.$class.'"><strong>'.__($type, 'ip-address-approval').'</strong></div>';
             }
             echo '<ul>';
             echo '<li>'.__(trim($exp[1]), 'ip-address-approval').'</li>';
             echo '</ul>';
          }
       }
    }
  }
  else {
    echo 'changelog.txt file is missing.';
  }
}
IP_PropagateLog();
?>

                             <!--
                            <h3 class="ip-log-ver">Version  1.0.0 (Mar 1, 2019) </h3>
                            <div class="ip-log-type changed"><strong>Changed</strong></div>
                            <ul><li>Released Plugin</li></ul>

                            <h3 class="ip-log-ver">Version  1.0.0 (Mar 1, 2019) </h3>
                            <div class="ip-log-type newfeatures"><strong>New Features</strong></div>
                            <ul><li>Released Plugin</li></ul>

                            <h3 class="ip-log-ver">Version  1.0.0 (Mar 1, 2019) </h3>
                            <div class="ip-log-type improve"><strong>Improved</strong></div>
                            <ul><li>Released Plugin</li></ul>

                            <h3 class="ip-log-ver">Version  1.0.0 (Mar 1, 2019) </h3>
                            <div class="ip-log-type bugfixes"><strong>Bug Fixes</strong></div>
                            <ul><li>Released Plugin</li></ul>

                            <h3 class="ip-log-ver">Version  1.0.0 (Mar 1, 2019) </h3>
                            <div class="ip-log-type remove"><strong>Removed</strong></div>
                            <ul><li>Released Plugin</li></ul>
                             -->

                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                <br />
                            </td>
                        </tr>
                    </tbody>
                </table>
      </div>