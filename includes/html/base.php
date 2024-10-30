<?php
 if(!defined('ABSPATH')) {
    exit;
 }
 $ip_dir_path = plugin_dir_path(__FILE__);
 $cred_check_error = false;

 echo '<div id="ip_approval_options" class="wrap">'; 

   if (empty($this->api_message)) {
       require($ip_dir_path.'menu.php');
       if ($this->is_api_key AND $this->is_api_secret) {
           if ($this->is_ip_id) {
               if (isset($_GET['link']) && $_GET['link'] === 'cred_check') { 
                   $cred_check_error = 'true';
               }
               require($ip_dir_path.'settings.php');
               require($ip_dir_path.'visits.php');
               require($ip_dir_path.'sysinfo.php');
           }
       }
       require($ip_dir_path.'cred.php');
   }
   else { 
       echo $this->api_message;
   }
   require($ip_dir_path.'logs.php');
  
 echo '</div>'; 
?>
<script type="text/javascript" language="javascript">
   $ = jQuery;
 <?php if (empty($this->api_message)) { ?>
   var site_value = <?php echo $this->options['ip_site_id']; ?>;
   var per_page = 20;
   var totalResults = 0;
   var AppViews = {};
   AppViews.evt_data_page = 1;
   AppViews.evt_data_per_page = per_page;
   AppViews.paginateTotal = '';

   var SysInfo = {};
   SysInfo.html = '';
   SysInfo.raw = '';

   var ip_approval_prevVal = '';
   var ip_approval_nonce = "<?php echo wp_create_nonce($this->options['ip_id']); ?>";
   $("body").append('<div class="pagenate-load" style="display:none;"></div>');
   //$("body").append('<!-- <div class="pagenate-load-text">Please Wait... </div> -->');

   /* extend jQuery, check to see if .pagenate-content (or any html) is Empty 
   ------------------------------------------------------------------------*/
   $.fn.IPFuncIsEmpty = function(){
       return $(this).html() == "";
   }
   /* Pagenate Display
   ------------------------------------------------------------------------*/
   function ip_approval_check_pagenate() {
     if(!$('.pagenate-content').IPFuncIsEmpty()){
        $('.tablesorter').css({'display' : 'table'});
        $('.FilterVisitInput').css({'display' : 'inline-block'});
        $('.pagenate-link').css({'display' : 'block'});
        ip_approval_pagenateDisplay('fadeout');
        ip_approval_FilterResults();
     }
   }


   /*   SYSTEM INFO 
   --------------------------------------------------------------------*/
   $('#serv-sys-info').on('click', function(e) {
    e.preventDefault();
    if (SysInfo.html === ''){
        $('.raw-content-table').hide();
        ip_approval_pagenateDisplay('show');
           $.post(ajaxurl, {
               'action': 'ip_approval_ajax_action',
               'do_action': 'sys_info',
               security: ip_approval_nonce
           }, function(response) {
               SysInfo.html = response.data.html;
               SysInfo.raw = response.data.raw;
               $('.html-content').html(SysInfo.html);
               $('.raw-content').html(SysInfo.raw);
               $('.raw-content-table').show();
           });
        ip_approval_pagenateDisplay('fadeout');
    };
    $('#ip_approval-tab_sys_info').show();
    $('#ip_approval-tab_sys_info').click();
    $('#ip_approval_sys_info').show();
   });


   /*   ACCORDIAN 
   --------------------------------------------------------------------*/
   var $ipresearch = $('.form-table');
   var sectionsView = {};
   sectionsView.one = "<?php echo $this->options['sections']['1']; ?>";
   sectionsView.two = "<?php echo $this->options['sections']['2']; ?>";

   <?php
      if (!(array_key_exists("sections",$this->options))){
          $ip_sections = array();
          $ip_sections['1'] = 'yes';
          $ip_sections['2'] = 'yes';

          $this->options['sections'] = $ip_sections;
          update_option($ip_approval_plugin_name, $this->options);
      }
      if ($this->options['sections']['1'] === 'no') {
          echo '$(".ip-accordion").siblings(\'tr[data="1"]\').not(".ip-accordion").not(".do-not-ip-fold").fadeToggle(500);';
          echo '$ipresearch.find(\'tr[data="1"]\').find("span").addClass("up");';
      }
      if ($this->options['sections']['2'] === 'no') {
          echo '$(".ip-accordion").siblings(\'tr[data="2"]\').not(".ip-accordion").not(".do-not-ip-fold").fadeToggle(500);';
          echo '$ipresearch.find(\'tr[data="2"]\').find("span").addClass("up");';
      }
   ?>

   $ipresearch.find(".ip-accordion").click(function(){
          var ip_approval_accord_data = $(this)["0"].attributes[1].value;
          if (ip_approval_accord_data == 1) {
              if (sectionsView.one == 'yes') { sectionsView.one = 'no'; }
              else { sectionsView.one = 'yes'; }
          }
          if (ip_approval_accord_data == 2) {
              if (sectionsView.two == 'yes') { sectionsView.two = 'no'; }
              else { sectionsView.two = 'yes'; }
          }
          $(this).siblings('tr[data="' + ip_approval_accord_data + '"]').not(".ip-accordion").not(".do-not-ip-fold").fadeToggle(500);
          $(this).find("span").toggleClass("up");
          $.post(ajaxurl, {
             'action': 'ip_approval_ajax_action',
             'do_action': 'update_sections',
             security: ip_approval_nonce,
             one: sectionsView.one,
             two: sectionsView.two
          }, function(response) {
          });
   });

  $("#ip_approval_ip_id_input, #ip_approval_site_id_input").on("input", function (evt) {
    var self = $(this);
    if (self.val().match(/^-?\d*(\.(?=\d*)\d*)?$/) !== null) {
        ip_approval_prevVal = self.val()
    } else {
        self.val(ip_approval_prevVal);
    }
    if ((evt.which != 46 || self.val().indexOf('.') != -1) && (evt.which < 48 || evt.which > 57) && (evt.which != 45 && self.val().indexOf("-") == 0)) {
        evt.preventDefault();
    }
  });


 $(function() {
   setTimeout(function() {
     $('#visited_page_checked').change(function(){
       ip_approval_visit_page_urlFunction();
     });

     $( "table#VisitorTable tbody" ).on("touchstart mousedown", "td:nth-of-type(3)", function(e) {
         var x = e.clientX;
         var y = e.clientY;
         var coords = "X coords: " + x + ", Y coords: " + y;
         if (x > $(this).offset().left + 11 &&
             x < $(this).offset().left + 32 &&
             y > $(this).offset().top + 6 &&
             y < $(this).offset().top + 23) {
             // $(document.body).append('<div class="tooltip fade right in" role="tooltip" style="z-index: 9999; display:none;"><div class="tooltip-arrow" style="top: 50%;"></div><div class="tooltip-inner">This column allows you to add the IP Address to the Allowed or Banned list. Click the (-)Minus Button to change it to (X)Banned and again to change it to (+)Plus to add the IP to the Allowed list. Clicking it again resets it to Minus.</div></div>');
             $('.tooltip').css({top: $(this).offset().top - 45 +'px', left: $(this).offset().left + 32 +'px', position: 'absolute', display: 'block'});
         }
     }).on("touchend mouseup", "td:nth-of-type(3)", function(){
         setTimeout(function(){ 
            $('.tooltip').fadeOut( "slow", function() {
              $('.tooltip').remove();
            });
         }, 6000);
     });

     var theIPids = {};
     theIPids.timeout=null;
     $("table#VisitorTable tbody").on( "click", "i", function() {
     var wasfoundin = '<?php echo __(' was found in ', 'ip-address-approval');?>';
     var wouldYouLike4 = '<?php echo __(' Would you like to delete this IPv4 Range from the Allowed List', 'ip-address-approval');?>';
     var wouldYouLikeBanned4 = '<?php echo __(' Would you like to delete this IPv4 Range from the Banned List', 'ip-address-approval');?>';
     var wouldYouLike6 = '<?php echo __(' Would you like to delete this IPv6 Range from the Allowed List', 'ip-address-approval');?>';
     var wouldYouLikeBanned6 = '<?php echo __(' Would you like to delete this IPv6 Range from the Banned List', 'ip-address-approval');?>';
        var theIPid = $(this).attr("data-ip");
        theIPids.theipid=theIPid;

       var in_allowed_list_cancel = false;
       var in_allowed_list= false;
       var in_banned_list_cancel = false;
       var in_banned_list = false;
       var in_donotlog_list_cancel = false;
       var in_donotlog_list = false;

       if($(this).hasClass('fa-minus-circle')){
          //IF In Allowed remove IP
          var allowed_lines = $('.remain-open-input textarea').val().split('\n');
          $.each(allowed_lines, function(i, val){
                 var val_all_line = this.split("//");
                 var val_all_line_trim = $.trim(val_all_line[0]);
                 if(val_all_line_trim == theIPid) {
                    in_allowed_list = true;
                    // Remove THIS from Banned List
                    allowed_lines.splice(i, 1);
                    return false; 
                 }
                 else if (ip_approval_is_ipv6(theIPid)){
                    if (ip_approval_is_ipv6(val_all_line_trim) | val_all_line_trim.includes(":")){
                      if (in_ipv6_range(theIPid, val_all_line_trim)){
                          var rconfirm = confirm(theIPid+wasfoundin+val_all_line_trim+'\n '+wouldYouLike6+'?\n\n');
                          if (rconfirm == true) {
                              in_allowed_list = true;
                              // Remove THIS from Banned List
                              allowed_lines.splice(i, 1);
                              return false; 
                          }
                          else {
                              in_allowed_list_cancel = true;
                              return false;
                          }
                      }
                    }
                 }
                 else if (in_ipv4_range(theIPid, val_all_line_trim)){
                          var rconfirm = confirm(theIPid+wasfoundin+val_all_line_trim+'\n '+wouldYouLike4+'?\n\n');
                          if (rconfirm == true) {
                              in_allowed_list = true;
                              // Remove THIS from Banned List
                              allowed_lines.splice(i, 1);
                              return false; 
                          }
                          else {
                              in_allowed_list_cancel = true;
                              return false;
                          }
                 }
          });

          if (in_allowed_list == true) {
              in_allowed_list = false;
              $('.remain-open-input textarea').val(allowed_lines.join("\n"));
              var default_allowed = '';
              default_allowed = $('.remain-open-input textarea').val();
              var data = {
                  "action": "update_allowed",
                  "site_value": site_value,
                  "allowed": default_allowed
              };
              ip_approval_reMakeCall(data, null);
          }

          //IF In Do Not Log remove IP
          var donotlog_lines = $('.donotlog-input textarea').val().split('\n');
          $.each(donotlog_lines, function(i, val){
                 var val_donot_line = this.split("//");
                 var val_donot_line_trim = $.trim(val_donot_line[0]);
                 if(val_donot_line_trim == theIPid) {
                    in_donotlog_list = true;
                    // Remove THIS from Banned List
                    donotlog_lines.splice(i, 1);
                    return false; 
                 }
                 else if (ip_approval_is_ipv6(theIPid)){
                    if (ip_approval_is_ipv6(val_donot_line_trim) | val_donot_line_trim.includes(":")){
                      if (in_ipv6_range(theIPid, val_donot_line_trim)){
                          var rconfirm = confirm(theIPid+wasfoundin+val_donot_line_trim+'\n Would you like to delete this IPv6 Range from the Do Not Log List?\n\n');
                          if (rconfirm == true) {
                              in_donotlog_list = true;
                              // Remove THIS from Banned List
                              donotlog_lines.splice(i, 1);
                              return false; 
                          }
                          else {
                              in_donotlog_list_cancel = true;
                              return false;
                          }
                      }
                    }
                 }
                 else if (in_ipv4_range(theIPid, val_donot_line_trim)){
                          var rconfirm = confirm(theIPid+wasfoundin+val_donot_line_trim+'\n Would you like to delete this IP Range from the Do Not Log List?\n\n');
                          if (rconfirm == true) {
                              in_donotlog_list = true;
                              // Remove THIS from Banned List
                              donotlog_lines.splice(i, 1);
                              return false; 
                          }
                          else {
                              in_donotlog_list_cancel = true;
                              return false;
                          }
                 }
          });

          if (in_donotlog_list == true) {
              in_donotlog_list = false;
              $('.donotlog-input textarea').val(donotlog_lines.join("\n"));
              var default_donotlog = '';
              default_donotlog = $('.donotlog-input textarea').val();
              var data = {
                  "action": "update_donotlog",
                  "site_value": site_value,
                  "donotlog": default_donotlog
              };
              ip_approval_reMakeCall(data, null);
          }
          if (in_allowed_list_cancel != true && in_donotlog_list_cancel != true) {
              //IF In Banned remove IP
              var banned_lines = $('.banned-input textarea').val().split('\n');
              $.each(banned_lines, function(){
                     if(this == theIPid) {
                        in_banned_list = true;
                        return false;
                     }
              });


              clearTimeout(theIPids.timeout);
              theIPids.timeout = null;
              theIPids.timeout=setTimeout(function(){   
                if (in_banned_list == false) {
                    //ADD to Banned list
                    var ipbtxt = $.trim(theIPids.theipid);
                    var ipbbox = $('.banned-input textarea');
                    if (ipbbox.val() == "") {
                        ipbbox.val(ipbtxt);
                    }
                    else {
                        ipbbox.val(ipbbox.val() + '\n' + ipbtxt);
                    }
                    //UpDate Table
                    var default_banned = '';
                    default_banned = ipbbox.val();
                    var data = {
                         "action": "update_banned",
                         "site_value": site_value,
                         "banned": default_banned
                    };
                    ip_approval_reMakeCall(data, null);
                } // END 'if'(in_banned_list == false)
              },1200);

              $.each($('i[id^="'+theIPid+'"]'), function(i, e) {
                     $(this).removeClass('fa-minus-circle').addClass('fa-ban');
                     $(this).addClass('fa-red');
              });

          }
          // $(this).toggleClass('fa-minus-circle').toggleClass('fa-ban');
       }
       else if($(this).hasClass('fa-ban')){
              //IF In BANNED remove IP
              var banned_lines = $('.banned-input textarea').val().split('\n');
              $.each(banned_lines, function(i, val){
                  var val_ban_line = this.split("//");
                  var val_ban_line_trim = $.trim(val_ban_line[0]);
                  if(val_ban_line_trim == theIPid) {
                     in_banned_list = true;
                     // Remove THIS from Banned List
                     banned_lines.splice(i, 1);
                     return false; 
                  }
                  else if (ip_approval_is_ipv6(theIPid)){
                    if (ip_approval_is_ipv6(val_ban_line_trim) | val_ban_line_trim.includes(":")){
                      if (in_ipv6_range(theIPid, val_ban_line_trim)){
                          var rconfirm = confirm(theIPid+wasfoundin+val_ban_line_trim+'\n '+wouldYouLikeBanned6+'?\n\n');
                          if (rconfirm == true) {
                              in_banned_list = true;
                              // Remove THIS from Banned List
                              banned_lines.splice(i, 1);
                              return false; 
                          }
                          else {
                              in_banned_list_cancel = true;
                              return false;
                          }
                      }
                    }
                  }
                  else if (in_ipv4_range(theIPid, val_ban_line_trim)){
                       var rconfirm = confirm(theIPid+wasfoundin+val_ban_line_trim+'\n '+wouldYouLikeBanned4+'?\n\n');
                       if (rconfirm == true) {
                           in_banned_list = true;
                           // Remove THIS from Banned List
                           banned_lines.splice(i, 1);
                           return false; 
                       }
                       else {
                           in_banned_list_cancel = true;
                           return false;
                       }
                  }
              });

              if (in_banned_list == true) {
                  in_banned_list = false;
                  $('.banned-input textarea').val(banned_lines.join("\n"));

                  var default_banned = '';
                  default_banned = $('.banned-input textarea').val();
                  var data = {
                       "action": "update_banned",
                       "site_value": site_value,
                       "banned": default_banned
                  };
               ip_approval_reMakeCall(data, null);
              }
              //IF In Do Not Log remove IP
              var donotlog_lines = $('.donotlog-input textarea').val().split('\n');
              $.each(donotlog_lines, function(i, val){
                  var val_ban_line = this.split("//");
                  var val_ban_line_trim = $.trim(val_ban_line[0]);
                  if(val_ban_line_trim == theIPid) {
                     in_donotlog_list = true;
                     // Remove THIS from donotlog List
                     donotlog_lines.splice(i, 1);
                     return false; 
                  }
                  else if (ip_approval_is_ipv6(theIPid)){
                    if (ip_approval_is_ipv6(val_ban_line_trim) | val_ban_line_trim.includes(":")){
                      if (in_ipv6_range(theIPid, val_ban_line_trim)){
                          var rconfirm = confirm(theIPid+wasfoundin+val_ban_line_trim+'\n Would you like to delete this IPv6 Range from the Do Not Log List?\n\n');
                          if (rconfirm == true) {
                              in_donotlog_list = true;
                              // Remove THIS from donotlog List
                              donotlog_lines.splice(i, 1);
                              return false; 
                          }
                          else {
                              in_donotlog_list_cancel = true;
                              return false;
                          }
                      }
                    }
                  }
                  else if (in_ipv4_range(theIPid, val_ban_line_trim)){
                       var rconfirm = confirm(theIPid+wasfoundin+val_ban_line_trim+'\n Would you like to delete this IP Range from the Do Not Log List?\n\n');
                       if (rconfirm == true) {
                           in_donotlog_list = true;
                           // Remove THIS from donotlog List
                           donotlog_lines.splice(i, 1);
                           return false; 
                       }
                       else {
                           in_donotlog_list_cancel = true;
                           return false;
                       }
                  }
              });

              if (in_donotlog_list == true) {
                  in_donotlog_list = false;
                  $('.donotlog-input textarea').val(donotlog_lines.join("\n"));

                  var default_donotlog = '';
                  default_donotlog = $('.donotlog-input textarea').val();
                  var data = {
                       "action": "update_donotlog",
                       "site_value": site_value,
                       "donotlog": default_donotlog
                  };
               ip_approval_reMakeCall(data, null);
              }
              if (in_banned_list_cancel != true && in_donotlog_list_cancel != true) {
                  //IF In Allowed remove IP
                  var allowed_lines = $('.remain-open-input textarea').val().split('\n');
                  $.each(allowed_lines, function(){
                       if(this == theIPid) {
                          in_allowed_list = true;
                          return false;
                       }
                  });

                  clearTimeout(theIPids.timeout);
                  theIPids.timeout = null;
                  theIPids.timeout=setTimeout(function(){
          
                     if (in_allowed_list == false) {
                         //ADD to Allowed list
                         var ipatxt = $.trim(theIPids.theipid);
                         var ipabox = $('.remain-open-input textarea');
                         if (ipabox.val() == "") {
                             ipabox.val(ipatxt);
                         }
                         else {
                             ipabox.val(ipabox.val() + '\n' + ipatxt);
                         }
                         //UpDate Table
                         var default_allowed = '';
                         default_allowed = ipabox.val();
                         var data = {
                              "action": "update_allowed",
                              "site_value": site_value,
                              "allowed": default_allowed
                         };
                       ip_approval_reMakeCall(data, null);
                     } // END 'if'(in_allowed_list == false)
                  },1200);

                  $.each($('i[id^="'+theIPid+'"]'), function(i, e) {
                         $(this).removeClass('fa-ban').addClass('fa-plus-circle');
                         $(this).removeClass('fa-red').addClass('fa-green');
                  });
              }
       }
       else if($(this).hasClass('fa-plus-circle')){
              //IF In Allowed remove IP
              var allowed_lines= $('.remain-open-input textarea').val().split('\n');
              $.each(allowed_lines, function(i, val){
                     var val_all_line = this.split("//");
                     var val_all_line_trim = $.trim(val_all_line[0]);
                     if(val_all_line_trim == theIPid) {
                        in_allowed_list = true;
                        // Remove THIS from Banned List
                        allowed_lines.splice(i, 1);
                        return false; 
                     }
                     else if (ip_approval_is_ipv6(theIPid)){
                      if (ip_approval_is_ipv6(val_all_line_trim) | val_all_line_trim.includes(":")){
                        if (in_ipv6_range(theIPid, val_all_line_trim)){
                            var rconfirm = confirm(theIPid+wasfoundin+val_all_line_trim+'\n '+wouldYouLike6+'?\n\n');
                            if (rconfirm == true) {
                                in_allowed_list = true;
                                // Remove THIS from Banned List
                                allowed_lines.splice(i, 1);
                                return false; 
                            }
                        }
                      }
                     }
                     else if (in_ipv4_range(theIPid, val_all_line_trim)){
                        var rconfirm = confirm(theIPid+wasfoundin+val_all_line_trim+'\n '+wouldYouLike4+'?\n\n');
                        if (rconfirm == true) {
                            in_allowed_list = true;
                            // Remove THIS from Banned List
                            allowed_lines.splice(i, 1);
                            return false; 
                        }
                     }
              });

              if (in_allowed_list == true) {
                  in_allowed_list = false;
                  $('.remain-open-input textarea').val(allowed_lines.join("\n"));
                  var default_allowed = '';
                  default_allowed = $('.remain-open-input textarea').val();
                  var data = {
                       "action": "update_allowed",
                       "site_value": site_value,
                       "allowed": default_allowed
                  };
                  ip_approval_reMakeCall(data, null);

                  $.each($('i[id^="'+theIPid+'"]'), function(i, e) {
                       $(this).removeClass('fa-plus-circle').addClass('fa-asterisk');
                       $(this).removeClass('fa-green').addClass('fa-orange');
                  });
              }

              //IF In BANNED remove IP
              var banned_lines = $('.banned-input textarea').val().split('\n');
              $.each(banned_lines, function(i, val){
                  var val_ban_line = this.split("//");
                  var val_ban_line_trim = $.trim(val_ban_line[0]);
                  if(val_ban_line_trim == theIPid) {
                     in_banned_list = true;
                     // Remove THIS from Banned List
                     banned_lines.splice(i, 1);
                     return false; 
                  }
                  else if (ip_approval_is_ipv6(theIPid)){
                     if (ip_approval_is_ipv6(val_ban_line_trim) | val_ban_line_trim.includes(":")){
                        if (in_ipv6_range(theIPid, val_ban_line_trim)){
                            var rconfirm = confirm(theIPid+wasfoundin+val_ban_line_trim+'\n '+wouldYouLikeBanned6+'?\n\n');
                              if (rconfirm == true) {
                                  in_banned_list = true;
                                  // Remove THIS from Banned List
                                  banned_lines.splice(i, 1);
                                  return false; 
                              }
                        }
                     }
                  }
                  else if (in_ipv4_range(theIPid, val_ban_line_trim)){
                           var rconfirm = confirm(theIPid+wasfoundin+val_ban_line_trim+'\n '+wouldYouLikeBanned4+'?\n\n');
                           if (rconfirm == true) {
                               in_banned_list = true;
                               // Remove THIS from Banned List
                               banned_lines.splice(i, 1);
                               return false; 
                           }
                  }
              });

              clearTimeout(theIPids.timeout);
              theIPids.timeout = null;
              theIPids.timeout=setTimeout(function(){

                 if (in_banned_list == true) {
                     in_banned_list = false;
                     $('.banned-input textarea').val(banned_lines.join("\n"));
                     var default_banned = '';
                     default_banned = $('.banned-input textarea').val();
                     var data = {
                          "action": "update_banned",
                          "site_value": site_value,
                          "banned": default_banned
                     };
                   ip_approval_reMakeCall(data, null);

                   $.each($('i[id^="'+theIPid+'"]'), function(i, e) {
                          $(this).removeClass('fa-plus-circle').addClass('fa-asterisk');
                          $(this).removeClass('fa-green').addClass('fa-orange');
                   });
                 }
              },1200);

          if (in_banned_list != true && in_allowed_list != true) {
              //IF In Do Not Log remove IP
              var donotlog_lines = $('.donotlog-input textarea').val().split('\n');
              $.each(donotlog_lines, function(){
                     if(this == theIPid) {
                        in_donotlog_list = true;
                        return false;
                     }
              });


              clearTimeout(theIPids.timeout);
              theIPids.timeout = null;
              theIPids.timeout=setTimeout(function(){   
                if (in_donotlog_list == false) {
                    //ADD to Do Not Log list
                    var ipdtxt = $.trim(theIPids.theipid);
                    var ipdbox = $('.donotlog-input textarea');
                    if (ipdbox.val() == "") {
                        ipdbox.val(ipdtxt);
                    }
                    else {
                        ipdbox.val(ipdbox.val() + '\n' + ipdtxt);
                    }
                    //UpDate Table
                    var default_donotlog = '';
                    default_donotlog = ipdbox.val();
                    var data = {
                         "action": "update_donotlog",
                         "site_value": site_value,
                         "donotlog": default_donotlog
                    };
                    ip_approval_reMakeCall(data, null);
                } // END 'if'(in_donotlog_list == false)
              },1200);

              $.each($('i[id^="'+theIPid+'"]'), function(i, e) {
                     $(this).removeClass('fa-minus-circle').addClass('fa-asterisk');
                     $(this).addClass('fa-orange');
              });

          }
       }
       else if($(this).hasClass('fa-asterisk')){
              //IF In Do Not Log remove IP
              var donotlog_lines= $('.donotlog-input textarea').val().split('\n');
              $.each(donotlog_lines, function(i, val){
                     var val_donot_line = this.split("//");
                     var val_donot_line_trim = $.trim(val_donot_line[0]);
                     if(val_donot_line_trim == theIPid) {
                        in_donotlog_list = true;
                        // Remove THIS from Banned List
                        donotlog_lines.splice(i, 1);
                        return false; 
                     }
                     else if (ip_approval_is_ipv6(theIPid)){
                      if (ip_approval_is_ipv6(val_donot_line_trim) | val_donot_line_trim.includes(":")){
                        if (in_ipv6_range(theIPid, val_donot_line_trim)){
                            var rconfirm = confirm(theIPid+wasfoundin+val_donot_line_trim+'\n Would you like to delete this IPv6 Range from the Do Not Log List?\n\n');
                            if (rconfirm == true) {
                                in_donotlog_list = true;
                                // Remove THIS from Banned List
                                donotlog_lines.splice(i, 1);
                                return false; 
                            }
                        }
                      }
                     }
                     else if (in_ipv4_range(theIPid, val_donot_line_trim)){
                        var rconfirm = confirm(theIPid+wasfoundin+val_donot_line_trim+'\n Would you like to delete this IP Range from the Do Not Log List?\n\n');
                        if (rconfirm == true) {
                            in_donotlog_list = true;
                            // Remove THIS from Banned List
                            donotlog_lines.splice(i, 1);
                            return false; 
                        }
                     }
              });

              if (in_donotlog_list == true) {
                  in_donotlog_list = false;
                  $('.donotlog-input textarea').val(donotlog_lines.join("\n"));
                  var default_donotlog = '';
                  default_donotlog = $('.donotlog-input textarea').val();
                  var data = {
                       "action": "update_donotlog",
                       "site_value": site_value,
                       "donotlog": default_donotlog
                  };
                  ip_approval_reMakeCall(data, null);

                  $.each($('i[id^="'+theIPid+'"]'), function(i, e) {
                       $(this).removeClass('fa-asterisk').addClass('fa-minus-circle');
                       $(this).removeClass('fa-orange');
                  });
              }

              //IF In Allowed remove IP
              var allowed_lines= $('.remain-open-input textarea').val().split('\n');
              $.each(allowed_lines, function(i, val){
                     var val_all_line = this.split("//");
                     var val_all_line_trim = $.trim(val_all_line[0]);
                     if(val_all_line_trim == theIPid) {
                        in_allowed_list = true;
                        // Remove THIS from Banned List
                        allowed_lines.splice(i, 1);
                        return false; 
                     }
                     else if (ip_approval_is_ipv6(theIPid)){
                      if (ip_approval_is_ipv6(val_all_line_trim) | val_all_line_trim.includes(":")){
                        if (in_ipv6_range(theIPid, val_all_line_trim)){
                            var rconfirm = confirm(theIPid+wasfoundin+val_all_line_trim+'\n '+wouldYouLike6+'?\n\n');
                            if (rconfirm == true) {
                                in_allowed_list = true;
                                // Remove THIS from Banned List
                                allowed_lines.splice(i, 1);
                                return false; 
                            }
                        }
                      }
                     }
                     else if (in_ipv4_range(theIPid, val_all_line_trim)){
                        var rconfirm = confirm(theIPid+wasfoundin+val_all_line_trim+'\n '+wouldYouLike4+'?\n\n');
                        if (rconfirm == true) {
                            in_allowed_list = true;
                            // Remove THIS from Banned List
                            allowed_lines.splice(i, 1);
                            return false; 
                        }
                     }
              });

              if (in_allowed_list == true) {
                  in_allowed_list = false;
                  $('.remain-open-input textarea').val(allowed_lines.join("\n"));
                  var default_allowed = '';
                  default_allowed = $('.remain-open-input textarea').val();
                  var data = {
                       "action": "update_allowed",
                       "site_value": site_value,
                       "allowed": default_allowed
                  };
                  ip_approval_reMakeCall(data, null);

                  $.each($('i[id^="'+theIPid+'"]'), function(i, e) {
                       $(this).removeClass('fa-plus-circle').addClass('fa-minus-circle');
                       $(this).removeClass('fa-green');
                  });
              }

              //IF In BANNED remove IP
              var banned_lines = $('.banned-input textarea').val().split('\n');
              $.each(banned_lines, function(i, val){
                  var val_ban_line = this.split("//");
                  var val_ban_line_trim = $.trim(val_ban_line[0]);
                  if(val_ban_line_trim == theIPid) {
                     in_banned_list = true;
                     // Remove THIS from Banned List
                     banned_lines.splice(i, 1);
                     return false; 
                  }
                  else if (ip_approval_is_ipv6(theIPid)){
                     if (ip_approval_is_ipv6(val_ban_line_trim) | val_ban_line_trim.includes(":")){
                        if (in_ipv6_range(theIPid, val_ban_line_trim)){
                            var rconfirm = confirm(theIPid+wasfoundin+val_ban_line_trim+'\n '+wouldYouLikeBanned6+'?\n\n');
                              if (rconfirm == true) {
                                  in_banned_list = true;
                                  // Remove THIS from Banned List
                                  banned_lines.splice(i, 1);
                                  return false; 
                              }
                        }
                     }
                  }
                  else if (in_ipv4_range(theIPid, val_ban_line_trim)){
                           var rconfirm = confirm(theIPid+wasfoundin+val_ban_line_trim+'\n '+wouldYouLikeBanned4+'?\n\n');
                           if (rconfirm == true) {
                               in_banned_list = true;
                               // Remove THIS from Banned List
                               banned_lines.splice(i, 1);
                               return false; 
                           }
                  }
              });

              clearTimeout(theIPids.timeout);
              theIPids.timeout = null;
              theIPids.timeout=setTimeout(function(){

                 if (in_banned_list == true) {
                     in_banned_list = false;
                     $('.banned-input textarea').val(banned_lines.join("\n"));
                     var default_banned = '';
                     default_banned = $('.banned-input textarea').val();
                     var data = {
                          "action": "update_banned",
                          "site_value": site_value,
                          "banned": default_banned
                     };
                   ip_approval_reMakeCall(data, null);

                   $.each($('i[id^="'+theIPid+'"]'), function(i, e) {
                          $(this).removeClass('fa-plus-circle').addClass('fa-minus-circle');
                          $(this).removeClass('fa-green');
                   });
                 }
              },1200);
              //  $(this).toggleClass('fa-plus-circle').toggleClass('fa-minus-circle');
       }
     });
   }, 4000);
 });

 /* Page URL Change
 ------------------------------------------------------------------------*/
 $('[name="page_url"]').change(function(){
   var page_url = '';
   ip_approval_page_urlFunction();
   $('[name="page_url"]').each(function(i,e) {
     if ($(e).is(':checked')) {
         var comma = page_url.length===0?'':',';
         page_url += (comma+e.value.replace(/\//g, "\\/"));
     }
   });
   var data = {
      "action": "page_checked",
      "site_value": site_value,
      "page_urls": page_url
   };
   ip_approval_reMakeCall(data); // For Results ADD~> , 'succeed'
 });


 /*   NAV TAB WRAPPER 
 --------------------------------------------------------------------*/
 var get_visitors_true = false;
 $('.nav-tab-wrapper a').on('click', function(e) {
   var clicked = $(this).attr('href');
   if (clicked.indexOf('#') == -1) {
       return true;
   }
   $('.nav-tab-wrapper a').not(this).removeClass('nav-tab-active');
   $(this).addClass('nav-tab-active').blur();
   $('.ipwrap').not(clicked).hide();
   $(clicked).show();
   e.preventDefault();
   <?php if($this->options['has_ug'] > 0) { ?>
   if (clicked == '#ip_approval_page_visits') {
       if (!get_visitors_true) {
           $('.tablesorter').css({'display' : 'none'});
           $('.pagenate-link').css({'display' : 'none'});
           $('.FilterVisitInput').css({'display' : 'none'});
           get_visitors_true = true;
           ip_approval_pagenateDisplay('show');
           $.post(ajaxurl, {
               'action': 'ip_approval_ajax_action',
               'do_action': 'get_visitors',
               security: ip_approval_nonce,
               page: AppViews.evt_data_page,
               per_page: AppViews.evt_data_per_page
           }, function(response) {
               $('.pagenate-content').html(response.data.message);
               if (response.data.status == 'Success') {
                  if (response.data.count){
                      var totalResults = response.data.count;
                      $('ul#paginate').empty();
                      if(totalResults > 0){
                         $('.tablesorter').css({'display' : 'table'});
                         $('.FilterVisitInput').css({'display' : 'inline-block'});
                         $('.pagenate-link').css({'display' : 'block'});
                         AppViews.paginateTotal=Math.ceil(totalResults/AppViews.evt_data_per_page);
                         var newPaginate = ip_approval_makePaginate($("#paginate li#1").not('.pagenate-prev'), AppViews.evt_data_page, AppViews.paginateTotal);
                         $('ul#paginate').html(newPaginate);
                         $('#paginate li').css({'color' : ''});
                         $("#paginate li#1").not('.pagenate-prev,.pagenate-next').css({'color' : '#ff375f'});
                      }
                  }
                  ip_approval_pagenateDisplay('fadeout');
                  ip_approval_FilterResults();
               }
               if (response.data.status == 'Failed') {
                   $('.tablesorter > thead').css({'display' : 'none'});
                   $('.tablesorter').css({'display' : 'table'});
               }
               if (response.data.errorMessage) {
                   ip_approval_pagenateDisplay('fadeout');
                   $('.pagenate-content').html(response.data.errorMessage);
               }
           });
           $('select[name^="per_page"] option[value="'+per_page+'"]').attr("selected","selected");
       }
   }
   <?php } ?>
   if (clicked != '#ip_approval_page_visits') {
       ip_approval_pagenateDisplay('fadeout');
   }
 });

 /*   Delete Checked Visits
 --------------------------------------------------------------------*/
 $('#visits_check_all_delete').on('click', function(e) {
   ip_approval_scrollUp();
   var visited_page_url = '';
   $('[name="visited_page_url"]').each(function(i,e) {
     if ($(e).is(':checked')) {
         var comma = visited_page_url.length===0?'':',';
         visited_page_url += (comma+e.value.replace(/\//g, "\\/"));
     }
   });
   if (visited_page_url){
       $('.tablesorter').css({'display' : 'none'});
       $('.pagenate-link').css({'display' : 'none'});
       $('.FilterVisitInput').css({'display' : 'none'});
       ip_approval_pagenateDisplay('show');
       $.post(ajaxurl, {
           'action': 'ip_approval_ajax_action',
           'do_action': 'delete_visitors',
           security: ip_approval_nonce,
           list: visited_page_url,
           page: AppViews.evt_data_page,
           per_page: AppViews.evt_data_per_page
       }, function(response) {
           $('.pagenate-content').html(response.data.message);
           if (response.data.status == 'Success') {
               if (response.data.count){
                   var totalResults = response.data.count;
                   $('ul#paginate').empty();
                   if(totalResults > 0){
                      $('.tablesorter').css({'display' : 'table'});
                      $('.FilterVisitInput').css({'display' : 'inline-block'});
                      $('.pagenate-link').css({'display' : 'block'});
                      AppViews.paginateTotal=Math.ceil(totalResults/AppViews.evt_data_per_page);
                      var newPaginate = ip_approval_makePaginate($("#paginate li#1").not('.pagenate-prev'), AppViews.evt_data_page, AppViews.paginateTotal);
                      $('ul#paginate').html(newPaginate);
                      $('#paginate li').css({'color' : ''});
                      $("#paginate li#"+AppViews.evt_data_page).not('.pagenate-prev,.pagenate-next').css({'color' : '#ff375f'});
                   }
               }
               ip_approval_pagenateDisplay('fadeout');
               ip_approval_FilterResults();
           }
           if (response.data.status == 'Failed') {
               $('.tablesorter > thead').css({'display' : 'none'});
               $('.tablesorter').css({'display' : 'table'});
           }
       });
       ip_approval_check_pagenate();
       $('select[name^="per_page"] option[value="'+AppViews.evt_data_per_page+'"]').attr("selected","selected");
       $("#VisitorTable thead tr th").removeClass("headerSortUp").removeClass("headerSortDown");
       $('[name="visits_check_all"]').prop('checked', false);
       $('[name="visits_check_all_2"]').prop('checked', false);
   }
   document.activeElement && document.activeElement.blur();
 });


 /*   Per Page
 --------------------------------------------------------------------*/
 AppViews.bind = function() {
   $('select[name^="per_page"]').on('change',{ value: AppViews.evt_data_per_page, site: AppViews.evt_data_site },function (evt) {
     ip_approval_scrollUp();
     AppViews.evt_data_page = 1;
     AppViews.evt_data_site = evt.data.site;
     $('select[name^="per_page"] option[value="'+AppViews.evt_data_per_page+'"]').removeAttr('selected');
     AppViews.evt_data_per_page = $(this).val();
     $('select[name^="per_page"] option[value="'+AppViews.evt_data_per_page+'"]').attr("selected","selected");

     $('.tablesorter').css({'display' : 'none'});
     $('.pagenate-link').css({'display' : 'none'});
     $('.FilterVisitInput').css({'display' : 'none'});
     ip_approval_pagenateDisplay('show');
     $.post(ajaxurl, {
          'action': 'ip_approval_ajax_action',
          'do_action': 'get_visitors',
          security: ip_approval_nonce,
          page: AppViews.evt_data_page,
          per_page: AppViews.evt_data_per_page
     }, function(response) {
          $('.pagenate-content').html(response.data.message);
          if (response.data.status == 'Success') {
              if (response.data.count){
                  var totalResults = response.data.count;
                  $('ul#paginate').empty();
                  if(totalResults > 0){
                     $('.tablesorter').css({'display' : 'table'});
                     $('.FilterVisitInput').css({'display' : 'inline-block'});
                     $('.pagenate-link').css({'display' : 'block'});
                     AppViews.paginateTotal=Math.ceil(totalResults/AppViews.evt_data_per_page);
                     var newPaginate = ip_approval_makePaginate($("#paginate li#1").not('.pagenate-prev'), AppViews.evt_data_page, AppViews.paginateTotal);
                     $('ul#paginate').html(newPaginate);
                     $('#paginate li').css({'color' : ''});
                     $("#paginate li#1").not('.pagenate-prev,.pagenate-next').css({'color' : '#ff375f'});
                  }
              }
              ip_approval_pagenateDisplay('fadeout');
              ip_approval_FilterResults();
          }
          if (response.data.status == 'Failed') {
              $('.tablesorter > thead').css({'display' : 'none'});
              $('.tablesorter').css({'display' : 'table'});
          }
     });
     ip_approval_check_pagenate();
     $("#VisitorTable thead tr th").removeClass("headerSortUp").removeClass("headerSortDown");
     $('[name="visits_check_all"]').prop('checked', false);
     $('[name="visits_check_all_2"]').prop('checked', false);
   });
 };
 AppViews.init = function() {
    AppViews.bind();
 };
 $(AppViews.init);

 /*   Check All Visits
 --------------------------------------------------------------------*/
 isChecked = false;
 isChecked2 = false;
 $("#visits-checkAll, #visits-checkAll-2").change(function () {
   if (isChecked == false && isChecked2 == false) {
       if (this.id == "visits-checkAll") {
           isChecked = true;
           $('#visits-checkAll-2').prop('checked',$('#visits-checkAll').prop("checked")).change();
       }
       if (this.id == "visits-checkAll-2") {
           isChecked2 = true;
           $('#visits-checkAll').prop('checked',$('#visits-checkAll-2').prop("checked")).change();
       }
       $('[name="visited_page_url"]').each(function(i,e) {
         if (!$(e).parent().parent().hasClass("visible")) {
             $(e).prop("checked", false);
         }
         else {
             $(e).prop('checked',$('#visits-checkAll').prop("checked"));
         }
       });
   }
   isChecked = false;
   isChecked2 = false;
 });

 /*   Pageinate
 --------------------------------------------------------------------*/
 $('#paginate').on('click','li',function() {
    ip_approval_scrollUp();
    AppViews.evt_data_page = this.id;
    $('.tablesorter').css({'display' : 'none'});
    $('.pagenate-link').css({'display' : 'none'});
    $('.FilterVisitInput').css({'display' : 'none'});
    ip_approval_pagenateDisplay('show');
    $.post(ajaxurl, {
        'action': 'ip_approval_ajax_action',
        'do_action': 'get_visitors',
        security: ip_approval_nonce,
        page: AppViews.evt_data_page,
        per_page: AppViews.evt_data_per_page
    }, function(response) {
        $('.pagenate-content').html(response.data.message);
        if (response.data.status == 'Success') {
           if (response.data.count){
               var totalResults = response.data.count;
               $('ul#paginate').empty();
               if(totalResults > 0){
                  $('.tablesorter').css({'display' : 'table'});
                  $('.FilterVisitInput').css({'display' : 'inline-block'});
                  $('.pagenate-link').css({'display' : 'block'});
                  AppViews.paginateTotal=Math.ceil(totalResults/AppViews.evt_data_per_page);
                  var newPaginate = ip_approval_makePaginate($("#paginate li#1").not('.pagenate-prev'), AppViews.evt_data_page, AppViews.paginateTotal);
                  $('ul#paginate').html(newPaginate);
                  $('#paginate li').css({'color' : ''});
                  $("#paginate li#"+AppViews.evt_data_page).not('.pagenate-prev,.pagenate-next').css({'color' : '#ff375f'});
               }
           }
           ip_approval_pagenateDisplay('fadeout');
           ip_approval_FilterResults();
        }
        if (response.data.status == 'Failed') {
           $('.tablesorter > thead').css({'display' : 'none'});
           $('.tablesorter').css({'display' : 'table'});
        }
    });
    ip_approval_check_pagenate();
    $("#VisitorTable thead tr th").removeClass("headerSortUp").removeClass("headerSortDown");
    $('[name="visits_check_all"]').prop('checked', false);
    $('[name="visits_check_all_2"]').prop('checked', false);
 });

 /*   OnChange for ALL pages
 --------------------------------------------------------------------*/
 $("#ippagesall").change(function () {
    $('[name="ippages[]"]').filter(':not([data-page-type=Access]):not([data-page-type=Banned])').prop('checked', $(this).prop("checked")).change();
 });
 $('[name="ippages[]"]').change(function(){
    if($('[name="ippages[]"]:checked').filter(':not([data-page-type=Access]):not([data-page-type=Banned])').length == $('[name="ippages[]"]').filter(':not([data-page-type=Access]):not([data-page-type=Banned])').length) {
       $('#ippagesall').prop('checked', true);
    }
    else {
       if($('[name="ippages[]"]:checked').filter(':not([data-page-type=Access]):not([data-page-type=Banned])').length == $('[name="ippages[]"]').filter(':not([data-page-type=Access]):not([data-page-type=Banned])').length) {
         $('#ippagesall').prop('checked', false);
       }
    }
 });

 <?php if(empty($this->has_ug['0'])) { ?>
 /*   OnChange for Options page button 
 --------------------------------------------------------------------*/
 $('li[name="ip_approval_o_c_unit"]').on('click', function(){
     var open_closed_unit = $(this).attr('id');
     $(this).attr("class", "active");
     $('li[name="ip_approval_o_c_unit"]').not(this).removeClass("active");
     $('input[name="ip_approval_o_c_unit"]').val(open_closed_unit);
 });

 $('.main-switch .slider.round').click(function(){
     var switchchecked = $(this).attr('id');
     $('[name="'+switchchecked+'"]').prop('checked', !($('[name="'+switchchecked+'"]').is(':checked')));
 });
 <?php } ?>

 /*  ADMIN BAR SAVE 
 --------------------------------------------------------------------*/
 $(".custom-ip-save a, li#wp-admin-bar-custom-button .ip-save").click(function(e){
      e.preventDefault();
      $('input#save').click();
 });

 /*  ADMIN BAR SAVE 
 --------------------------------------------------------------------*/
 $(document).keydown(function(event) {
     if (!( String.fromCharCode(event.which).toLowerCase() == 's' && event.ctrlKey) && !(event.which == 19)) {
         return true;
     }
     event.preventDefault();
     $('input#save').click();
     return false;
 });

 /*  Connect Account Input
 --------------------------------------------------------------------*/
 $("#ip_id_").click(function(e){
     var ip_peav = '<?php echo __('Please enter a valid', 'ip-address-approval');?>';
     var ip_pea = '<?php echo __('Please enter a', 'ip-address-approval');?>';
     ip_approval_idInput = document.getElementById('ip_approval_ip_id_input');
     ip_approval_idInput.addEventListener('invalid', function() {
        if (ip_approval_idInput.validity.valueMissing){
            ip_approval_idInput.setCustomValidity(ip_peav+" User ID");
            $('#ip_approval_ip_id_input').css({'border-color':'#f00f00'});
            e.preventDefault();
        }
        if (ip_approval_idInput.validity.patternMismatch){
            ip_approval_idInput.setCustomValidity(ip_pea+" User ID");
            $('#ip_approval_ip_id_input').css({'border-color':'#f00f00'});
            e.preventDefault();
        }
        ip_approval_idInput.addEventListener('input', function(){
            ip_approval_idInput.setCustomValidity('');
            $('#ip_approval_ip_id_input').css({'border-color':''});
        });
  }, false);

  ip_approval_site_idInput = document.getElementById('ip_approval_site_id_input');
  ip_approval_site_idInput.addEventListener('invalid', function() {
     if (ip_approval_site_idInput.validity.valueMissing){
         ip_approval_site_idInput.setCustomValidity(ip_peav+" User ID");
         $('#ip_approval_site_id_input').css({'border-color':'#f00f00'});
         e.preventDefault();
     }
     if (ip_approval_site_idInput.validity.patternMismatch){
         ip_approval_site_idInput.setCustomValidity(ip_pea+" User ID");
         $('#ip_approval_site_id_input').css({'border-color':'#f00f00'});
         e.preventDefault();
     }
     ip_approval_site_idInput.addEventListener('input', function(){
         ip_approval_site_idInput.setCustomValidity('');
         $('#ip_approval_site_id_input').css({'border-color':''});
     });
  }, false);

  ip_approval_api_keyInput = document.getElementById('ip_approval_api_key_input');
  ip_approval_api_keyInput.addEventListener('invalid', function() {
     if (ip_approval_api_keyInput.validity.valueMissing){
         ip_approval_api_keyInput.setCustomValidity(ip_peav+" API Key");
         $('#ip_approval_api_key_input').css({'border-color':'#f00f00'});
         e.preventDefault();
     }
     if (ip_approval_api_keyInput.validity.patternMismatch){
         ip_approval_api_keyInput.setCustomValidity(ip_pea+" API Key");
         $('#ip_approval_api_key_input').css({'border-color':'#f00f00'});
         e.preventDefault();
     }
     ip_approval_api_keyInput.addEventListener('input', function(){
         ip_approval_api_keyInput.setCustomValidity('');
         $('#ip_approval_api_key_input').css({'border-color':''});
     });
  }, false);
  
  api_secretInput = document.getElementById('ip_approval_api_secret_input');
  api_secretInput.addEventListener('invalid', function() {
     if (api_secretInput.validity.valueMissing){
         api_secretInput.setCustomValidity(ip_peav+" API Secret");
         $('#ip_approval_api_secret_input').css({'border-color':'#f00f00'});
         e.preventDefault();
     }
     if (api_secretInput.validity.patternMismatch){
         api_secretInput.setCustomValidity(ip_pea+" API Secret");
         $('#ip_approval_api_secret_input').css({'border-color':'#f00f00'});
         e.preventDefault();
     }
     api_secretInput.addEventListener('input', function(){
         api_secretInput.setCustomValidity('');
         $('#ip_approval_api_secret_input').css({'border-color':''});
     });
  }, false);
 });
 <?php } ?>

 /*   Nav Click
 --------------------------------------------------------------------*/
 <?php if (!empty($this->api_message)) { ?>
 $(document).ready(function () {
    $("a#ip_approval-tab_logs").click(); $(window).scrollTop(0);
 });
 <?php } elseif (!$this->is_api_key AND !$this->is_api_secret) { ?>
 $(document).ready(function () {
    $("a#ip_approval-tab_ip_id").click(); $(window).scrollTop(0);
 });
 <?php } elseif (!$this->is_ip_id) { ?>
 $(document).ready(function () {
    $("a#ip_approval-tab_ip_id").click(); $(window).scrollTop(0);
 });
 <?php } elseif (isset($_GET['link']) && $_GET['link'] === 'cred_check') { ?>
 $(document).ready(function () {
    $("a#ip_approval-tab_ip_id").click(); $(window).scrollTop(0);
    var ip_approvalnewURL = location.href.split("&link=cred_check")[0];
    window.history.pushState('object', document.title, ip_approvalnewURL);
 });
 <?php } ?>
</script>