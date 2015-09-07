<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Filemanager</title>
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
</head>
<body>
    <div id="main">
        <div class="window active">
            <a href="/" class="l-logo"></a>
            <div class="pwd pwd-tab-A">
           <? /* <? foreach($pwd as $dir ){ 
                $path .= '/'.$dir;
                echo '<a href="/admin.php?page=tor/index.php&files=1&path='.$path.'">'.$dir.'</a>';
            } ?> */ ?>
            </div>
            <div class="menu menu-left menu-A">
                <?php $pre_tab = 'A';include($_SERVER['DOCUMENT_ROOT'].'/templates/file_manager/tab_menu.php'); ?>
            </div>

            <ul class="listing listing-left">
                <?
                /*if(count($pwd) > 1){
                    echo '<li class="back">
                        <span class="marker"></span>
                        <span class="filename"><a href="/admin.php?page=tor/index.php&files=1&path='.$path.'/..">..</a></span><span class="mode"><span class="owner"></span><span class="size"></span><span class="date"></span><span class="time"></time>
                          </li>';
                }

                foreach($listing['dirs'] as $dir){
                    echo '<li class="dir">
                    <span class="marker"></span><span class="filename"><a href="/admin.php?page=tor/index.php&files=1&path='.$path.'/'.$dir['name'].'">'.$dir['name'].'</a></span>
                    <span class="time">'.$dir['atime_human'].'</span>
                    <span class="date">'.$dir['adate_human'].'</span>
                    <span class="size-unit">&nbsp;</span>
                    <span class="size">&nbsp;</span>
                    <span class="owner">'.$dir['owner'].'/'.$dir['group'].'</span>
                    <span class="mode m-775">'.$dir['mode']['owner'].''.$dir['mode']['group'].''.$dir['mode']['other'].'</span>
                        </li>';
                } 

                foreach($listing['files'] as $item){
                    echo '<li class="dir">
                    <span class="marker"></span><span class="filename">'.$item['name'].'</span>
                    <span class="time">'.$item['atime_human'].'</span>
                    <span class="date">'.$item['adate_human'].'</span>
                    <span class="size-unit">&nbsp;</span>
                    <span class="size">'.$item['size'].'</span>
                    <span class="owner">'.$item['owner'].'/'.$item['group'].'</span>
                    <span class="mode m-775">'.$item['mode']['owner'].''.$item['mode']['group'].''.$item['mode']['other'].'</span>
                        </li>';
                }*/ ?>


            </ul>
        </div>


        <div class="window">
            <div class="pwd pwd-tab-B">
                <? /* <a>var</a><a>www</a><a>html</a><a>sites</a><a>public html</a> */ ?>
            </div>
            <div class="menu menu-right menu-B">
                <?php $pre_tab = 'B';include($_SERVER['DOCUMENT_ROOT'].'/templates/file_manager/tab_menu.php'); ?>
            </div>


            <ul class="listing listing-right">

            </ul>
        </div>
        </div>


    <!-- div class="popups">

    <ul class="context-menu">
        <li class="download">download</li>
        <li class="">rename</li>
        <li class="">chmod</li>
        <li class="">chown</li>
        <li class="">copy</li>
        <li class="">cut</li>
        <li class="disabled">paste</li>
        <li class="">archive</li>
        <li class="delete">delete</li>
    </ul>


    <div class="confirm-box replace">
        <div class="message">File <span class="title">"reading.txt"</span> already exists</div>
        <div class="action-name"><label><span class="checkbox"></span><span>apply to next <span class="number">27</span> conflicts</span></label></div>
        <div class="controls">
        <p class="cancel">cancel</p>
            <p class="keep-original">keep original</p>
            <p class="ok">replace</p>
        </div>
    </div>


<br><br>

    <div class="confirm-box delete">
        <div class="message">Are you sure you want to delete file <span class="title">"reading.txt"</span>?</div>
            <div class="controls">
        <p class="cancel">cancel</p>
        <p class="ok">delete</p>
        </div>
    </div>


