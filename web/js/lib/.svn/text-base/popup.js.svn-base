/*
 * $Revision$
 * $Date$
 *
 * Copyright (C) 1999-2009 Jive Software. All rights reserved.
 *
 * This software is the proprietary information of Jive Software. Use is subject to license terms.
 */

/*
* $ lightbox_me
* By: Buck Wilson
* Version : 2.0
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*     http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/


(function($) {

    $.fn.lightbox_me = function(options) {



        return this.each(function() {

            var
                opts = $.extend({}, $.fn.lightbox_me.defaults, options),
                $overlay = $('<div class="' + opts.classPrefix + '_overlay"/>'),
                $self = $(this),

                $iframe = $('<iframe src="' +  /^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank' + '" style="z-index: ' + (opts.zIndex + 1) + '; display: none; border: none; margin: 0; padding: 0; position: absolute; width: 100%; height: 100%; top: 0; left: 0;"/>'),
                ie6 = ($.browser.msie && $.browser.version < 7);


            /*----------------------------------------------------
               DOM Building
            ---------------------------------------------------- */
            if (ie6) { $('body').append($iframe) } // iframe shim for ie6, to hide select elements
            $('body').append($self).append($overlay);


            /*----------------------------------------------------
               CSS stuffs
            ---------------------------------------------------- */

            // set css of the modal'd window

            setSelfPosition();
            $self.css({left: '50%', marginLeft: ($self.outerWidth() / 2) * -1,  zIndex: (opts.zIndex + 3) });


            // set css of the overlay

            setOverlayHeight(); // pulled this into a function because it is called on window resize.
            $overlay
                    .css({ position: 'absolute', width: '100%', top: 0, left: 0, right: 0, bottom: 0, zIndex: (opts.zIndex + 2), display: 'none' })
                    .css(opts.overlayCSS);


            /*----------------------------------------------------
               Animate it in.
            ---------------------------------------------------- */

            $overlay.fadeIn(opts.overlaySpeed, function() {
                $self[opts.appearEffect](opts.lightboxSpeed, function() { setOverlayHeight(); opts.onLoad()});
            });



            /*----------------------------------------------------
               Bind Events
            ---------------------------------------------------- */

            $(window).resize(setOverlayHeight)
                     .resize(setSelfPosition)
                     .scroll(setSelfPosition)
                     .keypress(observeEscapePress);
            $self.find(opts.closeSelector).add($overlay).click(function() { closeLightbox(); return false; });
            $self.bind('close', closeLightbox);
            $self.bind('resize', setSelfPosition);

            

            /*--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
              -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */


            /*----------------------------------------------------
               Private Functions
            ---------------------------------------------------- */

            /* Remove or hide all elements */
            function closeLightbox() {
                if (opts.destroyOnClose) {
                    $self.add($overlay).remove();
                } else {
                    $self.add($overlay).hide();
                }

                $iframe.remove();

                $(window).unbind('resize', setOverlayHeight);
                $(window).unbind('resize', setSelfPosition);

                opts.onClose();
            }


            /* Function to bind to the window to observe the escape key press */
            function observeEscapePress(e) {
                if(e.keyCode == 27 || (e.DOM_VK_ESCAPE == 27 && e.which==0)) closeLightbox();
            }


            /* Set the height of the overlay
                    : if the document height is taller than the window, then set the overlay height to the document height.
                    : otherwise, just set overlay height: 100%
            */
            function setOverlayHeight() {
                if ($(window).height() < $(document).height()) {
                    $overlay.css({height: $(document).height() + 'px'});
                } else {
                    $overlay.css({height: '100%'});
                    if (ie6) {$('html,body').css('height','100%'); } // ie6 hack for height: 100%; TODO: handle this in IE7
                }
            }


            /* Set the position of the modal'd window ($self)
                    : if $self is taller than the window, then make it absolutely positioned
                    : otherwise fixed
            */
            function setSelfPosition() {
                var s = $self[0].style;

                if (($self.height() + 80  >= $(window).height()) && ($self.css('position') != 'absolute' || ie6)) {
                    var topOffset = $(document).scrollTop() + 40;
                    $self.css({position: 'absolute', top: topOffset + 'px', marginTop: 0})
                    if (ie6) {
                        s.removeExpression('top');
                    }
                } else if ($self.height()+ 80  < $(window).height()) {
                    if (ie6) {
                        s.position = 'absolute';
                        if (opts.centered) {
                            s.setExpression('top', '(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"')
                            s.marginTop = 0;
                        } else {
                            var top = (opts.modalCSS && opts.modalCSS.top) ? parseInt(opts.modalCSS.top) : 0;
                            s.setExpression('top', '((blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + '+top+') + "px"')
                        }
                    } else {
                        if (opts.centered) {
                            $self.css({ position: 'fixed', top: '50%', marginTop: ($self.outerHeight() / 2) * -1})
                        } else {
                            $self.css({ position: 'fixed'}).css(opts.modalCSS);
                        }

                    }
                }
            }

        });



    };


    $.fn.lightbox_me.defaults = {

        // animation
        appearEffect: "fadeIn",
        overlaySpeed: 300,
        lightboxSpeed: "fast",

        // close
        closeSelector: ".close",
        closeClick: true,
        closeEsc: true,

        // behavior
        destroyOnClose: false,

        // callbacks
        onLoad: function() {},
        onClose: function() {},

        // style
        classPrefix: 'lb',
        zIndex: 999,
        centered: false,
        modalCSS: {top: '40px'},
        overlayCSS: {background: 'black', opacity: .6}
    }


})(jQuery);