<?php
 if(!defined('ABSPATH')) {
    exit;
 }

 if (empty($this->api_message)) {
  echo '<div class="ipwrap" id="ip_approval_sys_info" style="display:none">';
    echo '<h2 class="ip_approval-title">'.__("System Info", "ip-address-approval").'</h2>';


     echo '<div class="html-content"></div>';
     echo '<table class="form-table raw-content-table">';
      echo '<tbody>';
       echo '<tr valign="middle" class="ip-tr-colorbox"><th colspan="2" style="background: #c43838;"></th></tr>';
       echo '<tr valign="top">';
         echo '<th>'.__("Copy & Paste", "ip-address-approval").'</th>';
         echo '<td>';
           echo '<label id="ip-approval-raw-code-label" for="ip-approval-raw-code">'.__("Simply click in the text area to copy the information. If that doesn't work you can copy the below info using Ctrl+C/Ctrl+V or right-click then copy/paste:", "ip-address-approval").'</label>';
           echo '<textarea class="raw-content" id="ip-approval-raw-code" readonly style="height: 250px; width: 100%; max-width: 100%; position: relative;">';
           echo '</textarea>';
         echo '</td>';
        echo '</tr>';
      echo '</tbody>';
     echo '</table>';
     ?>
     <script>
     var tooltip, // global variables oh my! Refactor when deploying!
       hidetooltiptimer

     function createtooltip(){ // call ONCE at the end to create tool tip object
       tooltip = document.createElement('div');
       tooltip.className = "introjs-tooltip";
       tooltiparrow = document.createElement('div');
       tooltiparrow.className = "introjs-arrow top";
       tooltip.style.cssText = 'display:none;min-width:60px !important;text-align;center;opacity:0;transition:opacity 0.5s;'
       tooltip.innerHTML = '<?php echo __('Copied!', 'ip-address-approval'); ?>';
       tooltip.appendChild(tooltiparrow);
       document.body.appendChild(tooltip);
     }
     function showtooltip(e){
       var evt = e || event; 
       clearTimeout(hidetooltiptimer);
       tooltip.style.left = evt.pageX - 15 + 'px';
       tooltip.style.top = evt.pageY + 15 + 'px';
       tooltip.style.display = 'block';
       tooltip.style.opacity = 1;
       hidetooltiptimer = setTimeout(function(){
         tooltip.style.opacity = 0;
       }, 1900)
     }

     function getSelectionText(){
       var selectedText = "";
       if (window.getSelection){ // all modern browsers and IE9+
         selectedText = window.getSelection().toString();
       }
       return selectedText;
     }

     function selectElementText(el){
       var range = document.createRange(); // create new range object
       range.selectNodeContents(el); // set range to encompass desired element text
       var selection = window.getSelection(); // get Selection object from currently user selected text
       selection.removeAllRanges(); // unselect any user selected text (if any)
       selection.addRange(range); // add range to Selection object to select it
     }

     function copySelectionText(){
       var copysuccess; // var to check whether execCommand successfully executed
       try{
         copysuccess = document.execCommand('copy', false, null); // run command to copy selected text to clipboard
       } catch(e){
         copysuccess = false;
       }
     return copysuccess;
     }
     createtooltip();

     jQuery('#ip-approval-raw-code').on('click', function(e) {
        var textarea = document.getElementById('ip-approval-raw-code');
        var selectRange = function() {
          textarea.setSelectionRange(0, textarea.value.length);
        };
        textarea.onfocus = textarea.onblur = textarea.onclick = selectRange;
        textarea.onfocus();
        var copysuccess = copySelectionText();
        if (copysuccess){
            showtooltip(e);
        }
        else {
           if (!reset_form.done) { 
             if (!!navigator.userAgent.match(/Version\/[\d\.]+.*Safari/)) {
                 reset_form.done = true;
                 alert('<?php echo __('Safari does not support click to copy. Please manually copy the API Credentials.', 'ip-address-approval'); ?>');
             }
           }
        }
     });
     </script>
<?php
  echo '</div>';
 }
?>