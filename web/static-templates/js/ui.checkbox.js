/**
 * @author alexander.farkas
 * @version 1.4.3
 */
(function($){
	
	var supportsValidity;
	(function(){
		if(!$.prop || supportsValidity){return;}
		var supportTest = function(){
			supportsValidity = !!$('<input />').prop('validity');
		};
		supportTest();
		$(supportTest);
	})();
	
    $.widget('ui.checkBox', {
		options: {
	        hideInput: true,
			addVisualElement: true,
			addLabel: true
	    },
        _create: function(){
            var that = this, 
				opts = this.options
			;
			
			if(!this.element.is(':radio,:checkbox')){
				if(this.element[0].elements && $.nodeName(this.element[0], 'form')){
					$(this.element[0].elements).filter(':radio,:checkbox').checkBox(opts);
				}
				return false;
			}
			
			this._proxiedReflectUI = $.proxy(this, 'reflectUI');
			
            this.labels = $([]);
			
            this.checkedStatus = false;
			this.disabledStatus = false;
			this.hoverStatus = false;
            
			this.inputType = this.element[0].type;
            this.radio = this.inputType == 'radio';
					
            this.visualElement = $([]);
            if (opts.hideInput) {
				this.element.addClass('ui-helper-hidden-accessible');
				if(opts.addVisualElement){
					this.visualElement = $('<span />')
						.addClass('ui-'+this.inputType)
					;
					this.element.after(this.visualElement[0]);
				}
            }
			
			if(opts.addLabel){
				var id = this.element[0].id;
				if(id){
					this.labels = $('label[for="' + id + '"]', this.element[0].form || this.element[0].ownerDocument).add(this.element.parent('label'));
				}
				if(!this.labels[0]){
					this.labels = this.element.closest('label', this.element[0].form);
				}
				this.labels.addClass(this.radio ? 'ui-radio' : 'ui-checkbox');
			}
			
			this.visualGroup = this.visualElement.add(this.labels);
			
			this._addEvents();
			
			this.initialized = true;
            this.reflectUI({type: 'initialreflect'});
			return undefined;
        },
		_addEvents: function(){
			var that 		= this, 
			
				opts 		= this.options,
					
				toggleHover = function(e){
					if(that.disabledStatus){
						return false;
					}
					that.hover = (e.type == 'focus' || e.type == 'mouseenter');
					if(e.type == 'focus'){
						that.visualGroup.addClass(that.inputType +'-focused');
					} else if(e.type == 'blur'){
						that.visualGroup.removeClass(that.inputType +'-focused');
					}
					that._changeStateClassChain();
					return undefined;
				}
			;
			
			this.element
				.bind('click.checkBox invalid.checkBox', this._proxiedReflectUI)
				.bind('focus.checkBox blur.checkBox', toggleHover)
			;
			if (opts.hideInput){
				this.element
					.bind('usermode', function(e){
	                    (e.enabled &&
	                        that.destroy.call(that, true));
	                })
				;
            }
			if(opts.addVisualElement){
				this.visualElement
					.bind('click.checkBox', function(e){
						that.element[0].click();
						return false;
					})
				;
			}
			
			this.visualGroup.bind('mouseenter.checkBox mouseleave.checkBox', toggleHover);
			
		},
		_changeStateClassChain: function(){
			var allElements = this.labels.add(this.visualElement),
				stateClass 	= '',
				baseClass 	= 'ui-'+ this.inputType
			;
				
			if(this.checkedStatus){
				stateClass += '-checked'; 
				allElements.addClass(baseClass+'-checked');
			} else {
				allElements.removeClass(baseClass+'-checked');
			}
			
			if(this.disabledStatus){
				stateClass += '-disabled'; 
				allElements.addClass(baseClass+'-disabled');
			} else {
				allElements.removeClass(baseClass+'-disabled');
			}
			if(this.hover){
				stateClass += '-hover'; 
				allElements.addClass(baseClass+'-hover');
			} else {
				allElements.removeClass(baseClass+'-hover');
			}
			
			baseClass += '-state';
			if(stateClass){
				stateClass = baseClass + stateClass;
			}
			
			function switchStateClass(){
				var classes = this.className.split(' '),
					found = false;
				$.each(classes, function(i, classN){
					if(classN.indexOf(baseClass) === 0){
						found = true;
						classes[i] = stateClass;
						return false;
					}
					return undefined;
				});
				if(!found){
					classes.push(stateClass);
				}
				this.className = classes.join(' ');
			}
			
			this.visualGroup.each(switchStateClass);
		},
        destroy: function(onlyCss){
            this.element.removeClass('ui-helper-hidden-accessible');
			this.visualElement.addClass('ui-helper-hidden');
            if (!onlyCss) {
                var o = this.options;
                this.element.unbind('.checkBox');
				this.visualElement.remove();
                this.labels
					.unbind('.checkBox')
					.removeClass('ui-state-hover ui-state-checked ui-state-disabled')
				;
            }
        },
		
        disable: function(status){
			if(status === undefined){
				status = true;
			}
            this.element[0].disabled = status;
            this.reflectUI({type: 'manuallydisabled'});
        },
		
        enable: function(){
            this.element[0].disabled = false;
            this.reflectUI({type: 'manuallyenabled'});
        },
		
        toggle: function(e){
            this.changeCheckStatus(!(this.element.is(':checked')), e);
        },
		
        changeCheckStatus: function(status, e){
            if(e && e.type == 'click' && this.element[0].disabled){
				return false;
			}
			this.element[0].checked = !!status;
            this.reflectUI(e || {
                type: 'changecheckstatus'
            });
			return undefined;
        },
        propagate: function(n, e, _noGroupReflect){
			if(!e || e.type != 'initialreflect'){
				if (this.radio && !_noGroupReflect) {
					var elem = this.element[0];
					//dynamic
	                $('[name="'+ elem.name +'"]', elem.form || elem.ownerDocument).checkBox('reflectUI', e, true);
						
	            }
	            return this._trigger(n, e, {
	                options: this.options,
	                checked: this.checkedStatus,
	                labels: this.labels,
					disabled: this.disabledStatus
	            });
			}
			return undefined;
        },
		changeValidityState: function(){
			if(supportsValidity){
				this.visualGroup[ !this.element.prop('willValidate') || (this.element.prop('validity') || {valid: true}).valid ? 'removeClass' : 'addClass' ](this.inputType +'-invalid');
			}
		},
        reflectUI: function(e){
			
            var oldChecked 			= this.checkedStatus, 
				oldDisabledStatus 	= this.disabledStatus
			;
            					
			this.disabledStatus = this.element.is(':disabled');
			this.checkedStatus = this.element.is(':checked');
			if(!e || e.type !== 'initialreflect'){
				this.changeValidityState();
			}
			
			if (this.disabledStatus != oldDisabledStatus || this.checkedStatus !== oldChecked) {
				this._changeStateClassChain();
				
				(this.disabledStatus != oldDisabledStatus &&
					this.propagate('disabledchange', e));
				
				(this.checkedStatus !== oldChecked &&
					this.propagate('change', e));
			}
            
        }
    });
		
	if($.propHooks){
		$.each({checked: 'changeCheckStatus', disabled: 'disable'}, function(name, fn){
			//be hook friendly
			if(!$.propHooks[name]){
				$.propHooks[name] = {};
			}
			var oldSetHook = $.propHooks[name].set;
			
			$.propHooks[name].set = function(elem, value){
				var widget = $.data(elem, 'checkBox');
				if(widget){
					widget[fn](!!value);
				}
				return oldSetHook && oldSetHook(elem, value) ;
			};
			
		});
	}
})(jQuery);
