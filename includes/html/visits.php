<?php
 if(!defined('ABSPATH')) {
    exit;
 }
?>

<div class="ipwrap" id="ip_approval_page_visits" style="display:none">
<?php
if ($this->options['has_ug'] > 0) {
    echo '<input type="text" class="FilterVisitInput" placeholder="Search Filter for.." title="'.__('Search Filter for Page visits.', 'ip-address-approval').'">';
    echo '<table id="VisitorTable" class="tablesorter">';
      echo '<thead>';
        echo '<tr>';
          echo '<th class="header"><input type="checkbox" id="visits-checkAll" name="visits_check_all" /></th>';
          echo '<th class="header">'.__('IP Address', 'ip-address-approval').'</th>';
          echo '<th class="header" style="background-image: none;"><i id="questionmark_page_checked" class="fa fa-question-circle-o fa-lg" data-toggle="tooltip" data-placement="right" title="'.__('This column allows you to add the IP Address to the Allowed or Banned list. Click the (-)Minus Button to change it to (/)Banned and again to change it to (+)Plus to add the IP to the Allowed list and (*)Asterisk to add the IP to the Do Not Log list. Clicking it again resets it to Minus.', 'ip-address-approval').'" style="font-size: 1.6em;"></i></th>';
          echo '<th class="header">'.__('User Agent', 'ip-address-approval').'</th>';
          echo '<th class="header">'.__('Page Visited', 'ip-address-approval').'</th>';
          echo '<th class="header">'.__('Date', 'ip-address-approval').':</th>';
        echo '</tr>';
      echo '</thead>';
      echo '<tbody class="pagenate-content">';
      echo '</tbody>';
    echo '</table>';

    echo '<div class="pagenate-link" align="center">';
      echo '<div class="visits-checkAll-2">';
        echo '<input type="checkbox" id="visits-checkAll-2" name="visits_check_all_2" />';
        echo '<label for="visits_check_all_2">';
          echo __('Select All', 'ip-address-approval');
        echo '</label> ';
        echo '<button id="visits_check_all_delete" class="deleteexportcsv">';
          echo __('Delete', 'ip-address-approval');
        echo '</button>';
      echo '</div>';

      echo '<div class="paginate">';
        echo '<ul id="paginate"><li></li></ul>';
      echo '</div>';

      echo '<div class="per_page">';
        echo '<!--<button class="deleteexportcsv">Export</button>-->';
        echo '<select project="per_page" id="per_page" name="per_page" class="tall-setting">';
          echo '<option value="10">10</option>';
          echo '<option value="20">20</option>';
          echo '<option value="30">30</option>';
          echo '<option value="40">40</option>';
          echo '<option value="50" selected="selected">50</option>';
          echo '<option value="100">100</option>';
          echo '<option value="200">200</option>';
        echo '</select>';
        echo '<span id="per_page">';
          echo __('Per Page', 'ip-address-approval');
        echo '/</span>';
      echo '</div>';

    echo '</div>';
}
else {
   echo '<div class="page-list-upgrade" style="position:relative;top:80px;width:100%;text-align:center;">';
   echo __('In order to use this feature you need to upgrade to a paid version of our service.', 'ip-address-approval');
   echo '<BR>';
   echo '<a href="https://www.ip-approval.com/upgrade" target="_blank" class="btn btn-lg btn-secondary hidden-xs bounceInUp animated slow go">'.__('Upgrade to view Site Visitors', 'ip-address-approval').'</a>';
   echo '</div>';
}
?>
</div>