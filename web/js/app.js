/**
 * jQuery.browser.mobile (http://detectmobilebrowser.com/)
 *
 * jQuery.browser.mobile will be true if the browser is a mobile device
 *
 **/
(function(a){(jQuery.browser=jQuery.browser||{}).mobile=/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))})(navigator.userAgent||navigator.vendor||window.opera);

/*! jQuery JSON plugin 2.4.0 | code.google.com/p/jquery-json */
(function(jQuery){'use strict';var escape=/["\\\x00-\x1f\x7f-\x9f]/g,meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'},hasOwn=Object.prototype.hasOwnProperty;jQuery.toJSON=typeof JSON==='object'&&JSON.stringify?JSON.stringify:function(o){if(o===null){return'null';}
var pairs,k,name,val,type=jQuery.type(o);if(type==='undefined'){return undefined;}
if(type==='number'||type==='boolean'){return String(o);}
if(type==='string'){return jQuery.quoteString(o);}
if(typeof o.toJSON==='function'){return jQuery.toJSON(o.toJSON());}
if(type==='date'){var month=o.getUTCMonth()+1,day=o.getUTCDate(),year=o.getUTCFullYear(),hours=o.getUTCHours(),minutes=o.getUTCMinutes(),seconds=o.getUTCSeconds(),milli=o.getUTCMilliseconds();if(month<10){month='0'+month;}
if(day<10){day='0'+day;}
if(hours<10){hours='0'+hours;}
if(minutes<10){minutes='0'+minutes;}
if(seconds<10){seconds='0'+seconds;}
if(milli<100){milli='0'+milli;}
if(milli<10){milli='0'+milli;}
return'"'+year+'-'+month+'-'+day+'T'+
hours+':'+minutes+':'+seconds+'.'+milli+'Z"';}
pairs=[];if(jQuery.isArray(o)){for(k=0;k<o.length;k++){pairs.push(jQuery.toJSON(o[k])||'null');}
return'['+pairs.join(',')+']';}
if(typeof o==='object'){for(k in o){if(hasOwn.call(o,k)){type=typeof k;if(type==='number'){name='"'+k+'"';}else if(type==='string'){name=jQuery.quoteString(k);}else{continue;}
type=typeof o[k];if(type!=='function'&&type!=='undefined'){val=jQuery.toJSON(o[k]);pairs.push(name+':'+val);}}}
return'{'+pairs.join(',')+'}';}};jQuery.evalJSON=typeof JSON==='object'&&JSON.parse?JSON.parse:function(str){return eval('('+str+')');};jQuery.secureEvalJSON=typeof JSON==='object'&&JSON.parse?JSON.parse:function(str){var filtered=str.replace(/\\["\\\/bfnrtu]/g,'@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']').replace(/(?:^|:|,)(?:\s*\[)+/g,'');if(/^[\],:{}\s]*jQuery/.test(filtered)){return eval('('+str+')');}
throw new SyntaxError('Error parsing JSON, source is not valid.');};jQuery.quoteString=function(str){if(str.match(escape)){return'"'+str.replace(escape,function(a){var c=meta[a];if(typeof c==='string'){return c;}
c=a.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+(c%16).toString(16);})+'"';}
return'"'+str+'"';};}(jQuery));


$.fn.scrollTo = function( target, options, callback ){
  if(typeof options == 'function' && arguments.length == 2){ callback = options; options = target; }
  var settings = $.extend({
    scrollTarget  : target,
    offsetTop     : 50,
    duration      : 10,
    easing        : 'swing'
  }, options);
  return this.each(function(){
    var scrollPane = $(this);
    var scrollTarget = (typeof settings.scrollTarget == "number") ? settings.scrollTarget : $(settings.scrollTarget);
    var scrollY = (typeof scrollTarget == "number") ? scrollTarget : scrollTarget.offset().top + scrollPane.scrollTop() - parseInt(settings.offsetTop);
    scrollPane.animate({scrollTop : scrollY }, parseInt(settings.duration), settings.easing, function(){
      if (typeof callback == 'function') { callback.call(this); }
    });
  });
}

/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */


var dateFormat = function () {
    var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
        timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
        timezoneClip = /[^-+\dA-Z]/g,
        pad = function (val, len) {
            val = String(val);
            len = len || 2;
            while (val.length < len) val = "0" + val;
            return val;
        };

    // Regexes and supporting functions are cached through closure
    return function (date, mask, utc) {
        var dF = dateFormat;

        // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
        if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
            mask = date;
            date = undefined;
        }

        // Passing date through Date applies Date.parse, if necessary
        date = date ? new Date(date) : new Date;
        if (isNaN(date)) throw SyntaxError("invalid date");

        mask = String(dF.masks[mask] || mask || dF.masks["default"]);

        // Allow setting the utc argument via the mask
        if (mask.slice(0, 4) == "UTC:") {
            mask = mask.slice(4);
            utc = true;
        }

        var _ = utc ? "getUTC" : "get",
            d = date[_ + "Date"](),
            D = date[_ + "Day"](),
            m = date[_ + "Month"](),
            y = date[_ + "FullYear"](),
            H = date[_ + "Hours"](),
            M = date[_ + "Minutes"](),
            s = date[_ + "Seconds"](),
            L = date[_ + "Milliseconds"](),
            o = utc ? 0 : date.getTimezoneOffset(),
            flags = {
                d:    d,
                dd:   pad(d),
                ddd:  dF.i18n.dayNames[D],
                dddd: dF.i18n.dayNames[D + 7],
                m:    m + 1,
                mm:   pad(m + 1),
                mmm:  dF.i18n.monthNames[m],
                mmmm: dF.i18n.monthNames[m + 12],
                yy:   String(y).slice(2),
                yyyy: y,
                h:    H % 12 || 12,
                hh:   pad(H % 12 || 12),
                H:    H,
                HH:   pad(H),
                M:    M,
                MM:   pad(M),
                s:    s,
                ss:   pad(s),
                l:    pad(L, 3),
                L:    pad(L > 99 ? Math.round(L / 10) : L),
                t:    H < 12 ? "a"  : "p",
                tt:   H < 12 ? "am" : "pm",
                T:    H < 12 ? "A"  : "P",
                TT:   H < 12 ? "AM" : "PM",
                Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
            };

        return mask.replace(token, function ($0) {
            return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
        });
    };
}();

// Some common format strings
dateFormat.masks = {
    "default":      "ddd mmm dd yyyy HH:MM:ss",
    shortDate:      "m/d/yy",
    mediumDate:     "mmm d, yyyy",
    longDate:       "mmmm d, yyyy",
    fullDate:       "dddd, mmmm d, yyyy",
    shortTime:      "h:MM TT",
    mediumTime:     "h:MM:ss TT",
    longTime:       "h:MM:ss TT Z",
    isoDate:        "yyyy-mm-dd",
    isoTime:        "HH:MM:ss",
    isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
    isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
    dayNames: [
        "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
        "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
    ],
    monthNames: [
        "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
        "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
    ]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
    return dateFormat(this, mask, utc);
};


﻿/*
 * http://code.google.com/p/flexible-js-formatting/
 * 
 * Copyright (C) 2004 Baron Schwartz <baron at sequent dot org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

Date.parseFunctions = {count:0};
Date.parseRegexes = [];
Date.formatFunctions = {count:0};

Date.prototype.dateFormat = function(format, ignore_offset) {
    if (Date.formatFunctions[format] == null) {
        Date.createNewFormat(format);
    }
    var func = Date.formatFunctions[format];
    if (ignore_offset || ! this.offset) {
      return this[func]();
    } else {
      return (new Date(this.valueOf() - this.offset))[func]();
    }
};

Date.createNewFormat = function(format) {
    var funcName = "format" + Date.formatFunctions.count++;
    Date.formatFunctions[format] = funcName;
    var code = "Date.prototype." + funcName + " = function(){return ";
    var special = false;
    var ch = '';
    for (var i = 0; i < format.length; ++i) {
        ch = format.charAt(i);
                // escape character start
        if (!special && ch == "\\") {
            special = true;
        }
                // escaped string
        else if (!special && ch == '"') {
                        var end = format.indexOf('"', i+1);
                        if (end==-1)
                        {
                                end = format.length;
                        }
            code += "'" + String.escape(format.substring(i+1, end)) + "' + ";
                        i = end;
        }
                // escaped character
        else if (special) {
            special = false;
            code += "'" + String.escape(ch) + "' + ";
        }
        else {
            code += Date.getFormatCode(ch);
        }
    }
    eval(code.substring(0, code.length - 3) + ";}");
};

Date.getFormatCode = function(character) {
    switch (character) {
    case "d":
        return "String.leftPad(this.getDate(), 2, '0') + ";
    case "D":
        return "Date.dayNames[this.getDay()].substring(0, 3) + ";
    case "j":
        return "this.getDate() + ";
    case "l":
        return "Date.dayNames[this.getDay()] + ";
    case "S":
        return "this.getSuffix() + ";
    case "w":
        return "this.getDay() + ";
    case "z":
        return "this.getDayOfYear() + ";
    case "W":
        return "this.getWeekOfYear() + ";
    case "F":
        return "Date.monthNames[this.getMonth()] + ";
    case "m":
        return "String.leftPad(this.getMonth() + 1, 2, '0') + ";
    case "M":
        return "Date.monthNames[this.getMonth()].substring(0, 3) + ";
    case "n":
        return "(this.getMonth() + 1) + ";
    case "t":
        return "this.getDaysInMonth() + ";
    case "L":
        return "(this.isLeapYear() ? 1 : 0) + ";
    case "Y":
        return "this.getFullYear() + ";
    case "y":
        return "('' + this.getFullYear()).substring(2, 4) + ";
    case "a":
        return "(this.getHours() < 12 ? 'am' : 'pm') + ";
    case "A":
        return "(this.getHours() < 12 ? 'AM' : 'PM') + ";
    case "g":
        return "((this.getHours() %12) ? this.getHours() % 12 : 12) + ";
    case "G":
        return "this.getHours() + ";
    case "h":
        return "String.leftPad((this.getHours() %12) ? this.getHours() % 12 : 12, 2, '0') + ";
    case "H":
        return "String.leftPad(this.getHours(), 2, '0') + ";
    case "i":
        return "String.leftPad(this.getMinutes(), 2, '0') + ";
    case "s":
        return "String.leftPad(this.getSeconds(), 2, '0') + ";
    case "X":
        return "String.leftPad(this.getMilliseconds(), 3, '0') + ";
    case "O":
        return "this.getGMTOffset() + ";
    case "T":
        return "this.getTimezone() + ";
    case "Z":
        return "(this.getTimezoneOffset() * -60) + ";
    case "q":   // quarter num, Q for name?
        return "this.getQuarter() + ";
    default:
        return "'" + String.escape(character) + "' + ";
    }
};

Date.parseDate = function(input, format) {
    if (Date.parseFunctions[format] == null) {
        Date.createParser(format);
    }
    var func = Date.parseFunctions[format];
    return Date[func](input);
};

Date.createParser = function(format) {
    var funcName = "parse" + Date.parseFunctions.count++;
    var regexNum = Date.parseRegexes.length;
    var currentGroup = 1;
    Date.parseFunctions[format] = funcName;

    var code = "Date." + funcName + " = function(input){\n"
        + "var y = -1, m = -1, d = -1, h = -1, i = -1, s = -1, ms = -1, z = 0;\n"
        + "var d = new Date();\n"
        + "y = d.getFullYear();\n"
        + "m = d.getMonth();\n"
        + "d = d.getDate();\n"
        + "var results = input.match(Date.parseRegexes[" + regexNum + "]);\n"
        + "if (results && results.length > 0) {" ;
    var regex = "";

    var special = false;
    var ch = '';
    for (var i = 0; i < format.length; ++i) {
        ch = format.charAt(i);
        if (!special && ch == "\\") {
            special = true;
        }
        else if (special) {
            special = false;
            regex += String.escape(ch);
        }
        else {
            obj = Date.formatCodeToRegex(ch, currentGroup);
            currentGroup += obj.g;
            regex += obj.s;
            if (obj.g && obj.c) {
                code += obj.c;
            }
        }
    }

    code += "if (y > 0 && m >= 0 && d > 0 && h >= 0 && i >= 0 && s >= 0 && ms >= 0)\n"
        + "{return new Date(y, m, d, h, i, s, ms).applyOffset(z);}\n"
        + "if (y > 0 && m >= 0 && d > 0 && h >= 0 && i >= 0 && s >= 0)\n"
        + "{return new Date(y, m, d, h, i, s).applyOffset(z);}\n"
        + "else if (y > 0 && m >= 0 && d > 0 && h >= 0 && i >= 0)\n"
        + "{return new Date(y, m, d, h, i).applyOffset(z);}\n"
        + "else if (y > 0 && m >= 0 && d > 0 && h >= 0)\n"
        + "{return new Date(y, m, d, h).applyOffset(z);}\n"
        + "else if (y > 0 && m >= 0 && d > 0)\n"
        + "{return new Date(y, m, d).applyOffset(z);}\n"
        + "else if (y > 0 && m >= 0)\n"
        + "{return new Date(y, m).applyOffset(z);}\n"
        + "else if (y > 0)\n"
        + "{return new Date(y).applyOffset(z);}\n"
        + "}return null;}";

    Date.parseRegexes[regexNum] = new RegExp("^" + regex + "$");
    eval(code);
};

Date.formatCodeToRegex = function(character, currentGroup) {
    switch (character) {
    case "D":
        return {g:0,
        c:null,
        s:"(?:Sun|Mon|Tue|Wed|Thu|Fri|Sat)"};
    case "j":
    case "d":
        return {g:1,
            c:"d = parseInt(results[" + currentGroup + "], 10);\n",
            s:"(\\d{1,2})"};
    case "l":
        return {g:0,
            c:null,
            s:"(?:" + Date.dayNames.join("|") + ")"};
    case "S":
        return {g:0,
            c:null,
            s:"(?:st|nd|rd|th)"};
    case "w":
        return {g:0,
            c:null,
            s:"\\d"};
    case "z":
        return {g:0,
            c:null,
            s:"(?:\\d{1,3})"};
    case "W":
        return {g:0,
            c:null,
            s:"(?:\\d{2})"};
    case "F":
        return {g:1,
            c:"m = parseInt(Date.monthNumbers[results[" + currentGroup + "].substring(0, 3)], 10);\n",
            s:"(" + Date.monthNames.join("|") + ")"};
    case "M":
        return {g:1,
            c:"m = parseInt(Date.monthNumbers[results[" + currentGroup + "]], 10);\n",
            s:"(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)"};
    case "n":
    case "m":
        return {g:1,
            c:"m = parseInt(results[" + currentGroup + "], 10) - 1;\n",
            s:"(\\d{1,2})"};
    case "t":
        return {g:0,
            c:null,
            s:"\\d{1,2}"};
    case "L":
        return {g:0,
            c:null,
            s:"(?:1|0)"};
    case "Y":
        return {g:1,
            c:"y = parseInt(results[" + currentGroup + "], 10);\n",
            s:"(\\d{4})"};
    case "y":
        return {g:1,
            c:"var ty = parseInt(results[" + currentGroup + "], 10);\n"
                + "y = ty > Date.y2kYear ? 1900 + ty : 2000 + ty;\n",
            s:"(\\d{1,2})"};
    case "a":
        return {g:1,
            c:"if (results[" + currentGroup + "] == 'am') {\n"
                + "if (h == 12) { h = 0; }\n"
                + "} else { if (h < 12) { h += 12; }}",
            s:"(am|pm)"};
    case "A":
        return {g:1,
            c:"if (results[" + currentGroup + "] == 'AM') {\n"
                + "if (h == 12) { h = 0; }\n"
                + "} else { if (h < 12) { h += 12; }}",
            s:"(AM|PM)"};
    case "g":
    case "G":
    case "h":
    case "H":
        return {g:1,
            c:"h = parseInt(results[" + currentGroup + "], 10);\n",
            s:"(\\d{1,2})"};
    case "i":
        return {g:1,
            c:"i = parseInt(results[" + currentGroup + "], 10);\n",
            s:"(\\d{2})"};
    case "s":
        return {g:1,
            c:"s = parseInt(results[" + currentGroup + "], 10);\n",
            s:"(\\d{2})"};
    case "X":
      return {g:1,
          c:"ms = parseInt(results[" + currentGroup + "], 10);\n",
          s:"(\\d{3})"};
    case "O":
    case "P":
        return {g:1,
            c:"z = Date.parseOffset(results[" + currentGroup + "], 10);\n",
            s:"(Z|[+-]\\d{2}:?\\d{2})"}; // "Z", "+05:00", "+0500" all acceptable.
    case "T":
        return {g:0,
            c:null,
            s:"[A-Z]{3}"};
    case "Z":
        return {g:1,
            c:"s = parseInt(results[" + currentGroup + "], 10);\n",
            s:"([+-]\\d{1,5})"};
    default:
        return {g:0,
            c:null,
            s:String.escape(character)};
    }
};

Date.parseOffset = function(str) {
  if (str == "Z") { return 0 ; } // UTC, no offset.
  var seconds ;
  seconds = parseInt(str[0] + str[1] + str[2]) * 3600 ; // e.g., "+05" or "-08"
  if (str[3] == ":") {            // "+HH:MM" is preferred iso8601 format ("O")
    seconds += parseInt(str[4] + str[5]) * 60;
  } else {                      // "+HHMM" is frequently used, though. ("P")
    seconds += parseInt(str[3] + str[4]) * 60;
  }
  return seconds ;
};

Date.today = function() {
    var now = new Date();
    now.setHours(0);
    now.setMinutes(0);
    now.setSeconds(0);
    
    return now;
}

// convert the parsed date into UTC, but store the offset so we can optionally use it in dateFormat()
Date.prototype.applyOffset = function(offset_seconds) {
  this.offset = offset_seconds * 1000 ;
  this.setTime(this.valueOf() + this.offset);
  return this ;
};

Date.prototype.getTimezone = function() {
    return this.toString().replace(
        /^.*? ([A-Z]{3}) [0-9]{4}.*$/, "$1").replace(
        /^.*?\(([A-Z])[a-z]+ ([A-Z])[a-z]+ ([A-Z])[a-z]+\)$/, "$1$2$3").replace(
        /^.*?[0-9]{4} \(([A-Z]{3})\)/, "$1");
};

Date.prototype.getGMTOffset = function() {
    return (this.getTimezoneOffset() > 0 ? "-" : "+")
        + String.leftPad(Math.floor(this.getTimezoneOffset() / 60), 2, "0")
        + String.leftPad(this.getTimezoneOffset() % 60, 2, "0");
};

Date.prototype.getDayOfYear = function() {
    var num = 0;
    Date.daysInMonth[1] = this.isLeapYear() ? 29 : 28;
    for (var i = 0; i < this.getMonth(); ++i) {
        num += Date.daysInMonth[i];
    }
    return num + this.getDate() - 1;
};

Date.prototype.getWeekOfYear = function() {
    // Skip to Thursday of this week
    var now = this.getDayOfYear() + (4 - this.getDay());
    // Find the first Thursday of the year
    var jan1 = new Date(this.getFullYear(), 0, 1);
    var then = (7 - jan1.getDay() + 4);
    document.write(then);
    return String.leftPad(((now - then) / 7) + 1, 2, "0");
};

Date.prototype.isLeapYear = function() {
    var year = this.getFullYear();
    return ((year & 3) == 0 && (year % 100 || (year % 400 == 0 && year)));
};

Date.prototype.getFirstDayOfMonth = function() {
    var day = (this.getDay() - (this.getDate() - 1)) % 7;
    return (day < 0) ? (day + 7) : day;
};

Date.prototype.getLastDayOfMonth = function() {
    var day = (this.getDay() + (Date.daysInMonth[this.getMonth()] - this.getDate())) % 7;
    return (day < 0) ? (day + 7) : day;
};

Date.prototype.getDaysInMonth = function() {
    Date.daysInMonth[1] = this.isLeapYear() ? 29 : 28;
    return Date.daysInMonth[this.getMonth()];
};
Date.prototype.getQuarter = function() {
    return Date.quarterFromMonthNum[this.getMonth()];
};

Date.prototype.getSuffix = function() {
    switch (this.getDate()) {
        case 1:
        case 21:
        case 31:
            return "st";
        case 2:
        case 22:
            return "nd";
        case 3:
        case 23:
            return "rd";
        default:
            return "th";
    }
};

String.escape = function(string) {
    return string.replace(/('|\\)/g, "\\$1");
};

String.leftPad = function (val, size, ch) {
    var result = new String(val);
    if (ch == null) {
        ch = " ";
    }
    while (result.length < size) {
        result = ch + result;
    }
    return result;
};

Date.quarterFromMonthNum = [1,1,1,2,2,2,3,3,3,4,4,4];
Date.daysInMonth = [31,28,31,30,31,30,31,31,30,31,30,31];
Date.monthNames =
   ["January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December"];
Date.dayNames =
   ["Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"];
Date.y2kYear = 50;
Date.monthNumbers = {
    Jan:0,
    Feb:1,
    Mar:2,
    Apr:3,
    May:4,
    Jun:5,
    Jul:6,
    Aug:7,
    Sep:8,
    Oct:9,
    Nov:10,
    Dec:11};
Date.patterns = {
    ISO8601LongPattern: "Y\\-m\\-d\\TH\\:i\\:sO",
    ISO8601ShortPattern: "Y\\-m\\-d",
    ShortDatePattern: "n/j/Y",
    LongDatePattern: "l, F d, Y",
    FullDateTimePattern: "l, F d, Y g:i:s A",
    MonthDayPattern: "F d",
    ShortTimePattern: "g:i A",
    LongTimePattern: "g:i:s A",
    SortableDateTimePattern: "Y-m-d\\TH:i:s",
    UniversalSortableDateTimePattern: "Y-m-d H:i:sO",
    YearMonthPattern: "F, Y"};


/**
 *
 * @author: Malishev Dmitry <dima.malishev@gmail.com>
 */
var _DEBUG = true;
var _DEBUG_LEVEL = 'ALL';
// possible levels: ALL, IMPORTANT
var Error = {FATAL: 1, WARNING: 0, NORMAL: -1};


/**
 * Init debug, grabs console object if accessible, or makes dummy debugger
 */
var fb = _DEBUG && 'undefined' != typeof(console) ? console : {
    log         : function(){},
    debug       : function(){},
    info        : function(){},
    warn        : function(){},
    error       : function(){},
    assert      : function(){},
    dir         : function(){},
    dirxml      : function(){},
    trace       : function(){},
    group       : function(){},
    groupEnd    : function(){},
    time        : function(){},
    timeEnd     : function(){},
    profile     : function(){},
    profileEnd  : function(){},
    count       : function(){},
    msg         : function(){}
};

var checked = false;
var frmname = '';
var lastScrollTop = 0;


//
var App = {
    // Main namespases for page specific functions
    // Core namespaces
    Ajax: { 
        Busy: {} 
    },
    Core: {},
    // CONSTANT VALUES
    Constants: {
        UNLIM_VALUE: 'unlimited', // overritten in i18n.js.php
        UNLIM_TRANSLATED_VALUE: 'unlimited' // overritten in i18n.js.php
    }, 
    // Actions. More widly used funcs
    Actions: {
        DB:      {},
        WEB:     {},
        PACKAGE: {},
        MAIL_ACC:{}
    },
    // Utilities
    Helpers: {},
    HTML: {
        Build: {}
    },
    Filters: {},
    Env: {
        lang: GLOBAL.lang,
    },
    i18n: {},
    Listeners: {
        DB:      {},
        WEB:     {},
        PACKAGE: {},
        MAIL_ACC:{}
    },
    View:{
        HTML: {
            Build: {}
        },
        // pages related views
    },
    Cache: {
        clear: function() {} // TODO: stub method, will be used later
    },
    Ref: {},
    Tmp: {},
    Thread: {
        run: function(delay, ref) {
            setTimeout(function() {
                ref();
            }, delay*10);
        }
    },
    Settings: { 
        GLOBAL:  {}, 
        General: {}
    },
    Templates: {
        Templator: null,
        Tpl:       {},
        _indexes:  {}
    }
};

// Internals
Array.prototype.set = function(key, value){
    var index = this[0][key];
    this[1][index] = value;
}
Array.prototype.get = function(key){
    var index = this[0][key];
    return this[1][index];
}
Array.prototype.finalize = function(){
    this.shift();
    this[0] = this[0].join('');
    return this[0];
}
Array.prototype.done = function(){
    return this.join('');
}

String.prototype.wrapperize = function(key, ns){
    var tpl = App.Templates.get(key, ns);
    tpl.set(':content', this);

    return tpl.finalize();
}



App.Ajax.request = function(method, data, callback, onError){
    // this will prevent multiple ajaxes on user clicks
    /*if (App.Helpers.isAjaxBusy(method, data)) {
        fb.warn('ajax request ['+method+'] is busy');
        return;
    }*/
    //App.Helpers.setAjaxBusy(method, data);
    data = data || {};

    var prgs = $('.progress-container');

    switch (method) {
        case 'cd':
            prgs.find('title').text('Opening dir');
            prgs.show();
            break;
        case 'delete_files':
            prgs.find('title').text('Deleting');
            prgs.show();
            break;
        case 'unpack_item':
            prgs.find('title').text('Unpacking');
            prgs.show();
            break;
        case 'create_file':
            prgs.find('title').text('Creating file');
            prgs.show();
            break;
        case 'create_dir':
            prgs.find('title').text('Creating directory');
            prgs.show();
            break;
        case 'rename_file':
            prgs.find('title').text('Renaming file');
            prgs.show();
            break;
        case 'copy_file':
        case 'copy_directory':
            prgs.find('title').text('Copying files');
            prgs.show();
            break;
        default:
            break;
    }

    jQuery.ajax({
        url: GLOBAL.ajax_url,
        global: false,
        type: data.request_method || "GET",
        data: jQuery.extend(data, {'action': method}),
        dataType: "text boost",
        converters: {
            "text boost": function(value) {
                value = value.trim();
                return $.parseJSON(value);
        }},
        async: true,
        cache: false,
        error: function(jqXHR, textStatus, errorThrown)
        {
            prgs.hide();
            onError && onError();
            if ('undefined' != typeof onError) {
                fb.error(textStatus);
            }
        },
        complete: function()
        {
            //App.Helpers.setAjaxFree(method, data);
            prgs.hide();
        },
        success: function(reply)
        {
            prgs.hide();
            //App.Helpers.setAjaxFree(method, data);
            try {
                callback && callback(reply);
            }
            catch(e) {
                fb.error('GENERAL ERROR with ajax method: ' + data.request_method + ' ' + e);
                //App.Helpers.generalError();
            }
        }
    });
}

jQuery.extend({
    keys:    function(obj){
        if (!obj) {
            return [];
        }
        var a = [];
        jQuery.each(obj, function(k){ a.push(k) });
        return a;
    }
})


App.Core.create_hidden_form = function(action){
    var form = jQuery('<form>', {
            id     : 'hidden-form',
            method : 'post',
            action : action
        });
    jQuery('body').append(form);

    return form;
};

App.Core.extend_from_json = function(elm, data, prefix){
    elm      = jQuery(elm);
    var data = App.Core.flatten_json(data, prefix);
    var keys = jQuery.keys(data);
    for(var i=0, cnt=keys.length; i<cnt; i++)
    {
        elm.append(jQuery('<input>', {
            name : keys[i],
            value: data[keys[i]],
            type : 'hidden'
        }));
    }

    return elm;
}

App.Core.flatten_json = function(data, prefix){
    var keys   = jQuery.keys(data);
    var result = {};

    prefix || (prefix = '');

    if(keys.length)
    {
        for(var i=0, cnt=keys.length; i<cnt; i++)
        {
            var value = data[keys[i]];
            switch(typeof(value))
            {
                case 'function': break;
                case 'object'  : result = jQuery.extend(result, App.Core.flatten_json(value, prefix + '[' + keys[i] + ']')); break;
                default        : result[prefix + '[' + keys[i] + ']'] = value;
            }
        }
        return result;
    }
    else
    {
        return false;
    }
}

//
// Cookies adapter
// Allow to work old pages realisations of cookie requests
//
function createCookie(name, value, expire_days) {
    jQuery.cookie(name, value, { expires: expire_days});
}

function readCookie(name) {
    jQuery.cookie(name);
}

function eraseCookie(name) {
    jQuery.removeCookie(name);
}


/**
 * Timer for profiling
 */
var timer = {};
timer.start = function()
{
    timer.start_time = new Date();
}

timer.stop = function( msg )
{
    timer.stop_time = new Date();
    timer.print( msg );
}

timer.print = function( msg )
{
    var passed = timer.stop_time - timer.start_time;
    fb.info( msg || '' + passed / 1000 );
}


String.prototype.trim = function()
{
    var str = this;
    str = str.replace(/^\s+/, '');
    for (var i = str.length - 1; i >= 0; i--) {
        if (/\S/.test(str.charAt(i))) {
            str = str.substring(0, i + 1);
        break;
        }
    }
    return str;
}

hover_menu = function() {
    var sep_1 = $('div.l-content > div.l-separator:nth-of-type(2)');
    var sep_2 = $('div.l-content > div.l-separator:nth-of-type(4)');
    var nav_main = $('.l-stat');
    var nav_a = $('.l-stat .l-stat__col a');
    var nav_context = $('.l-sort');

    var st = $(window).scrollTop();

    if (st <= 112) {
        sep_1.css({'margin-top': 214 - st + 'px'});
        sep_2.css({'margin-top': 259 - st + 'px'});
        nav_a.css({'height': 111 - st + 'px'});
        nav_a.css({'min-height': 111 - st + 'px'});
        nav_context.css({'margin-top': 215 - st + 'px'});
        sep_2.css({'box-shadow':'none'});
        sep_2.css({'background-color': '#ddd'});
        sep_2.css({'height': '1px'});
    }

    if(st > 112){
        sep_1.css({'margin-top': '100px'});
        sep_2.css({'margin-top': '130px'});
        sep_2.css({'height': '15px'});
        sep_2.css({'background-color': '#fff'});
        nav_a.css({'height': '0'});
        nav_a.css({'min-height': '0'});
        nav_context.css({'margin-top': '101px'});
        nav_a.find('ul').css({'visibility': 'hidden'});
        nav_main.css({'padding-top': '27px'});
        sep_2.css({'box-shadow':'0 5px 3px 0 rgba(200, 200, 200, 0.5)'});
    }

    if(st == 0){
        nav_a.css({'min-height': '111px'});
        nav_a.css({'height': '111px'});
    }

    if(st < 109 ){
        nav_a.find('ul').css({'visibility': 'visible'});
        nav_main.css({'padding-top': 30 + 'px'});
    }

    if (st <= 112 && st > 110 ) {
        nav_main.css({'padding-top': 30 - st + 109  + 'px'});
    }

    lastScrollTop = st;
}


function checkedAll(frmname) {
    if ($('.l-unit.selected:not(.header)').length > 0 || !$('.l-unit').length ) {
        $('.l-unit:not(.header)').removeClass("selected");
        $('.ch-toggle').prop("checked", false);
        $('.toggle-all').removeClass('clicked-on');
    }
    else {
        $('.l-unit:not(.header)').addClass("selected");
        $('.ch-toggle').prop("checked", true);
        $('.toggle-all').addClass('clicked-on');
    }
}

function doSearch(url) {
    var url = url || '/search/';
    var loc = url + '?q=' + $('.search-input').val();

    location.href = loc;
    return false;
}


function elementHideShow(elementToHideOrShow){
    var el = document.getElementById(elementToHideOrShow);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

