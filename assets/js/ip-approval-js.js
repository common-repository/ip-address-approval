    $ = jQuery;

    /* Scroll Up - when clicking Delete, Paginate and Per Page
    ------------------------------------------------------------------------*/
    function ip_approval_scrollUp() {
       $('html, body').animate({
         scrollTop: $("#ip_approval_options").offset().top
       }, 0);
    }

    /* Pagenate Display
    ------------------------------------------------------------------------*/
    function ip_approval_pagenateDisplay(action) {
       if (action === "show") {
           $('.pagenate-load').fadeIn("slow");
           setTimeout(function() {
              $('.pagenate-load-text').fadeIn("slow");
           }, 300);
           setTimeout(function() {
               $('.pagenate-load-text').fadeOut("slow");
               $('.pagenate-load').fadeOut("slow");
           }, 11000);
       }
       if (action === "fadeout") {
           $('.pagenate-load-text').fadeOut("slow");
           $('.pagenate-load').fadeOut("slow");
       }
       if (action === "hide") {
           $('.pagenate-load-text').hide();
           $('.pagenate-load').hide();
       }
    }

    /* Visit Page URL Function
    ------------------------------------------------------------------------*/
    ip_approval_visit_page_urlFunction = function() {
       if ($('[name="visited_page_url"]:checked').length == $('[name="visited_page_url"]').length) {
           $('[name="visits_check_all"]').prop('checked', true);
           $('[name="visits_check_all_2"]').prop('checked', true);
       }
       else {
           $('[name="visits_check_all"]').prop('checked', false);
           $('[name="visits_check_all_2"]').prop('checked', false);
       }
    }

    /* Page URL Function
    ------------------------------------------------------------------------*/
    ip_approval_page_urlFunction = function() {
       if ($('[name="page_url"]:checked').length == $('[name="page_url"]').length) {
           $('[name="check_all"]').prop('checked', true);
       }
       else {
           $('[name="check_all"]').prop('checked', false);
       }
    }
    ip_approval_page_urlFunction();


   /* apply filterTable to all tables on this page
   ------------------------------------------------------------------------*/
   ip_approval_FilterResults = function(){
     setTimeout(function() {
        $('table.tablesorter').filterTable({
          filterExpression: 'filterTableFindAny',
          inputSelector: '.FilterVisitInput',
          highlightClass: 'found',
          minRows:1
        });
        $("#VisitorTable").tablesorter({headers:{0:{sorter:false}}});
        $("#VisitorTable").trigger("update");
     }, 800);
   }

   /* Make Paginate for visitor table
   ------------------------------------------------------------------------*/
   ip_approval_makePaginate = function(thisIS,evt_data_page,paginateTotal) {
      var parseMinusVisit = parseInt(evt_data_page) - parseInt('1');
      if (parseMinusVisit == 0) {
          parseMinusVisit = 1;
      }
      var parsePlusVisit = parseInt(evt_data_page) + parseInt('1');
      if (parsePlusVisit > paginateTotal) {
          parsePlusVisit = evt_data_page;
      }

      ///////////////////////////
      //setup starting point 'max' is equal to number of links shown
      var max = 5;
      if (evt_data_page < max) {
          sp = 1;
      }
      else if(evt_data_page >= (paginateTotal - Math.floor(max / 2)) ) {
         sp = paginateTotal - max + 1;
      }
      else if(evt_data_page >= max) {
         sp = evt_data_page - Math.floor(max/2);
      }

      var newPaginate = '';
      if (paginateTotal > 1) {
          newPaginate += '<li class="pagenate-prev" id="' + parseMinusVisit + '">&laquo;</li>&nbsp';
      }

      // If the current page is greatewr than or equal to $max then show link to 1st page
      if (evt_data_page >= max) {
          newPaginate += '<li id="1">1</li>..';
      }
      // Loop though max number of pages shown and show links either side equal to 'max' / 2 
      for(i = sp; i <= (sp + max - 1);i++) {
         if (i > paginateTotal) {
             continue;
         }
         if (evt_data_page == i) {
             newPaginate += '<li id="' + i + '">' + i + '</li>';
         }
         else {
            newPaginate += '<li id="' + i + '">' + i + '</li>';
         }
      }

      //If the current page is less than say the last page minus $max pages divided by 2 
      if (evt_data_page < (paginateTotal - Math.floor(max/2))) {
          newPaginate += '..<li id="' + paginateTotal + '">' + paginateTotal + '</li>';
      }
      if (paginateTotal > 1) {
          newPaginate += '&nbsp;<li class="pagenate-next" id="' + parsePlusVisit + '">&raquo;</li>';
      }
      return newPaginate;
   }

   /* START IPv4 
   --------------------------------------------------------------------*/
   ip_approval_dot2num = function(dot) {
     var d = dot.split('.');
     return ((((((+d[0]) * 256) + (+d[1])) * 256) + (+d[2])) * 256) + (+d[3]);
   }

   ip_approval_num2dot = function(num) {
     var d = num % 256;
     for (var i = 3; i > 0; i--) {
        num = Math.floor(num / 256);
        d = num % 256 + '.' + d;
     }
     return d;
   }

   ip_approval_long2ip = function (proper_address) {
     // Converts an (IPv4) Internet network address into a string in Internet standard dotted format
     // discuss at: http://phpjs.org/functions/long2ip
     // *     example 1: ip_approval_long2ip( 3221234342 );
     // *     returns 1: '192.0.34.166'
     var output = false;
     if (!isNaN(proper_address) && (proper_address >= 0 || proper_address <= 4294967295)) {
         output = Math.floor(proper_address / Math.pow(256, 3)) + '.' +
         Math.floor((proper_address % Math.pow(256, 3)) / Math.pow(256, 2)) + '.' +
         Math.floor(((proper_address % Math.pow(256, 3)) % Math.pow(256, 2)) / Math.pow(256, 1)) + '.' +
         Math.floor((((proper_address % Math.pow(256, 3)) % Math.pow(256, 2)) % Math.pow(256, 1)) / Math.pow(256, 0));
     }
     return output;
   }

   ip_approval_ip2long = function (argIP) {
     //  discuss at: http://locutus.io/php/ip2long/
     let i = 0;
     const pattern = new RegExp([
         '^([1-9]\\d*|0[0-7]*|0x[\\da-f]+)',
         '(?:\\.([1-9]\\d*|0[0-7]*|0x[\\da-f]+))?',
         '(?:\\.([1-9]\\d*|0[0-7]*|0x[\\da-f]+))?',
         '(?:\\.([1-9]\\d*|0[0-7]*|0x[\\da-f]+))?$'
     ].join(''), 'i');
     argIP = argIP.match(pattern); // Verify argIP format.
     if (!argIP) {
         // Invalid format.
         return false;
     }
     // Reuse argIP variable for component counter.
     argIP[0] = 0;
     for (i = 1; i < 5; i += 1) {
         argIP[0] += !!((argIP[i] || '').length);
         argIP[i] = parseInt(argIP[i]) || 0;
     }
     argIP.push(256, 256, 256, 256);
     // Recalculate overflow of last component supplied to make up for missing components.
     argIP[4 + argIP[0]] *= Math.pow(256, 4 - argIP[0]);
     if (argIP[1] >= argIP[5] ||
         argIP[2] >= argIP[6] ||
         argIP[3] >= argIP[7] ||
         argIP[4] >= argIP[8]) {
         return false;
     }
     return argIP[1] * (argIP[0] === 1 || 16777216) +
         argIP[2] * (argIP[0] <= 2 || 65536) +
         argIP[3] * (argIP[0] <= 3 || 256) +
         argIP[4] * 1;
   }

   ip_approval_getRange = function(range) {
     if (range.indexOf('/') > 0) {
         var ranges = new Array();
         var dash = range.indexOf('/');

         LoIP = range.substring(0, dash);
         var LoIPparts = LoIP.split('.');
         if (LoIPparts.length < 4) {
             while(LoIPparts.length < 4) {
                LoIPparts.push('0');
             }
         }
         ranges.LoIP = LoIPparts[0] +'.'+LoIPparts[1] +'.'+LoIPparts[2] +'.'+LoIPparts[3];

         HiIP = range.substring(dash + 1, range.len);
         var HiIPparts = HiIP.split('.');
         if (HiIPparts.length == 1) {
            if(HiIP < 33) {
               start = ip_approval_ip2long(ranges.LoIP);
               ranges.HiIP = ip_approval_long2ip(Math.pow(2, 32 - HiIP) + start - 1);
               return ranges;
            }
            if(HiIP == 255) {
               ranges.HiIP = LoIPparts[0] +'.'+LoIPparts[1] +'.'+LoIPparts[2] +'.'+HiIP;
               return ranges;
            }
         }
         if (HiIPparts.length > 1 && HiIPparts.length < 4) {
            while(HiIPparts.length < 4) {
                HiIPparts.push('255');
            }
            ranges.HiIP = HiIPparts[0] +'.'+HiIPparts[1] +'.'+HiIPparts[2] +'.'+HiIPparts[3];
            return ranges;
         }
     }
     if (range.indexOf('*') > 0) {
         var ranges = new Array();
         ranges.LoIP = range.replace(/\*/g, "0");
         ranges.HiIP = range.replace(/\*/g, "255");
         return ranges;
     }
     if (range.indexOf('-') > 0) {
         var ranges = new Array();
         var dash = range.indexOf('-');
         ranges.LoIP  = range.substring(0, dash);
         ranges.HiIP = range.substring(dash + 1, range.len);
         return ranges;
     }
     else {
         // single IP comparison
         return false;
     }
   }

   in_ipv4_range = function(ip, array) {
     ranges = ip_approval_getRange(array);
     if(ranges) {
       if (ip_approval_dot2num(ip) >= ip_approval_dot2num(ranges.LoIP) && ip_approval_dot2num(ip) <= ip_approval_dot2num(ranges.HiIP)) {
          return true;
       }
       else {
          return false;
       }
     }
     return false;
   }

   /* START IPv6
   --------------------------------------------------------------------*/
   function ip_approval_inet_pton(a) {
      var r
      var m
      var x
      var i
      var j
      var f = String.fromCharCode
      var invAdd = 'Invalid IPv6 Address'
      // IPv4
      m = a.match(/^(?:\d{1,3}(?:\.|$)){4}/)
      if (m) {
          m = m[0].split('.')
          m = f(m[0]) + f(m[1]) + f(m[2]) + f(m[3])
          // Return if 4 bytes, otherwise false.
          return m.length === 4 ? m : false
      }

      // IPv6
      r = /^((?:[\da-f]{1,4}(?::|)){0,8})(::)?((?:[\da-f]{1,4}(?::|)){0,8})$/
      m = a.match(r)
      if (m) {
          // Translate each hexadecimal value.
          for (j = 1; j < 4; j++) {
             // Indice 2 is :: and if no length, continue.
             if (j === 2 || m[j].length === 0) {
                continue
             }
             m[j] = m[j].split(':')
             for (i = 0; i < m[j].length; i++) {
                m[j][i] = parseInt(m[j][i], 16)
                // Would be NaN if it was blank, return false.
                if (isNaN(m[j][i])) {
                   // Invalid IP.
                   return false
                }
                m[j][i] = f(m[j][i] >> 8) + f(m[j][i] & 0xFF)
             }
             m[j] = m[j].join('')
          }
          x = m[1].length + m[3].length
          if (x === 16) {
              return m[1] + m[3]
          } else if (x < 16 && m[2].length > 0) {
              return m[1] + (new Array(16 - x + 1)).join('\x00') + m[3]
          }
      }
      // Invalid IP
      return false
   }

   ip_approval_normalize = function (a) {
        if (!ip_approval_validate(a)) {
            throw new Error(invAdd+': ' + a);
        }
        a = a.toLowerCase()
        
        nh = a.split(/\:\:/g);
        if (nh.length > 2) {
            throw new Error(invAdd+': ' + a);
        }

        sections = [];
        if (nh.length == 1) {
            // full mode
            sections = a.split(/\:/g);
            if (sections.length !== 8) {
                throw new Error(invAdd+': ' + a);
            }
        } else if (nh.length == 2) {
            // compact mode
            n = nh[0];
            h = nh[1];
            ns = n.split(/\:/g);
            hs = h.split(/\:/g);
            for (i in ns) {
                sections[i] = ns[i];
            }
            for (i = hs.length; i > 0; --i) {
                sections[7 - (hs.length - i)] = hs[i - 1];
            }
        }
        for (i = 0; i < 8; ++i) {
            if (sections[i] === undefined) {
                sections[i] = '0000';
            }
            sections[i] = ip_approval_leftPad(sections[i], '0', 4);
        }
        return sections.join(':');
   };

   ip_approval_abbreviate = function (a) {
        if (!ip_approval_validate(a)) {
            throw new Error(invAdd+': ' + a);
        }
        a = ip_approval_normalize(a);
        a = a.replace(/0000/g, 'g');
        a = a.replace(/\:000/g, ':');
        a = a.replace(/\:00/g, ':');
        a = a.replace(/\:0/g, ':');
        a = a.replace(/g/g, '0');
        sections = a.split(/\:/g);
        zPreviousFlag = false;
        zeroStartIndex = -1;
        zeroLength = 0;
        zStartIndex = -1;
        zLength = 0;
        for (i = 0; i < 8; ++i) {
            section = sections[i];
            zFlag = (section === '0');
            if (zFlag && !zPreviousFlag) {
                zStartIndex = i;
            }
            if (!zFlag && zPreviousFlag) {
                zLength = i - zStartIndex;
            }
            if (zLength > 1 && zLength > zeroLength) {
                zeroStartIndex = zStartIndex;
                zeroLength = zLength;
            }
            zPreviousFlag = (section === '0');
        }
        if (zPreviousFlag) {
            zLength = 8 - zStartIndex;
        }
        if (zLength > 1 && zLength > zeroLength) {
            zeroStartIndex = zStartIndex;
            zeroLength = zLength;
        }
        if (zeroStartIndex >= 0 && zeroLength > 1) {
            sections.splice(zeroStartIndex, zeroLength, 'g');
        }
        a = sections.join(':');
        a = a.replace(/\:g\:/g, '::');
        a = a.replace(/\:g/g, '::');
        a = a.replace(/g\:/g, '::');
        a = a.replace(/g/g, '::');
        return a;
   };

    // Basic validation
   ip_approval_validate = function (a) {
        return /^[a-f0-9\\:]+$/ig.test(a);
   };

   ip_approval_leftPad = function (d, p, n) {
        padding = p.repeat(n);
        if (d.length < padding.length) {
            d = padding.substring(0, padding.length - d.length) + d;
        }
        return d;
   };

   ip_approval_hex2bin = function (hex) {
        return parseInt(hex, 16).toString(2)
   };

   ip_approval_bin2hex = function (bin) {
        return parseInt(bin, 2).toString(16)
   };

   ip_approval_addr2bin = function (addr) {
        nAddr = ip_approval_normalize(addr);
        sections = nAddr.split(":");
        binAddr = '';
        for (section of sections) {
            binAddr += ip_approval_leftPad(ip_approval_hex2bin(section), '0', 16);
        }
        return binAddr;
   };

   ip_approval_bin2addr = function (bin) {
        addr = [];
        for (i = 0; i < 8; ++i) {
            binPart = bin.substr(i * 16, 16);
            hexSection = ip_approval_leftPad(ip_approval_bin2hex(binPart), '0', 4);
            addr.push(hexSection);
        }
        return addr.join(':');
   };

   ip_approval_get_range = function (addr, mask0, mask1, abbr) {
        if (!ip_approval_validate(addr)) {
            throw new Error(invAdd+': ' + addr);
        }
        mask0 *= 1;
        mask1 *= 1;
        mask1 = mask1 || 128;
        if (mask0 < 1 || mask1 < 1 || mask0 > 128 || mask1 > 128 || mask0 > mask1) {
            throw new Error('Invalid IPv6 Mask.');
        }
        binAddr = ip_approval_addr2bin(addr);
        binNetPart = binAddr.substr(0, mask0);
        binHostPart = '0'.repeat(128 - mask1);
        binStartAddr = binNetPart + '0'.repeat(mask1 - mask0) + binHostPart;
        binEndAddr = binNetPart + '1'.repeat(mask1 - mask0) + binHostPart;
        if (!!abbr) {
            return {
                start: ip_approval_abbreviate(ip_approval_bin2addr(binStartAddr)),
                end: ip_approval_abbreviate(ip_approval_bin2addr(binEndAddr))
            };
        } else {
            return {
                start: ip_approval_bin2addr(binStartAddr),
                end: ip_approval_bin2addr(binEndAddr)
            };
        }
   };

   ip_approval_is_ipv6 = function(ip) {
        return /^s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3}))|:)))(%.+)?s*(\/([0-9]|[1-9][0-9]|1[0-1][0-9]|12[0-8]))?$/.test(ip);
   }

   function ip_approval_isIPBetweenRange(ip,startIP,endIP){
      if(ip_approval_inet_pton(ip)>=ip_approval_inet_pton(startIP) && ip_approval_inet_pton(ip)<=ip_approval_inet_pton(endIP)) {
         return true;
      }
      return false;
   }

   function in_ipv6_range(ip, array) {
      var lower
      var upper
      var start_address
      var end_address
      // range might be 1.2.3/24
      if(array.includes("/")) {
         thesplit = array.split("/");
         range = ip_approval_get_range(thesplit[0], thesplit[1], 128);
         return (ip_approval_isIPBetweenRange(ip,range.start,range.end));
      }
      else {
         // range might be 1.2.*.*
         if(array.includes("*")) { // a.b.*.* format
            // Just convert to A-B format by setting * to 0 for A and 255 for B
            lower = array.replace(/[*]+/g, "0");
            upper = array.replace(/[*]+/g, "ffff");
            array = lower+'-'+upper;
         }
         // range might be 1.2.3.0-1.2.3.255
         if(array.includes("-")) { // A-B format
          thesplit = array.split("-");
            return (ip_approval_isIPBetweenRange(ip,thesplit[0],thesplit[1]));
         }
      }
   }
   //END IPv6


   /* Call to DB 
   --------------------------------------------------------------------*/
   ip_approval_reMakeCall = function(data, succeed) {
     /*
       $.ajax({
          type: "POST",
          url: "main-process",
          data: data,       //POST variable name value
          success: function(data){
             if(data.status == 'success'){
                if(data.succeed == 'succeed'){
                   updateSuccess();
                   console.log(data.status);
                   if(data.page_urls){
                      console.log(data.page_urls);
                   }
                }
                //console.log(data.banned);
             } 
             else if(data.status == 'error'){
                if(succeed == 'succeed'){
                   updateSuccess('failed');
                   console.log(data.status); 
                }
                // NEED MESSAGE 'data.message' to replace  data.status BELOW
                console.log(data.status +' '+ data.status);
             }
          }
       });
     */
   }