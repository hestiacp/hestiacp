/**
 * @author trixta
 */
(function($){
	$.userMode = (function(){
		var userBg, 
			timer, 
			testDiv,
			boundEvents = 0;
		
		function testBg(){
			testDiv = testDiv || $('<div></div>').css({position: 'absolute', left: '-999em', top: '-999px', width: '0px', height: '0px'}).appendTo('body');
			var black = $.curCSS( testDiv.css({backgroundColor: '#000000'})[0], 'backgroundColor', true),
				white = $.curCSS( testDiv.css({backgroundColor: '#ffffff'})[0], 'backgroundColor', true),
				newBgStatus = (black === white || white === 'transparent');
			if(newBgStatus != userBg){
				userBg = newBgStatus;
				$.event.trigger('_internalusermode');
			}
			return userBg;
		}
		
		function init(){
			testBg();
			timer = setInterval(testBg, 3000);
		}
		
		function stop(){
			clearInterval(timer);
			testDiv.remove();
			testDiv = null;
		}
		
		$.event.special.usermode = {
			setup: function(){
				(!boundEvents && init());
				boundEvents++;
				var jElem = $(this)
					.bind('_internalusermode', $.event.special.usermode.handler);
				//always trigger
				setTimeout(function(){
					jElem.triggerHandler('_internalusermode');
				}, 1);
                return true;
            },
			teardown: function(){
                boundEvents--;
				(!boundEvents && stop());
				$(this).unbind('_internalusermode', $.event.special.usermode.handler);
                return true;
            },
            handler: function(e){
                e.type = 'usermode';
				e.disabled = !userBg;
				e.enabled = userBg;
                return jQuery.event.handle.apply(this, arguments);
            }
		};
		
		return {
			get: testBg
		};
		
	})();
	
	$.fn.userMode = function(fn){
		return this[(fn) ? 'bind' : 'trigger']('usermode', fn);
	};
	
	$(function(){
		$('html').userMode(function(e){
			$('html')[e.enabled ? 'addClass' : 'removeClass']('hcm');
		});
	});
})(jQuery);
