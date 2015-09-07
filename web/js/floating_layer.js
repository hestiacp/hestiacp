(function(jQuery)
{
    jQuery.fn.flayer_close = function()
    {
        try {
            jQuery(this).flayer.close();
        }
        catch (e) {
            fb.error(e);
        }
    }
    jQuery.fn.flayer = function(params)
    {  
        var elm = this;
        var ref = {};
        var customConfig = params;
        var config = {
            bgcolor : '#333',
            opacity : 0.6,
            id : 'floating-box',
            className : 'floating-box-class',
            zIndex : 5000,
            beforeStart : function() {},
            beforeEnd : function() {},
            afterStart: function() {},
            close : null,
            closeClass : 'close-floating-layer',
            outerClose : false,
            returnParent: jQuery(elm).parent()
        };

        jQuery.fn.flayer.close = function()
        {
            flayer_destroy();
        }

        function init()
        {
            jQuery.extend(config, customConfig);
        }
        
        function start_ovservers()
        {
            jQuery(window).bind('scroll.fl', function()
            {
                setTimeout(function()
                {
                    reposition();
                }, 5);
            });

            jQuery(window).bind('resize.fl', function()
            {
                setTimeout(function()
                {
                    reposition();
                }, 5);
            });

            jQuery(ref.container).bind('click.fl', function(evt)
            {
                jQuery(evt.target).hasClass(config.closeClass) ? flayer_destroy() : -1;
                if(!!config.outerClose)
                    jQuery(evt.target).hasClass('fl-cloud') ? flayer_destroy() : -1;
            });

            // todo:
            !!config.outerClose ? jQuery(window).bind('keypress.fl', function(evt){evt.keyCode == 27 ? flayer_destroy() : -1;}) : -1;

        }

        function position()
        {    
            var viewport = {};
            viewport.left = jQuery(window).scrollLeft();
            viewport.top = jQuery(window).scrollTop();
            viewport.height = window.innerHeight ? window.innerHeight : jQuery(window).height();//jQuery(document.body).height();
            viewport.width =  jQuery(window).width();//jQuery(document.body).width();
            
            var had = jQuery('<div>').attr('id', 'truth-is-out-there').css({'position' : 'absolute', 'left': '-50000px'});
            var dolly = jQuery(elm).clone(true).addClass('dolly');
            jQuery(had).append(jQuery(dolly).removeClass('hidden'));
            jQuery(document.body).prepend(had);
            var dims = {'width' : jQuery(dolly).width(), 'height': jQuery(dolly).height()}; 
            jQuery(had).remove();

            //dims.height = 350;
            //dims.width = 350;

            jQuery(ref.overlay).height(jQuery(document).height());
            jQuery(ref.overlay).width(jQuery(document).width());
            
            jQuery(ref.content).height(dims.height);
            jQuery(ref.content).width(dims.width);
                       
            jQuery(ref.close).css({'top' : viewport.top, 'left' : viewport.left });

            jQuery(ref.content).css({'left' : Math.round( (viewport.width - dims.width) / 2 ) + viewport.left, 'top': Math.round( (viewport.height - dims.height) / 2 ) + viewport.top});
        }
    
        function reposition()
        {        
            var viewport = {};
            viewport.left = jQuery(window).scrollLeft();
            viewport.top = jQuery(window).scrollTop();
            viewport.height = window.innerHeight ? window.innerHeight : jQuery(window).height();
            viewport.width =  jQuery(window).width();


            jQuery(ref.overlay).height(jQuery(document).height());
            jQuery(ref.overlay).width(jQuery(document).width());
            
            jQuery(ref.close).css({'top' : viewport.top, 'left' : viewport.left });
            
            jQuery(ref.content).css({'left' : Math.round( (viewport.width - jQuery(elm).width()) / 2 ) + viewport.left, 'top': Math.round( (viewport.height - jQuery(elm).height()) / 2 ) + viewport.top});
        }
    
        function flayer_destroy()
        {
            config.beforeEnd(elm);
            jQuery(window).unbind('scroll.fl');
            jQuery(window).unbind('resize.fl');
            jQuery(ref.container).unbind('click.fl');
            !!config.outerClose ? jQuery(window).unbind('keypress.fl') : -1;
            jQuery(config.returnParent).append(jQuery(elm).addClass('hidden'));
            jQuery(ref.container).remove();
            jQuery.browser.msie && jQuery.browser.version.substr(0,1)<7 ? show_selects() : -1;

            return true;
        }
        
        function embed()
        {
            ref.container = jQuery('<div>');
            jQuery(ref.container).addClass(config.className + '-container');
            jQuery(ref.container).css({
                position : 'absolute',
                left : '0',
                top : '0',
                display : 'none',
                zIndex: config.zIndex
            }).addClass('fl-cloud').addClass('hidden');
            
            ref.overlay = jQuery('<div>').addClass(config.className + '-layer');
            jQuery(ref.overlay).css({
                position : 'absolute',
                zIndex : config.zIndex,
                backgroundColor : config.bgcolor,
                opacity : config.opacity,
                zoom : 1
            }).addClass('fl-cloud');
            
            ref.content = jQuery('<div>').addClass(config.className);
            jQuery(ref.content).attr('id', config.id);
            jQuery(ref.content).css({
                position : 'absolute',
                zIndex: config.zIndex + 1
            }).addClass('fl-cloud');

            if(null == config.close || typeof(config.close) == 'undefined')
            {
                ref.close = jQuery('<div>').addClass(config.className);
                jQuery(ref.close).attr('id', config.closeClass);
                jQuery(ref.close).css({
                    position : 'absolute',
                    zIndex: config.zIndex + 1,
                    color: 'white',
                    cursor: 'pointer'
                }).addClass(config.closeClass);
                //jQuery(ref.close).text('[X]Close');
            }
            else
            {
                ref.close = jQuery(config.close).clone().addClass(config.closeClass);
            }

            jQuery(ref.container).append(ref.overlay);
            jQuery(ref.container).append(ref.iframe);
            jQuery(ref.container).append(ref.content);
            jQuery(ref.container).append(ref.close);
            
            return jQuery(document.body).prepend(ref.container);
        }

        function hide_selects()
        {
            /*jQuery('select').each(function(index, sb)
            {
                jQuery(sb).hide();
                var dummy = jQuery('<input>');
                jQuery(dummy).attr({'type':'text', 'value': jQuery(sb).val()});
                jQuery(dummy).addClass('dummy-select-box-ie6');
                jQuery(sb).after(dummy);
            });*/
        }

        function show_selects()
        {
            /*jQuery('select').each(function(index, sb)
            {
                jQuery(sb).show();
                jQuery(jQuery(sb).next('.dummy-select-box-ie6')).remove();
            });*/
        }
        
        function start()
        {
            //jQuery.browser.msie && jQuery.browser.version.substr(0,1)<7 ? hide_selects() : -1;
            config.beforeStart(elm);
            init();
            embed();
            position();
            jQuery(ref.content).append(jQuery(elm).removeClass('hidden'));
            start_ovservers();
            jQuery(ref.container).removeClass('hidden').css({'display':'block'});
            config.afterStart(elm);

        }
        //
        // Entry point
        start();
    }
})(jQuery);