<br><br>


    <div class="confirm-box rename warning">
        <div class="message">Rename file <span class="title">"reading.txt"</span></div>
        <div class="warning">File <span class="title">"reading.txt"</span> already exists</div>
        <div class="actions">
            <input type="text" class="new-title" />
        </div>
        <div class="controls">
            <p class="cancel">cancel</p>
        <p class="ok">rename</p>
        </div>

        <div class="controls replace">
            <p class="cancel">cancel</p>
            <p class="ok">replace</p>
        </div>
    </div>

<br><br>


    <div class="confirm-box archive warnin">
        <div class="message">Create archive</div>
        <div class="warning">File <span class="title">"reading.tar.gz"</span> already exists</div>
        <div class="actions">
            <span class="title">archive name</span><br>
            <input type="text" class="new-title" />
            <br>

            <span class="title">archive type</span><br>
            <select>
                <option value="tar">tar</option>
                <option value="zip">zip</option>
                <option value="rar">rar</option>
            </select>
            <br>

            <span class="title">compression level</span><br>
            <select>
                <option value="0">0</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
            </select>
        </div>
        <div class="controls">
            <p class="cancel">cancel</p>
            <p class="ok ripple ripple-radial">create</p>
        </div>

        <div class="controls replace">
            <p class="cancel">cancel</p>
        <p class="ok">replace</p>
        </div>
    </div>


