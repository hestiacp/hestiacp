<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=__('File Manager')?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="/images/filemanager.ico?" type="image/x-icon">
<link rel="icon" href="/images/filemanager.ico?" type="image/x-icon">
<link rel="stylesheet" href="/css/file_manager.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="/css/uploadify.css" />
<link href="//cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.2/fotorama.css" rel="stylesheet">
<style type="text/css" media="print, screen and (min-width: 481px)"></style>
<!-- link rel="shortcut icon" href="/2008/site/images/favicon.ico" type="image/x-icon" / -->
<link rel="stylesheet" href="/css/jquery.arcticmodal.css">
<link rel="stylesheet" href="/css/jquery.fileupload.css">
<script> GLOBAL = {}; </script>
</head>
<body>
    <a href="#" class="to-shortcuts">
        <i class="l-icon-shortcuts"></i>
    </a>

    <div id="main">
        <div class="window active">
            <a href="/" class="l-logo"></a>
            <div class="pwd pwd-tab-A"></div>
            <div class="menu menu-left menu-A">
                <?php $pre_tab = 'A';include($_SERVER['DOCUMENT_ROOT'].'/templates/file_manager/tab_menu.php'); ?>
            </div>
            <ul class="listing listing-left" onClick="FM.setTabActive('.listing-left');"></ul>
        </div>

        <div class="window">
            <div class="pwd pwd-tab-B"></div>
            <div class="menu menu-right menu-B">
                <?php $pre_tab = 'B';include($_SERVER['DOCUMENT_ROOT'].'/templates/file_manager/tab_menu.php'); ?>
            </div>
            <ul class="listing listing-right" onClick="FM.setTabActive('.listing-right');"></ul>
        </div>
        </div>

    <div class="warning-box inform hidden">
        <div class="close ripple"></div>
        <div class="message">Please Read the reading text at the reading write!</div>
        <div class="message-small">writing the reading text at the reading write writing the reading text at the reading write!</div>
    </div>

    <div class="warning-box reload hidden">
        <div class="message-small">Hit F5 to reload the page</div>
    </div>


    <div class="warning-box hidden">
        <div class="close ripple"></div>
        <div class="message">Please Read the reading text at the reading write!</div>
        <div class="message-small">writing the reading text at the reading write writing the reading text at the reading write!</div>
    </div>


    <ul class="context-menu tab-a sort-order hidden">
        <li entity="type"><span class="type active"><?=__('type')?></span><span class="up">&nbsp;</span></li>
        <li entity="size"><span class="size"><?=__('size')?></span><span class="up">&nbsp;</span></li>
        <li entity="date"><span class="date"><?=__('date')?></span><span class="up">&nbsp;</span></li>
        <li entity="name" class="last"><span class="name"><?=__('name')?></span><span class="up">&nbsp;</span></li>
    </ul>

    <ul class="context-menu tab-b sort-order hidden">
        <li entity="type"><span class="type active"><?=__('type')?></span><span class="up">&nbsp;</span></li>
        <li entity="size"><span class="size"><?=__('size')?></span><span class="up">&nbsp;</span></li>
        <li entity="date"><span class="date"><?=__('date')?></span><span class="up">&nbsp;</span></li>
        <li entity="name" class="last"><span class="name"><?=__('name')?></span><span class="up">&nbsp;</span></li>
    </ul>



            <div class="fotorama" data-auto="false"></div>
            <div class="progress-container hidden">
            <div class="progress-elm"><span class="title"><?=__('Initializing')?></span><span class="progress" style="backround-position: -96px;  backround-position:-10px"></span><span class="close hidden"></span></div>
        </div>

    <div class="shortcuts" style="display:none">
      <div class="header">
        <div class="title">Shortcuts</div>
        <div class="close"></div>
      </div>
      <ul>
        <li><span class="key">u</span><?=__('Upload')?></li>
        <li><span class="key">n</span><?=__('New File')?></li>
        <li><span class="key">F7</span><?=__('New Folder')?></li>
        <li><span class="key">d</span><?=__('Download')?></li>
        <li><span class="key">F2 / Shift+F6</span><?=__('Rename')?></li>
        <li><span class="key">m</span><?=__('Move')?></li>
        <li><span class="key">F5</span><?=__('Copy')?></li>
        <li><span class="key">a</span><?=__('Archive')?></li>
        <li><span class="key">F8 / Del</span><?=__('Delete')?></li>
        <li class="step-top"><span class="key">Ctrl + s</span><?=__('Save File (in text editor)')?></li>
        <li class="step-top"><span class="key">h</span><?=__('Display/Close shortcuts')?></li>
        <li class="step-top"><span class="key">Esc</span><?=__('Close Popup / Cancel')?></li>
      </ul>
      <ul>
        <li><span class="key bigger">&uarr;</span><?=__('Move Cursor Up')?></li>
        <li><span class="key bigger">&darr;</span><?=__('Move Cursor Down')?></li>
        <li><span class="key bigger">&larr;</span><?=__('Switch to Left Tab')?></li>
        <li><span class="key bigger">&rarr;</span><?=__('Switch to Right Tab')?></li>
        <li><span class="key">Tab</span><?=__('Switch Tab')?></li>
        <li><span class="key">Home</span><?=__('Go to the Top of the File List')?></li>
        <li><span class="key">End</span><?=__('Go to the Last File')?></li>
        <li class="step-top"><span class="key">Enter</span><?=__('Open File / Enter Directory')?></li>
        <li><span class="key">F4</span><?=__('Edit File')?></li>
        <li><span class="key">Backspace</span><?=__('Go to Parent Directory')?></li>
        <li class="step-top"><span class="key">Insert / Space</span><?=__('Select Current File')?></li>
        <li><span class="key">Shift + click</span><?=__('Select Bunch of Files')?></li>
        <li><span class="key">Ctrl + click</span><?=__('Add File to the Current Selection')?></li>
        <li><span class="key">Ctrl + a</span><?=__('Select All Files')?></li>
      </ul>
      <ul class="note"><?=__('shortcuts are inspired by magnificent GNU <a href="https://www.midnight-commander.org/">Midnight Commander</a> file manager')?></ul>
    </div>


        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.2/fotorama.js"></script>
        <script type="text/javascript" src="/js/jquery-ui.min.js"></script>
        <script src="/js/jquery.finder.js"></script>
        <script type="text/javascript" src="/js/hotkeys.js"></script>
        <script type="text/javascript" src="/js/app.js"></script>
        <script type="text/javascript"><?php echo include($_SERVER['DOCUMENT_ROOT'].'/js/i18n.js.php'); ?></script>
        <script type="text/javascript" src="/js/templates.js"></script>
        <script type="text/javascript" src="/js/floating_layer.js"></script>
        <script src="/js/ripple.js"></script>

        <?php if (!empty($GLOBAL_JS)): ?>
            <?php echo $GLOBAL_JS; ?>
        <?php endif; ?>

        <script type="text/javascript" src="/js/file_manager.js"></script>

        <script src="/js/jquery.iframe-transport.js"></script>
        <script src="/js/jquery.fileupload.js"></script>
        <script src="/js/jquery.arcticmodal.js"></script>

        <script type="text/javascript">
        $(function () {
            'use strict';
            // Change this to the location of your server-side upload handler:
            var show_msg = false;
            var acc = $('<div>');
            $(['A', 'B']).each(function(k, letter) {
                var url = '/upload/';
                $('#file_upload_' + letter).fileupload({
                    singleFileUploads: false,
                    add: function (e, data) {
                        FM.setTabActive(FM['TAB_'+letter]);

                        var tab = FM.getTabLetter(FM.CURRENT_TAB);
                        var file_relocation = FM['TAB_'+tab+'_CURRENT_PATH'];


                        $('#file_upload_' + letter).fileupload("option", "url", url + '?dir=' + file_relocation);
                        acc = $('<div>');
                        show_msg = false;
                        data.submit();
                        $('.file-upload-button-' + tab).addClass('progress');
                    },
                    url: url,
                    dataType: 'json',
                    done: function (e, data) {
                        var msg = '';
                        $.each(data.result.files, function (index, file) {
                            if ('undefined' != typeof file.error) {
                                msg += '<p class="msg-item">' + file.name + ': ' + file.error + '</p>';
                            }
                        });

                        if (msg != '') {
                            var tpl = Tpl.get('popup_alert', 'FM');
                            tpl.set(':TEXT', msg);
                            FM.popupOpen(tpl.finalize());
                        }
                        //console.log(e);
                        //console.log(data);
                    },
                    fail: function(e, data) {
                        var msg = '';
                        $.each(data.result.files, function (index, file) {
                            if ('undefined' != typeof file.error) {
                                msg += '<p class="msg-item">' + file.name + ': ' + file.error + '</p>';
                            }
                        });

                        if (msg != '') {
                            var tpl = Tpl.get('popup_alert', 'FM');
                            tpl.set(':TEXT', msg);
                            FM.popupOpen(tpl.finalize());
                        }
                        //console.log(e);
                        //console.log(data);
                    },
                    always: function(e, data) {
                        var tab = FM.getTabLetter(FM.CURRENT_TAB);
                        var box = FM['TAB_' + tab];
                        FM.openAndSync(FM['TAB_' + tab + '_CURRENT_PATH'], box);

                        $('.file-upload-button-' + tab).addClass('done');

                        setTimeout(function() {
                            $('.file-upload-button-' + tab).removeClass('progress');
                            $('.file-upload-button-' + tab).removeClass('done');
                        }, 2000);

                        $('.file-upload-button-' + tab).css('background-position', '-96px 0');
                    },
                    progressall: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        var tab = FM.getTabLetter(FM.CURRENT_TAB);
                        $('.file-upload-button-' + tab).css('background-position', '-' + (100 - progress) + 'px 0');
                    }
                })
                .prop('disabled', !$.support.fileInput)
                    .parent().addClass($.support.fileInput ? undefined : 'disabled');
            });
            
            $.widget("shift.selectable", $.ui.selectable, {
                options: {}, // required
                previousIndex: -1, // additional attribute to store previous selection index
                currentIndex: -1, // additional attribute to store current selection index
                _create: function() { // required
                    var self = this;

                    $.ui.selectable.prototype._create.call(this); // default implementation

                    // here is our addition, we are catching "selecting" event with shift key
                    $(this.element).on('selectableselecting', function(event, ui){
                        self.currentIndex = $(ui.selecting.tagName, event.target).index(ui.selecting);
                        if(event.shiftKey && self.previousIndex > -1) {
                            $(ui.selecting.tagName, event.target).slice(Math.min(self.previousIndex, self.currentIndex), 1 + Math.max(self.previousIndex, self.currentIndex)).addClass('ui-selected');
                            self.previousIndex = -1;
                        } else {
                            self.previousIndex = self.currentIndex;
                        }
                    });
                },
                destroy: function() { // required, default implementation
                    $.ui.selectable.prototype.destroy.call(this);
                },
                _setOption: function() { // required, default implementation
                    $.ui.selectable.prototype._setOption.apply(this, arguments);
                }
            });


            var checkIfArchive = function(item) {console.log(item);
                var item = $(item).hasClass('dir') ? item : $(item).parents('.dir');
                var tab = FM.getTabLetter(FM.CURRENT_TAB);
                var src = $(item).find('.source').val();
                src = $.parseJSON(src);
                var tab = FM.getTabLetter(FM.CURRENT_TAB);

                if (FM.itemIsArchieve(src)) {
                    if($('.menu-'+tab+' .archive.button').first().is(':visible'))
                        $('.menu-'+tab+' .extract-btn').first().show();
                    else
                        $('.menu-'+tab+' .extract-btn.small').show();
                } else {
                    $('.menu-'+tab+' .extract-btn').hide();
                }
            }
            
            
            $(".listing-left").selectable({
                selected: function (event, ui) {
					FM.setTabActive(FM.TAB_A, 'skip_highlights');
					
					$(".listing-left .active").removeClass('active');
					
                    $(".listing-left .selected").each(function(i, o) {
                        if (!$(o).hasClass('ui-selected')) {
                            $(o).removeClass('selected');
                            $(o).removeClass('active');
                        }
                    });
                    $(ui.selected).addClass('selected');
                    $(ui.selected).addClass('active');
                    
                    
                    checkIfArchive(ui.selected);
                    $(".listing-left .ui-selected").addClass('selected');
                    
                    if ($(".listing-left .active").length > 0) {
						FM['CURRENT_A_LINE'] = $(".listing-left .active").index();
					}
					else {
						FM['CURRENT_A_LINE'] = 0;
					}
					
					FM.preselectedItems.A = [];

                },
                unselected: function (event, ui) {
					FM.setTabActive(FM.TAB_A, 'skip_highlights');
					
					$(ui.unselected).removeClass('selected');
					$(ui.unselected).removeClass('active');
					
					if ($(".listing-left .active").length > 0) {
						FM['CURRENT_A_LINE'] = $(".listing-left .active").index();
					}
					else {
						FM['CURRENT_A_LINE'] = 0;
					}
					
                }
            });
            $(".listing-right").selectable({
                selected: function (event, ui) {
                    FM.setTabActive(FM.TAB_B, 'skip_highlights');
					
					$(".listing-right .active").removeClass('active');
					
                    $(".listing-right .selected").each(function(i, o) {
                        if (!$(o).hasClass('ui-selected')) {
                            $(o).removeClass('selected');
                            $(o).removeClass('active');
                        }
                    });
                    $(ui.selected).addClass('selected');
                    $(ui.selected).addClass('active');
                    
                    
                    //$(ui.selected).addClass('active');
                    checkIfArchive(ui.selected);
                    $(".listing-right .ui-selected").addClass('selected');
                    
                    
                    if ($(".listing-right .active").length > 0) {
						FM['CURRENT_B_LINE'] = $(".listing-right .active").index();
					}
					else {
						FM['CURRENT_B_LINE'] = 0;
					}
					
					FM.preselectedItems.B = [];
                    
                },
                unselected: function (event, ui) {
                    FM.setTabActive(FM.TAB_B, 'skip_highlights');
					
					$(ui.unselected).removeClass('selected');
					$(ui.unselected).removeClass('active');
					
					if ($(".listing-right .active").length > 0) {
						FM['CURRENT_B_LINE'] = $(".listing-right .active").index();
					}
					else {
						FM['CURRENT_B_LINE'] = 0;
					}
                }


            });
        });
        </script>
</body>
</html>
