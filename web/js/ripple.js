(function(){
    "use strict";

    var colour = "#FFCC00";
    var opacity = 0.3;
    var ripple_within_elements = ['input', 'button', 'a', 'p', 'span', 'div'];
    var ripple_without_diameter = 50;

    var overlays = {
	items: [],
	get: function(){
	    var $element;
	    for(var i = 0; i < overlays.items.length; i++){
		$element = overlays.items[i];
		if($element.transition_phase === false) {
		    $element.transition_phase = 0;
		    return $element;
		}
	    }
	    $element = document.createElement("div");
	    $element.style.position = "absolute";
	    $element.style.opacity = opacity;
	    //$element.style.outline = "10px solid red";
	    $element.style.pointerEvents = "none";
	    $element.style.background = "-webkit-radial-gradient(" + colour + " 64%, rgba(0,0,0,0) 65%) no-repeat";
	    $element.style.background = "radial-gradient(" + colour + " 64%, rgba(0,0,0,0) 65%) no-repeat";
	    $element.style.transform = "translateZ(0)";
	    $element.transition_phase = 0;
	    $element.rid = overlays.items.length;
	    $element.next_transition = overlays.next_transition_generator($element);
	    document.body.appendChild($element);
	    overlays.items.push($element);
	    return $element;
	},
	next_transition_generator: function($element){
	    return function(){
		$element.transition_phase++;
		switch($element.transition_phase){
		    case 1:
			$element.style[transition] = "all 0.2s ease-in-out";
			$element.style.backgroundSize = $element.ripple_backgroundSize;
			$element.style.backgroundPosition = $element.ripple_backgroundPosition;
			setTimeout($element.next_transition, 0.2 * 1000); //now I know transitionend is better but it fires multiple times when multiple properties are animated, so this is simpler code and (imo) worth tiny delays
			break;
		    case 2:
			$element.style[transition] = "opacity 0.15s ease-in-out";
			$element.style.opacity = 0;
			setTimeout($element.next_transition, 0.15 * 1000);
			break;
		    case 3:
			overlays.recycle($element);
			break;
		}
	    };
	},
	recycle: function($element){
	    $element.style.display = "none";
	    $element.style[transition] = "none";
	    if($element.timer) clearTimeout($element.timer);
	    $element.transition_phase = false;
	}
    };

    var transition = function(){
	var i,
	    el = document.createElement('div'),
	    transitions = {
		'WebkitTransition':'webkitTransition',
		'transition':'transition',
		'OTransition':'otransition',
		'MozTransition':'transition'
	    };
	for (i in transitions) {
	    if (transitions.hasOwnProperty(i) && el.style[i] !== undefined) {
		return transitions[i];
	    }
	}
    }();

    var click = function(event){
	touch = event.touches ? event.touches[0] : event;

	if(!$(touch.target).hasClass('ripple')){
	    return ;
	}

        var darker = 1;
	if($(touch.target).hasClass('ripple-brighter')){
	    darker = 0;
	}

	colour = change_brightness($(touch.target).css('backgroundColor'), 35, darker);

	var $element = overlays.get(),
	    touch,
	    x,
	    y;
	$element.style[transition] = "none";
	$element.style.backgroundSize = "3px 3px";
	$element.style.opacity = opacity;

	if(ripple_within_elements.indexOf(touch.target.nodeName.toLowerCase()) > -1) {
	    x = touch.offsetX;
	    y = touch.offsetY;
	    
	    var dimensions = touch.target.getBoundingClientRect();
	    if(!x || !y){
		x = (touch.clientX || touch.x) - dimensions.left;
		y = (touch.clientY || touch.y) - dimensions.top;
	    }

	    $element.style.backgroundPosition = x + "px " + y + "px";
	    $element.style.width = dimensions.width + "px";
	    $element.style.height = dimensions.height + "px";
	    $element.style.left = (dimensions.left) + "px";
	    $element.style.top = (dimensions.top + document.body.scrollTop + document.documentElement.scrollTop) + "px";
	    var computed_style = window.getComputedStyle(event.target);
	    for (var key in computed_style) {
		if (key.toString().indexOf("adius") > -1) {
		    if(computed_style[key]) {
			$element.style[key] = computed_style[key];
		    }
		} else if(parseInt(key, 10).toString() === key && computed_style[key].indexOf("adius") > -1){
		    $element.style[computed_style[key]] = computed_style[computed_style[key]];
		}
	    }
	    $element.style.backgroundPosition = x + "px " + y + "px";
	    $element.ripple_backgroundPosition = (x - dimensions.width)  + "px " + (y - dimensions.width) + "px";
	    $element.ripple_backgroundSize = (dimensions.width * 2) + "px " + (dimensions.width * 2) + "px";

    	    $element.ripple_x = x;
	    $element.ripple_y = y;
    	    $element.style.display = "block";
	    setTimeout($element.next_transition, 20);
	}
    };

    if('ontouchstart' in window || 'onmsgesturechange' in window){
	document.addEventListener("touchstart", click, false);
    } else {
	document.addEventListener("click", click, false);
    }
}());



function change_brightness(rgb_color, percent, darker){
    darker = darker || 0;
    var parts = rgb_color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    delete(parts[0]);
    for (var i = 1; i <= 3; ++i) {
        parts[i] = parseInt(parts[i]).toString(16);
        if (parts[i].length == 1) parts[i] = '0' + parts[i];
    }
    hex = parts.join('');

    // convert 3 char codes --> 6, e.g. `E0F` --> `EE00FF`
    if(hex.length == 3){
        hex = hex.replace(/(.)/g, '$1$1');
    }

    var r = parseInt(hex.substr(0, 2), 16),
        g = parseInt(hex.substr(2, 2), 16),
        b = parseInt(hex.substr(4, 2), 16);

    if(darker){
        return '#' +
	   ((0|(1<<8) + r - (r) * percent / 100).toString(16)).substr(1) +
           ((0|(1<<8) + g - (g) * percent / 100).toString(16)).substr(1) +
	   ((0|(1<<8) + b - (b) * percent / 100).toString(16)).substr(1);
    }else{
        return '#' +
	   ((0|(1<<8) + r + (256 - r) * percent / 100).toString(16)).substr(1) +
           ((0|(1<<8) + g + (256 - g) * percent / 100).toString(16)).substr(1) +
	   ((0|(1<<8) + b + (256 - b) * percent / 100).toString(16)).substr(1);
    }
}