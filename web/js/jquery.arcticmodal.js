/*

 arcticModal — jQuery plugin
 Version: 0.3
 Author: Sergey Predvoditelev (sergey.predvoditelev@gmail.com)
 Company: Arctic Laboratory (http://arcticlab.ru/)

 Docs & Examples: http://arcticlab.ru/arcticmodal/

 */
(function($) {


	var default_options = {

		type: 'html', // ajax или html
		content: '',
		url: '',
		ajax: {},
		ajax_request: null,

		closeOnEsc: true,
		closeOnOverlayClick: true,

		clone: false,

		overlay: {
			block: undefined,
			tpl: '<div class="arcticmodal-overlay"></div>',
			css: {
				backgroundColor: '#000',
				opacity: .6
			}
		},

		container: {
			block: undefined,
			tpl: '<div class="arcticmodal-container"><table class="arcticmodal-container_i"><tr><td class="arcticmodal-container_i2"></td></tr></table></div>'
		},

		wrap: undefined,
		body: undefined,

		errors: {
			tpl: '<div class="arcticmodal-error arcticmodal-close"></div>',
			autoclose_delay: 2000,
			ajax_unsuccessful_load: 'Error'
		},

		openEffect: {
			type: 'fade',
			speed: 400
		},
		closeEffect: {
			type: 'fade',
			speed: 400
		},

		beforeOpen: $.noop,
		afterOpen: $.noop,
		beforeClose: $.noop,
		afterClose: $.noop,
		afterLoading: $.noop,
		afterLoadingOnShow: $.noop,
		errorLoading: $.noop

	};


	var modalID = 0;
	var modals = $([]);


	var utils = {


		// Определяет произошло ли событие e вне блока block
		isEventOut: function(blocks, e) {
			var r = true;
			$(blocks).each(function() {
				if ($(e.target).get(0)==$(this).get(0)) r = false;
				if ($(e.target).closest('HTML', $(this).get(0)).length==0) r = false;
			});
			return r;
		}


	};


	var modal = {


		// Возвращает элемент, которым был вызван плагин
		getParentEl: function(el) {
			var r = $(el);
			if (r.data('arcticmodal')) return r;
			r = $(el).closest('.arcticmodal-container').data('arcticmodalParentEl');
			if (r) return r;
			return false;
		},


		// Переход
		transition: function(el, action, options, callback) {
			callback = callback==undefined ? $.noop : callback;
			switch (options.type) {
				case 'fade':
					action=='show' ? el.fadeIn(options.speed, callback) : el.fadeOut(options.speed, callback);
					break;
				case 'none':
					action=='show' ? el.show() : el.hide();
					callback();
					break;
			}
		},


		// Подготвка содержимого окна
		prepare_body: function(D, $this) {

			// Обработчик закрытия
			$('.arcticmodal-close', D.body).unbind('click.arcticmodal').bind('click.arcticmodal', function() {
				$this.arcticmodal('close');
				return false;
			});

		},


		// Инициализация элемента
		init_el: function($this, options) {
			var D = $this.data('arcticmodal');
			if (D) return;

			D = options;
			modalID++;
			D.modalID = modalID;

			// Overlay
			D.overlay.block = $(D.overlay.tpl);
			D.overlay.block.css(D.overlay.css);

			// Container
			D.container.block = $(D.container.tpl);

			// BODY
			D.body = $('.arcticmodal-container_i2', D.container.block);
			if (options.clone) {
				D.body.html($this.clone(true));
			} else {
				$this.before('<div id="arcticmodalReserve' + D.modalID + '" style="display: none" />');
				D.body.html($this);
			}

			// Подготовка содержимого
			modal.prepare_body(D, $this);

			// Закрытие при клике на overlay
			if (D.closeOnOverlayClick)
				D.overlay.block.add(D.container.block).click(function(e) {
					if (utils.isEventOut($('>*', D.body), e))
						$this.arcticmodal('close');
				});

			// Запомним настройки
			D.container.block.data('arcticmodalParentEl', $this);
			$this.data('arcticmodal', D);
			modals = $.merge(modals, $this);

			// Показать
			$.proxy(actions.show, $this)();
			if (D.type=='html') return $this;

			// Ajax-загрузка
			if (D.ajax.beforeSend!=undefined) {
				var fn_beforeSend = D.ajax.beforeSend;
				delete D.ajax.beforeSend;
			}
			if (D.ajax.success!=undefined) {
				var fn_success = D.ajax.success;
				delete D.ajax.success;
			}
			if (D.ajax.error!=undefined) {
				var fn_error = D.ajax.error;
				delete D.ajax.error;
			}
			var o = $.extend(true, {
				url: D.url,
				beforeSend: function() {
					if (fn_beforeSend==undefined) {
						D.body.html('<div class="arcticmodal-loading" />');
					} else {
						fn_beforeSend(D, $this);
					}
				},
				success: function(responce) {

					// Событие после загрузки до показа содержимого
					$this.trigger('afterLoading');
					D.afterLoading(D, $this, responce);

					if (fn_success==undefined) {
						D.body.html(responce);
					} else {
						fn_success(D, $this, responce);
					}
					modal.prepare_body(D, $this);

					// Событие после загрузки после отображения содержимого
					$this.trigger('afterLoadingOnShow');
					D.afterLoadingOnShow(D, $this, responce);

				},
				error: function() {

					// Событие при ошибке загрузки
					$this.trigger('errorLoading');
					D.errorLoading(D, $this);

					if (fn_error==undefined) {
						D.body.html(D.errors.tpl);
						$('.arcticmodal-error', D.body).html(D.errors.ajax_unsuccessful_load);
						$('.arcticmodal-close', D.body).click(function() {
							$this.arcticmodal('close');
							return false;
						});
						if (D.errors.autoclose_delay)
							setTimeout(function() {
								$this.arcticmodal('close');
							}, D.errors.autoclose_delay);
					} else {
						fn_error(D, $this);
					}
				}
			}, D.ajax);
			D.ajax_request = $.ajax(o);

			// Запомнить настройки
			$this.data('arcticmodal', D);

		},


		// Инициализация
		init: function(options) {
			options = $.extend(true, {}, default_options, options);
			if ($.isFunction(this)) {
				if (options==undefined) {
					$.error('jquery.arcticmodal: Uncorrect parameters');
					return;
				}
				if (options.type=='') {
					$.error('jquery.arcticmodal: Don\'t set parameter "type"');
					return;
				}
				switch (options.type) {
					case 'html':
						if (options.content=='') {
							$.error('jquery.arcticmodal: Don\'t set parameter "content"');
							return
						}
						var c = options.content;
						options.content = '';

						return modal.init_el($(c), options);
						break;
					case 'ajax':
						if (options.url=='') {
							$.error('jquery.arcticmodal: Don\'t set parameter "url"');
							return;
						}
						return modal.init_el($('<div />'), options);
						break;
				}
			} else {
				return this.each(function() {
					modal.init_el($(this), $.extend(true, {}, options));
				});
			}
		}


	};


	var actions = {


		// Показать
		show: function() {
			var $this = modal.getParentEl(this);
			if ($this===false) {
				$.error('jquery.arcticmodal: Uncorrect call');
				return;
			}
			var D = $this.data('arcticmodal');

			// Добавить overlay и container
			D.overlay.block.hide();
			D.container.block.hide();
			$('BODY').append(D.overlay.block);
			$('BODY').append(D.container.block);

			// Событие
			D.beforeOpen(D, $this);
			$this.trigger('beforeOpen');

			// Wrap
			if (D.wrap.css('overflow')!='hidden') {
				D.wrap.data('arcticmodalOverflow', D.wrap.css('overflow'));
				var w1 = D.wrap.outerWidth(true);
				D.wrap.css('overflow', 'hidden');
				var w2 = D.wrap.outerWidth(true);
				if (w2!=w1)
					D.wrap.css('marginRight', (w2 - w1) + 'px');
			}

			// Скрыть предыдущие оверлеи
			modals.not($this).each(function() {
				var d = $(this).data('arcticmodal');
				d.overlay.block.hide();
			});

			// Показать
			modal.transition(D.overlay.block, 'show', modals.length>1 ? {type: 'none'} : D.openEffect);
			modal.transition(D.container.block, 'show', modals.length>1 ? {type: 'none'} : D.openEffect, function() {
				D.afterOpen(D, $this);
				$this.trigger('afterOpen');
			});

			return $this;
		},


		// Закрыть
		close: function() {
			if ($.isFunction(this)) {
				modals.each(function() {
					$(this).arcticmodal('close');
				});
			} else {
				return this.each(function() {
					var $this = modal.getParentEl(this);
					if ($this===false) {
						$.error('jquery.arcticmodal: Uncorrect call');
						return;
					}
					var D = $this.data('arcticmodal');

					// Событие перед закрытием
					if (D.beforeClose(D, $this)===false) return;
					$this.trigger('beforeClose');

					// Показать предыдущие оверлеи
					modals.not($this).last().each(function() {
						var d = $(this).data('arcticmodal');
						d.overlay.block.show();
					});

					modal.transition(D.overlay.block, 'hide', modals.length>1 ? {type: 'none'} : D.closeEffect);
					modal.transition(D.container.block, 'hide', modals.length>1 ? {type: 'none'} : D.closeEffect, function() {

						// Событие после закрытия
						D.afterClose(D, $this);
						$this.trigger('afterClose');

						// Если не клонировали - вернём на место
						if (!D.clone)
							$('#arcticmodalReserve' + D.modalID).replaceWith(D.body.find('>*'));

						D.overlay.block.remove();
						D.container.block.remove();
						$this.data('arcticmodal', null);
						if (!$('.arcticmodal-container').length) {
							if (D.wrap.data('arcticmodalOverflow'))
								D.wrap.css('overflow', D.wrap.data('arcticmodalOverflow'));
							D.wrap.css('marginRight', 0);
						}

					});

					if (D.type=='ajax')
						D.ajax_request.abort();

					modals = modals.not($this);
				});
			}
		},


		// Установить опции по-умолчанию
		setDefault: function(options) {
			$.extend(true, default_options, options);
		}


	};


	$(function() {
		default_options.wrap = $((document.all && !document.querySelector) ? 'html' : 'body');
	});


	// Закрытие при нажатии Escape
	$(document).bind('keyup.arcticmodal', function(e) {
		var m = modals.last();
		if (!m.length) return;
		var D = m.data('arcticmodal');
		if (D.closeOnEsc && (e.keyCode===27))
			m.arcticmodal('close');
	});


	$.arcticmodal = $.fn.arcticmodal = function(method) {

		if (actions[method]) {
			return actions[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method==='object' || !method) {
			return modal.init.apply(this, arguments);
		} else {
			$.error('jquery.arcticmodal: Method ' + method + ' does not exist');
		}

	};


})(jQuery);