<br><br>


    <div class="confirm-box owner-mode warnin-g">
        <div class="message">Owner - Mode</div>
        <div class="warning">You have no rights to change owner, group or mode</div>
        <div class="actions">
            <div class="owner-group">
                <div class="owner col">
                <span class="title">owner</span><br>
                <select name="owner">
                    <option value="root">root</option>
                    <option value="bob">Bob</option>
                        <option value="ralph">Ralph</option>
                    </select>
                </div>

                <div class="group col">
                <span class="title">group</span><br>
                <select name="group">
                    <option value="root">root</option>
                    <option value="www">www</option>
                        <option value="apache">apache</option>
                    </select>
                </div>
        </div>

            <div class="mode">
                <div class="col owner">
                <span class="title">owner</span><br>
                <label><span class="title">read</span> <input type="checkbox" name="owner-read" /></label><br />
                <label><span class="title">write</span> <input type="checkbox" name="owner-write" /></label><br />
                <label><span class="title">execute</span> <input type="checkbox" name="owner-execute" /></label><br />
            </div>
                <div class="col group">
                <span class="title">group</span><br>
                <label><span class="title">read</span> <input type="checkbox" name="group-read" /></label><br />
                <label><span class="title">write</span> <input type="checkbox" name="group-write" /></label><br />
                <label><span class="title">execute</span> <input type="checkbox" name="group-execute" /></label><br />
            </div>
                <div class="col other">
                <span class="title">other</span><br>
                <label><span class="title">read</span> <input type="checkbox" name="other-read" /></label><br />
                <label><span class="title">write</span> <input type="checkbox" name="other-write" /></label><br />
                <label><span class="title">execute</span> <input type="checkbox" name="other-execute" /></label><br />
            </div>
            </div>
            <div class="recursive">
                <label><span class="title">recursive</span> <input type="checkbox" name="recursive" /></label>
            </div>
        </div>
        <div class="controls">
            <p class="cancel">cancel</p>
            <p class="ok rippler rippler-default">set</p>
        </div>
    </div>

    </div--> <!-- popups -->


    <div class="warning-box inform hidden">
        <div class="close ripple"></div>
        <div class="message">Please Read the reading text at the reading write!</div>
        <div class="message-small">writing the reading text at the reading write writing the reading text at the reading write!</div>
    </div>


    <div class="warning-box hidden">
        <div class="close ripple"></div>
        <div class="message">Please Read the reading text at the reading write!</div>
        <div class="message-small">writing the reading text at the reading write writing the reading text at the reading write!</div>
    </div>


    <ul class="context-menu tab-a sort-order hidden">
        <li entity="type"><span class="type active">type</span><span class="up">&nbsp;</span></li>
        <li entity="size"><span class="size">size</span><span class="up">&nbsp;</span></li>
        <li entity="date"><span class="date">date</span><span class="up">&nbsp;</span></li>
        <li entity="name" class="last"><span class="name">name</span><span class="up">&nbsp;</span></li>
    </ul>

    <ul class="context-menu tab-b sort-order hidden">
        <li entity="type"><span class="type active">type</span><span class="up">&nbsp;</span></li>
        <li entity="size"><span class="size">size</span><span class="up">&nbsp;</span></li>
        <li entity="date"><span class="date">date</span><span class="up">&nbsp;</span></li>
        <li entity="name" class="last"><span class="name">name</span><span class="up">&nbsp;</span></li>
    </ul>



            <div class="fotorama" data-auto="false"></div>
            <div class="progress-container hidden">
            <div class="progress-elm"><span class="title">Initializing</span><span class="progress" style="backround-position: -96px;  backround-position:-10px"></span><span class="close hidden"></span></div>
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
        <script src="/js/jquery.iframe-transport.js"></script>
        <script src="/js/jquery.fileupload.js"></script>
        <script src="/js/jquery.arcticmodal.js"></script>
        
        <?php if (!empty($GLOBAL_JS)): ?>
            <?php echo $GLOBAL_JS; ?>
        <?php endif; ?>

        <script type="text/javascript" src="/js/file_manager.js"></script>
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
                        /*if (show_msg) {
                            clearTimeout(window.ht_fd);
                            var info = $('.warning-box.inform').clone(true);
                            $(info).attr('id', 'file-upload-msg');
                            $(info).find('.message').text('Bla bla bla');
                            $(info).find('.message-small').html(acc);
                            $(info).find('.close').bind('click', function() {
                                $('#file-upload-msg').remove();
                            });
                            
                            $('body').append($(info).removeClass('hidden'));
                        
                            window.ht_fd = setTimeout(function() {
                                $('#file-upload-msg').fadeOut();
                            }, 3000);
                        }*/
                        
                        var tab = FM.getTabLetter(FM.CURRENT_TAB);
                        var box = FM['TAB_' + tab];
                        FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
                        
                        //$('.file-upload-button-' + tab).removeClass('progress');
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
                    $('.menu-'+tab+' .extract-btn').show();
                }
                else {
                    $('.menu-'+tab+' .extract-btn').hide();
                }
            }
            
            
            $(".listing-left").selectable({
                selected: function (event, ui) {
                    FM.setTabActive(FM.TAB_A, 'skip_highlights');
                    $(".listing-left .selected").each(function(i, o) {
                        if (!$(o).hasClass('ui-selected')) {
                            $(o).removeClass('selected');
                        }
                    });
                    $(ui.selected).addClass('selected');
                    checkIfArchive(ui.selected);
                    $(".listing-left .ui-selected").addClass('selected');
                },
                unselected: function (event, ui) {
                    $(".listing-left .selected").each(function(i, o) {
                        if (!$(o).hasClass('ui-selected')) {
                            $(o).removeClass('selected');
                        }
                    });
                    FM.setTabActive(FM.TAB_A, 'skip_highlights');
                    $(ui.unselected).removeClass('selected');
                }
            });
            $(".listing-right").selectable({
                selected: function (event, ui) {
                    $(".listing-left .selected").each(function(i, o) {
                        if (!$(o).hasClass('ui-selected')) {
                            $(o).removeClass('selected');
                        }
                    });
                    FM.setTabActive(FM.TAB_B, 'skip_highlights');
                    $(ui.selected).addClass('selected');
                    checkIfArchive(ui.selected);
                    $(".listing-left .ui-selected").addClass('selected');
                },
                unselected: function (event, ui) {
                    $(".listing-left .selected").each(function(i, o) {
                        if (!$(o).hasClass('ui-selected')) {
                            $(o).removeClass('selected');
                        }
                    });
                    FM.setTabActive(FM.TAB_B, 'skip_highlights');
                    $(ui.unselected).removeClass('selected');
                }
            });
           
            
        });
        </script>
</body>
</html>

