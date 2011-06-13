/**
 * jQBrowser v0.2 - Extend jQuery's browser detection capabilities
 *   * http://davecardwell.co.uk/javascript/jquery/plugins/jquery-browserdetect/0.2/
 *
 * Dave Cardwell <http://davecardwell.co.uk/>
 *
 * Built on the shoulders of giants:
 *   * John Resig <http://jquery.com/>
 *   * Peter-Paul Koch <http://www.quirksmode.org/?/js/detect.html>
 *
 *
 * Copyright (c) 2006 Dave Cardwell, dual licensed under the MIT and GPL
 * licenses:
 *   * http://www.opensource.org/licenses/mit-license.php
 *   * http://www.gnu.org/licenses/gpl.txt
 */


/**
 * For the latest version of this plugin, and a discussion of its usage and
 * implementation, visit:
 *   * http://davecardwell.co.uk/javascript/jquery/plugins/jquery-browserdetect/
 */

new function() {
    /**
     * The following functions and attributes form the Public interface of the
     * jQBrowser plugin, accessed externally through the $.browser object.
     * See the relevant function definition later in the source for further
     * information.
     *
     * $.browser.browser()
     * $.browser.version.number()
     * $.browser.version.string()
     * $.browser.OS()
     *
     * $.browser.aol()
     * $.browser.camino()
     * $.browser.firefox()
     * $.browser.flock()
     * $.browser.icab()
     * $.browser.konqueror()
     * $.browser.mozilla()
     * $.browser.msie()
     * $.browser.netscape()
     * $.browser.opera()
     * $.browser.safari()
     *
     * $.browser.linux()
     * $.browser.mac()
     * $.browser.win()
     */
    var Public = {
        // The current browser, its version as a number or a string, and the
        // operating system its running on.
        'browser': function() {
            return Private.browser;
        },
        'version': {
            'number': function() {
                return Math.floor(Private.version.number);
            },
            'string': function() {
                return Private.version.string;
            }
        },
        'OS': function() {
            return Private.OS;
        },

        // A boolean value indicating whether or not the given browser was
        // detected.
        'chrome': function() {
            return Private.chrome;
        },
        'aol': function() {
            return Private.aol;
        },
        'camino': function() {
            return Private.camino;
        },
        'firefox': function() {
            return Private.firefox;
        },
        'flock': function() {
            return Private.flock;
        },
        'icab': function() {
            return Private.icab;
        },
        'konqueror': function() {
            return Private.konqueror;
        },
        'mozilla': function() {
            return Private.mozilla;
        },
        'msie': function() {
            return Private.msie;
        },
        'netscape': function() {
            return Private.netscape;
        },
        'opera': function() {
            return Private.opera;
        },
        'safari': function() {
            return Private.safari;
        },

        // A boolean value indicating whether or not the given OS was
        // detected.
        'linux': function() {
            return Private.linux;
        },
        'mac': function() {
            return Private.mac;
        },
        'win': function() {
            return Private.win;
        }
    };

    // Allow external access to the 'Public' interface through the $.browser
    // object.
    $.browser = Public;



    /**
     * The following functions and attributes form the internal methods and
     * state of the jQBrowser plugin.  See the relevant function definition
     * later in the source for further information.
     *
     * Private.browser
     * Private.version
     * Private.OS
     *
     * Private.aol
     * Private.camino
     * Private.firefox
     * Private.flock
     * Private.icab
     * Private.konqueror
     * Private.mozilla
     * Private.msie
     * Private.netscape
     * Private.opera
     * Private.safari
     *
     * Private.linux
     * Private.mac
     * Private.win
     */
    var Private = {
        // Initially set to 'Unknown', if detected each of these properties will
        // be updated.
        'browser': 'Unknown',
        'version': {
            'number': undefined,
            'string': 'Unknown'
        },
        'OS': 'Unknown',

        // Initially set to false, if detected one of the following browsers
        // will be updated.
        'chrome': false,
        'aol': false,
        'camino': false,
        'firefox': false,
        'flock': false,
        'icab': false,
        'konqueror': false,
        'mozilla': false,
        'msie': false,
        'netscape': false,
        'opera': false,
        'safari': false,

        // Initially set to false, if detected one of the following operating
        // systems will be updated.
        'linux': false,
        'mac': false,
        'win': false
    };



    /**
     * Loop over the items in 'data' trying to find a browser match with the
     * test in data[i].browser().  Once found, attempt to determine the
     * browser version.
     *
     *       'name': A string containing the full name of the browser.
     * 'identifier': By default this is a lowercase version of 'name', but
     *               this can be overwritten by explicitly defining an
     *               'identifier'.
     *    'browser': A function that returns a boolean value indicating
     *               whether or not the given browser is detected.
     *    'version': An optional function that overwrites the default version
     *               testing.  Must return the result of a .match().
     *
     * Please note that the order of the data array is important, as some
     * browsers contain details of others in their navigator.userAgent string.
     * For example, Flock's contains 'Firefox' so much come before Firefox's
     * test to avoid false positives.
     */
    for( var  i = 0,                    // counter
        ua = navigator.userAgent,  // the navigator's user agent string
        ve = navigator.vendor,     // the navigator's vendor string
        data = [                     // browser tests and data
        { // Safari <http://www.apple.com/safari/>
            'name': 'Chrome',
            'browser': function() {
                return /Google/.test(ve)
            }
        },
        { // Safari <http://www.apple.com/safari/>
            'name': 'Safari',
            'browser': function() {
                return /Apple/.test(ve)
            }
        },
        { // Opera <http://www.opera.com/>
            'name': 'Opera',
            'browser': function() {
                return window.opera != undefined
            }
        },
        { // iCab <http://www.icab.de/>
            'name': 'iCab',
            'browser': function() {
                return /iCab/.test(ve)
            }
        },
        { // Konqueror <http://www.konqueror.org/>
            'name': 'Konqueror',
            'browser': function() {
                return /KDE/.test(ve)
            }
        },
        { // AOL Explorer <http://downloads.channel.aol.com/browser>
            'identifier': 'aol',
            'name': 'AOL Explorer',
            'browser': function() {
                return /America Online Browser/.test(ua)
            },
            'version': function() {
                return ua.match(/rev(\d+(?:\.\d+)+)/)
            }
        },
        { // Flock <http://www.flock.com/>
            'name': 'Flock',
            'browser': function() {
                return /Flock/.test(ua)
            }
        },
        { // Camino <http://www.caminobrowser.org/>
            'name': 'Camino',
            'browser': function() {
                return /Camino/.test(ve)
            }
        },
        { // Firefox <http://www.mozilla.com/firefox/>
            'name': 'Firefox',
            'browser': function() {
                return /Firefox/.test(ua)
            }
        },
        { // Netscape <http://browser.netscape.com/>
            'name': 'Netscape',
            'browser': function() {
                return /Netscape/.test(ua)
            }
        },
        { // Internet Explorer <http://www.microsoft.com/windows/ie/>
            //                   <http://www.microsoft.com/mac/ie/>
            'identifier': 'msie',
            'name': 'Internet Explorer',
            'browser': function() {
                return /MSIE/.test(ua)
            },
            'version': function() {
                return ua.match(
                    /MSIE (\d+(?:\.\d+)+(?:b\d*)?)/
                    )
            }
        },
        { // Mozilla <http://www.mozilla.org/products/mozilla1.x/>
            'name': 'Mozilla',
            'browser': function() {
                return /Gecko|Mozilla/.test(ua)
            },
            'version': function() {
                return ua.match(/rv:(\d+(?:\.\d+)+)/);
            }
        }
        ];
        i < data.length;
        i++
        ) {
        if( data[i].browser() ) { // we have a match
            // If the identifier is not explicitly set, use a lowercase
            // version of the given name.
            var identifier = data[i].identifier ? data[i].identifier
            : data[i].name.toLowerCase();

            // Make a note that this browser was detected.
            Private[ identifier ] = true;

            // $.browser.browser() will now return the correct browser.
            Private.browser = data[i].name;

            var result;
            if( data[i].version != undefined && (result = data[i].version()) ) {
                // Use the explicitly set test for browser version.
                Private.version.string = result[1];
                Private.version.number = parseFloat( result[1] );
            } else {
                // Otherwise use the default test which searches for the
                // version number after the browser name in the user agent
                // string.
                var re = new RegExp(
                    data[i].name + '(?:\\s|\\/)(\\d+(?:\\.\\d+)+(?:(?:a|b)\\d*)?)'
                    );

                result = ua.match(re);
                if( result != undefined ) {
                    Private.version.string = result[1];
                    Private.version.number = parseFloat( result[1] );
                }
            }

            // Once we've detected the browser there is no need to check the
            // others.
            break;
        }
    };



    /**
     * Loop over the items in 'data' trying to find a operating system match
     * with the test in data[i].os().
     *
     *       'name': A string containing the full name of the operating
     *               system.
     * 'identifier': By default this is a lowercase version of 'name', but
     *               this can be overwritten by explicitly defining an
     *               'identifier'.
     *         'OS': A function that returns a boolean value indicating
     *               whether or not the given operating system is detected.
     */
    for( var  i = 0,                  // counter
        pl = navigator.platform, // the navigator's platform string
        data = [                   // OS data and tests
        { // Microsoft Windows <http://www.microsoft.com/windows/>
            'identifier': 'win',
            'name': 'Windows',
            'OS': function() {
                return /Win/.test(pl)
            }
        },
        { // Apple Mac OS <http://www.apple.com/macos/>
            'name': 'Mac',
            'OS': function() {
                return /Mac/.test(pl)
            }
        },
        { // Linux <http://www.linux.org/>
            'name': 'Linux',
            'OS': function() {
                return /Linux/.test(pl)
            }
        }
        ];
        i < data.length;
        i++
        ) {
        if( data[i].OS() ) { // we have a match
            // If the identifier is not explicitly set, use a lowercase
            // version of the given name.
            var identifier = data[i].identifier ? data[i].identifier
            : data[i].name.toLowerCase();

            // Make a note that the OS was detected.
            Private[ identifier ] = true;

            // $.browser.OS() will now return the correct OS.
            Private.OS = data[i].name;

            // Once we've detected the browser there is no need to check the
            // others.
            break;
        }
    };
}();
