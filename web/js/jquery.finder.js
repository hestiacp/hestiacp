// jQuery finderSelect: a jQuery plugin that activates selecting elements
// within a parent with Ctrl+Click, Command+Click and Shift+Click.
//
// Copyright 2013 Mike Angell
//
// Please see:
//
// https://github.com/evulse/finderselect
//
// For complete documentation.

(function( $ ) {

    var d = $(document);
    var b = $('body');

    var commands = {
        highlight: highlight,
        unHighlight: unHighlight,
        highlightAll: highlightAll,
        unHighlightAll: unHighlightAll,
        selected: selected,
        children: children,
        update: update,
        addHook: addHook
    };

    var hooks = {};

    var o = {};

    var f = $.fn.finderSelect = function() {
        if (typeof arguments[0] === 'string') {
            var args = Array.prototype.slice.call(arguments);
            args.splice(0, 1);
            return commands[arguments[0]].apply(this, args);
        }
        else {
            finderSelect.apply(this, arguments);
            return this;
        }
        
    };

    function finderSelect(opt) {

        var p = $(this);
        var options = {
            selectClass: "selected",
            unSelectClass: "un-selected",
            currentClass: "selected-current",
            lastClass: "selected-last",
            shiftClass: "selected-shift",
            ctrlClass: "selected-ctrl",
            triggerUpdate: "finderSelectUpdate",
            children: false,
            event: "mousedown",
            cursor: "pointer",
            dragEvent: "mouseenter",
            enableClickDrag: true,
            enableShiftClick: true,
            enableCtrlClick: true,
            enableSingleClick: true,
            enableSelectAll: true,
            enableDisableSelection: true,
            enableTouchCtrlDefault: true,
            enableDesktopCtrlDefault: false,
            totalSelector: false,
            menuSelector: false,
            menuXOffset: 0,
            menuYOffset: 0

        };

        $.extend(options, opt);
        
        o = options;

        if(!o.children) {
            o.children = f.detect.children(p);
        }

        f.h.off(f.get.siblings(p,o), o);

        if(o.cursor) {
            f.set.cursor(p,o);
        }
        if(o.enableDisableSelection) {
            f.core.disableSelection(p,o);
        }
        if(o.enableClickDrag) {
            f.core.clickDrag(p,o);
        }
        if(o.enableSelectAll) {
            f.core.selectAll(p,o);
        }
        if(o.enableShiftClick || o.enableCtrlClick || o.enableSingleClick) {
            f.core.click(p,o);
        }
        if(o.totalSelector) {
            f.core.totalUpdate(p,o);
        }
        if(o.menuSelector) {
            f.core.loadMenu(p,o);
        }
    };
    
    function highlight(el) {
        f.h.on(el, o);
        return this;
    }
    function unHighlight(el) {
        f.h.off(el, o);
        return this;
    }
    function highlightAll() {
        var p = $(this);
        f.h.on(p.find(o.children), o);
        return this;
    }
    function unHighlightAll() {
        var p = $(this);
        f.h.off(p.find(o.children), o);
        return this;
    }
    function selected() {
        var p = $(this);
        return p.find(o.children+'.'+o.selectClass);
    }
    function children() {
        var p = $(this);
        return p.find(o.children);
    }
    function update() {
        var p = $(this);
        f.t.update(p, o);
        return this;
    }
    function addHook(hookName, fn) {
        if(!hooks[hookName]){
            hooks[hookName] = [];
        }
        hooks[hookName] = [fn];

        return this;
    }

    f.core = {
        clickDrag: function(p,o) {
            f.set.mouseDown(false);
            b.mousedown(function(e) {
                if(f.detect.leftMouse(e)) { f.set.mouseDown(true);}
            });
            b.mouseup(function(e) {
                if(f.detect.leftMouse(e)) { f.set.mouseDown(false);}
            });
            p.on(o.dragEvent, o.children, function(e){
                var c = f.get.clicks(p,o,$(this));

                if (f.get.mouseDown() && f.detect.ctrl(e)) {
                    f.t.deleteSelection(o);
                    f.t.toggleDrag(p,c,o);
                }
            });

            return p;
        },
        click: function(p,o) {
            p.on(o.event, o.children, function(e){
                if(f.detect.leftMouse(e)) {
                    if ($(e.target).hasClass('ch-toggle') ||
                        $(e.target).hasClass('check-label') ||
                        ( $(e.target).hasClass('l-unit-toolbar__col--left')) ) {

                        var c = f.get.clicks(p,o,$(this));
                        
                        var ref = $(e.target);
                        if (ref.parents('.l-unit').hasClass('selected')/* && $('.l-unit.selected').length == 1*/) {
                            ref.parents('.l-unit').find('.ch-toggle').attr('checked', false);
                            ref.parents('.l-unit').removeClass('selected');
                            ref.parents('.l-unit').removeClass('selected-current');
                            $('.toggle-all').removeClass('clicked-on');
                            return;
                        }

                        if (!(f.detect.ctrl(e) && o.enableCtrlClick) && (f.detect.shift(e) && o.enableShiftClick)) {
                            f.t.deleteSelection(o);
                            f.t.shiftClick(p,c,o);
                        }

                        if (((f.detect.ctrl(e) && o.enableCtrlClick) || (f.detect.touch() && o.enableTouchCtrlDefault) || o.enableDesktopCtrlDefault) && !(f.detect.shift(e) && o.enableShiftClick)) {
                            f.t.toggleClick(p,c,o);
                        }

                        if (!(f.detect.ctrl(e) && o.enableCtrlClick) && !(f.detect.shift(e) && o.enableShiftClick) && o.enableSingleClick && !o.enableDesktopCtrlDefault) {
                            f.t.singleClick(p,c,o);
                        }
                    }
                }

                o.onFinish(e);
            });
        },
        selectAll: function(p,o) {
            p.on('mouseover', function(){
                //d.on("keydown", turnOff);
            });
            p.on('mouseout', function(){
                //d.off("keydown", turnOff);
            });
            
            function turnOff(e) {
                if (f.detect.ctrl(e)) { 
/*
                  shortcut.add("Ctrl+a", function(evt){
                    if(jQuery('.ch-toggle:checked').length > 0) {
                      f.t.unHAll(p, o);
                      jQuery('.ch-toggle:checked').attr('checked', false);
                    } else {
                      f.t.hAll(p,o);
                    }
                  }, {
                    'type':             'keyup',
                    'propagate':        false,
                    'disable_in_input': true,
                    'target':           document
                    }
                  );
*/
                    if (e.keyCode == 65) { // ctrl + a
                        e.preventDefault();
                        //if(f.detect.alt(e)) {
                        if(jQuery('.ch-toggle:checked').length > 0) {
                            f.t.unHAll(p, o);
                            jQuery('.ch-toggle:checked').attr('checked', false);
                        } else {
                            f.t.hAll(p,o);
                        }
                    }
                }
                if (e.keyCode == 38) {
                    var last = f.get.click(p, o.shiftClass);
                    if(last.length == 0) {
                        last = f.get.click(p, o.lastClass);
                    }
                    var cur = f.get.prev(last,o);
                    if(last.length == 0) {
                        cur = p.find(o.children).last();
                    }
                    if(f.detect.alt(e)) {
                        cur = p.find(o.children).first();
                    }
                    e.preventDefault();
                    if(cur.length != 0) {

                        if(f.detect.shift(e) && o.enableShiftClick) {
                            var c = f.get.clicks(p,o,cur);
                            f.t.shiftClick(p,c,o);
                        } else {
                            var c = f.get.clicks(p,o,cur);
                            f.t.singleClick(p,c,o);
                        }
                    }
                }
                if (e.keyCode == 40) {
                    var last = f.get.click(p, o.shiftClass);
                    if(last.length == 0) {
                        last = f.get.click(p, o.lastClass);
                    }
                    var cur = f.get.next(last,o);
                    if(last.length == 0) {
                        cur = p.find(o.children).first();
                    }
                    if(f.detect.alt(e)) {
                        cur = p.find(o.children).last();
                    }
                    e.preventDefault();
                    if(cur.length != 0) {

                        if(f.detect.shift(e) && o.enableShiftClick) {
                            var c = f.get.clicks(p,o,cur);
                            f.t.shiftClick(p,c,o);
                        } else {
                            var c = f.get.clicks(p,o,cur);
                            f.t.singleClick(p,c,o);
                        }

                    }
                }
            }

        },
        totalUpdate: function(p,o) {
            p.on(o.triggerUpdate, function(){
                $(o.totalSelector).html($(this).find(o.children).filter('.'+o.selectClass).length)
            });
        },
        loadMenu: function(p, o) {
            p.bind("contextmenu",function(e){
                $(o.menuSelector).css({left:(e.pageX+o.menuXOffset),top:(e.pageY+o.menuYOffset)}).show();
                return false;
            }).bind("mousedown",function(){
                    $(o.menuSelector).hide();
                });
            $(o.menuSelector).bind("click",function(){
                $(this).hide();
            });
        },
        disableSelection: function(p, o) {
            d.on('keydown', function(){
                p.on("selectstart", turnOffSelection);
            }).on('keyup', function(){
                    p.off("selectstart", turnOffSelection);
                });

            function turnOffSelection(e) {
                e.preventDefault();
            }
        }
    };


    f.h = {
        on: function(el, o) {
            f.get.hook('highlight:before', [el, o]);
            el.removeClass(o.unSelectClass);
            el.addClass(o.selectClass);
            f.get.hook('highlight:after', [el, o]);

        },
        off: function(el,o) {
            f.get.hook('unHighlight:before', [el, o]);
            el.removeClass(o.selectClass);
            el.addClass(o.unSelectClass);
            f.get.hook('unHighlight:after', [el, o]);
        },
        tog: function(el,o) {

            el.each(function () {
                var child = $(this);
                if(f.detect.h(child, o)) {
                    f.h.off(child, o);
                } else {
                    f.h.on(child, o);
                }
            });
        },
        reset: function(el,o) {
            el.each(function () {
                var child = $(this);
                if(f.detect.lastH(child, o)) {
                    f.h.on(child, o);
                } else {
                    f.h.off(child, o);
                }
            });
 
        },
        state: function(el,o) {
            el.each(function () {
                var child = $(this);
                if(f.detect.h(child, o)) {
                    child.removeClass('stateUnSelected');
                    child.addClass('stateSelected');
                } else {
                    child.removeClass('stateSelected');
                    child.addClass('stateUnSelected');

                }
            });
            
        }
    };

    f.detect = {
        leftMouse: function(e) {
            return (e.which == 1);
        },
        shift: function(e) {
            return e.shiftKey;
        },
        alt: function(e) {
            return e.altKey;
        },
        ctrl: function(e) {
            return (e.ctrlKey || e.metaKey);
        },
        h: function(el,o) {
            return el.hasClass(o.selectClass);
        },
        lastH: function(el,o) {
            return el.hasClass('stateSelected');
        },
        touch: function() {
            return !!('ontouchstart' in window) // works on most browsers
                || !!('onmsgesturechange' in window); // works on ie10
        },
        children: function(el) {
            return el.children().get(0).tagName;
        }
    };

    f.set = {
        clicks: function(curr, shif, ctrl, p, o) {
            f.set.click(p, false, o.currentClass);
            f.set.click(p, curr, o.lastClass);
            f.set.click(p, shif,o.shiftClass);
            f.set.click(p, ctrl,o.ctrlClass);
            f.t.update(p, o);
        },
        click: function(p,el,c) {
            f.get.click(p,c).removeClass(c);
            if(el) { el.addClass(c); }
        },
        mouseDown: function(bool) {
            return b.data('down', bool);
        },
        cursor: function(p,o) {
            /* do not set cursor pointer inline styles
            var s = f.get.siblings(p,o);
            return s.css('cursor', o.cursor);*/
        }
    };

    f.get = {
        clicks: function(p, o, curr) {
            var c = {};
            f.set.click(p, curr, o.currentClass);
            c.current = {v:curr,c: o.currentClass};
            c.hard = {v:f.get.click(p, o.lastClass),c:o.lastClass};
            c.shift = {v:f.get.click(p, o.shiftClass),c:o.shiftClass};
            c.ctrl = {v:f.get.click(p, o.ctrlClass),c:o.ctrlClass};
            return c;
        },
        click: function(p,c) {
            return p.find('.'+c);
        },
        mouseDown: function() {
            return b.data('down');
        },
        siblings: function(p, o) {
            return p.find(o.children);
        },
        between: function(s,y, z) {
            if(s.index(y.v) < s.index(z.v)) {

                return f.get.elem(true, y.v, false, z.c);
            } else {
                return f.get.elem(false, y.v, false, z.c);
            }
        },
        elem: function(d, el, s, u) {
            var $els = [], $el = (d) ? el.next() : el.prev();
            while( $el.length ) {
                if(typeof u === 'undefined' || !u || !$el.hasClass(u)) {
                    if(typeof s === 'undefined' || !s || $el.hasClass(s)) {
                        $els.push($el[0]);
                    }
                    $el = (d) ? $el.next() : $el.prev();
                } else {
                    $el = {};
                }
            }
            return $($els)
        },
        next: function(p, o) {
            return p.next(o.children);
        },
        prev: function(p, o) {
            return p.prev(o.children);
        },
        hook: function(hookName, data){
            var hooked = hooks[hookName]

            if(hooked){
                    for(i=0; i<hooked.length; i++){
                        hooked[i].apply(undefined, data);
                    }
            }
        }

    };

    f.t = {
        update: function(el, o) {
            return el.trigger(o.triggerUpdate);
        },
        deleteSelection: function(o) {
            if(o.enableDisableSelection) {
                if(document.getSelection) {
                    var sel = document.getSelection();
                    if(sel.removeAllRanges) {
                        sel.removeAllRanges();
                    }
                }
            }
        },
        singleClick: function(p,c,o) {
            var s = f.get.siblings(p,o);
            //f.h.off(s, o);
            f.h.on(c.current.v, o);
            f.set.clicks(c.current.v, null, null, p, o);
        },
        toggleClick: function(p,c,o) {
            var s = f.get.siblings(p,o);
            f.h.tog(c.current.v, o);
            f.h.state(s,o);      
            f.set.clicks(c.current.v, null, null, p, o);
        },
        toggleClick: function(p,c,o) {
            var s = f.get.siblings(p,o);
            f.h.tog(c.current.v, o);
            f.h.state(s,o);
            f.set.clicks(c.current.v, null, null, p, o);
        },
        toggleDrag: function(p,c,o) {
            var s = f.get.siblings(p,o);
            f.h.reset(s,o);
            if(s.index(c.current.v) != s.index(c.hard.v)) {
                f.h.tog(f.get.between(s, c.current, c.hard), o);                       
                f.h.tog(c.current.v, o);
            }
            f.set.clicks(c.hard.v, null, null, p, o);

        },
        shiftClick: function(p, c, o) {
            var s = f.get.siblings(p,o);
            var z = s.index(c.current.v);
            var x = s.index(c.hard.v);
            if(c.hard.v.length != 0 && !f.detect.h(c.hard.v, o)) {
                var start = f.get.elem(true, c.hard.v, o.selectClass);
                if(start.length > 0) {
                    c.hard.v = $(start[0]);
                    f.set.click(p, c.hard.v, o.lastClass);
                } else {
                    var start = f.get.elem(z < x, c.hard.v, o.selectClass);
                    if(start.length > 0) {
                        start = (z > x ) ? $(start[0]) : $(start[start.length-1]);
                        c.hard.v = start;
                        f.set.click(p, c.hard.v, o.lastClass);
                    } else {
                        c.hard.v = s.first();
                        f.set.click(p, c.hard.v, o.lastClass);
                        f.t.singleClick(s,{current:{v:s.first()}},o);
                    }

                }

            }

            var x = s.index(c.hard.v);
            var y = s.index(c.shift.v);
            var z = s.index(c.current.v);


            if(c.hard.v.length == 0){
                f.t.singleClick(s,{current:{v:s.first()}},o);
            }

            if(c.shift.v.length != 0) {
                if((x < y && x < z && z < y) || (x > y && x > z && z > y)) {
                    f.h.off(f.get.between(s, c.shift, c.current), o);
                }
                if((x < y && x > z && z < y) || (x > y && x < z && z > y)) {
                    f.h.off(f.get.between(s, c.shift, c.hard), o);
                    f.h.on(f.get.between(s, c.current, c.hard), o);
                }
                if((x > y && x > z && z < y) || (x < y && x < z && z > y) || (x == y)) {
                    f.h.on(f.get.between(s, c.shift, c.current), o);
                } else {
                    f.h.off(c.shift.v, o);
                    f.t.unHExist(z>y, c.shift.v,o);
                }
            } else {
                f.t.unHExist(z>x,c.hard.v,o);
                f.h.on(f.get.between(s, c.current, c.hard), o);
            }

            f.h.on(c.current.v, o);
            f.set.clicks(c.hard.v, c.current.v, null, p, o);

        },
        unHAll: function(p,o) {
            f.h.off(p.find(o.children), o);
            f.t.update(p, o);
            $('.toggle-all').removeClass('clicked-on');
        },
        hAll: function(p,o) {
            f.h.on(p.find(o.children), o);
            f.t.update(p, o);
            o.toggleAllHook && o.toggleAllHook();
            $('.toggle-all').addClass('clicked-on');
        },
        unHExist: function(bool,el,o) {
            if(bool) {
                f.h.off(f.get.elem(false, el, false, o.unSelectClass), o);
            } else {

                f.h.off(f.get.elem(true, el, false, o.unSelectClass), o);
            }
        }

    };

})(window.jQuery || window.Zepto);
