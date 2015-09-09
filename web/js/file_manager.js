var FM = {};

FM.Env = {};

GLOBAL.ajax_url = '/file_manager/fm_api.php';

FM.DIR_MARKER = '&nbsp;&nbsp;/';

FM.errorMessageTimeout = 3500;

FM.CURRENT_TAB = '';
FM.CURRENT_A_LINE = -1;
FM.CURRENT_B_LINE = -1;

FM.BG_TAB = '';
FM.BG_LINE = 0;

FM.ROOT_DIR = '';

FM.TAB_A = '.listing-left';
FM.TAB_B = '.listing-right';

FM.TAB_A_MENU = '.menu-left';
FM.TAB_B_MENU = '.menu-right';

FM.ORDER_BOX_A = $('.context-menu.sort-order.tab-a');
FM.ORDER_BOX_B = $('.context-menu.sort-order.tab-b');

FM.ORDER_TAB_A = 'type_asc';
FM.ORDER_TAB_B = 'type_asc';

FM.TAB_A_CURRENT_PATH = GLOBAL.TAB_A__PATH;
FM.TAB_B_CURRENT_PATH = GLOBAL.TAB_B_PATH;

FM.IMAGES = {'A':[], 'B': []};

FM.IMG_FILETYPES = 'png, jpg, jpeg, gif';

FM.SUPPORTED_ARCHIEVES = [
    'zip', 'tgz', 'tar.gz',
    'gzip', 'tbz', 'tar.bz',
    'gz', 'zip', 'tar', 'rar'
];

FM.EDITABLE_FILETYPES = [
    'txt', 'php', 'js', 'html'
];

FM.preselectedItems = {'A': [], 'B': []};


FM.directoryNotAvailable = function(reply) {
    alert('Directory not available'); // todo: translate
}

FM.showError = function(type, message) {
    if (FM.isPopupOpened()) {
        var ref = FM.getPopupReference();
        if (ref.find('.warning-message').length > 0) {
            ref.find('.warning-message').html('<p class="msg-item">' + message + '</p>');
            ref.find('.warning-message').show();
            
            clearInterval(FM.Env.errorMessageHideTimeout);
            FM.Env.errorMessageHideTimeout =
            setTimeout(function() {
                ref.find('.warning-message').fadeOut();
            }, FM.errorMessageTimeout);
            return;
        }
    }
    else {
        FM.popupClose();
        var tpl = Tpl.get('popup_alert', 'FM');
        tpl.set(':TEXT', message);
        
        FM.popupOpen(tpl.finalize());
    }
}

FM.formatPath = function(dir) {
    var correct_path = '';
    if (dir.substr(0, GLOBAL.ROOT_DIR.length) == GLOBAL.ROOT_DIR) {
        correct_path = dir;
    }
    else {
        correct_path = GLOBAL.ROOT_DIR + '/' + dir;
    }
    
    correct_path = correct_path.replace(/\/(\/+)/g, '/');
    
    return correct_path;
}

FM.init = function() {
    FM.setTabActive(FM.TAB_A);
    FM.ROOT_DIR = 'undefined' == typeof GLOBAL.ROOT_DIR ? '' : GLOBAL.ROOT_DIR;
    
    var dir_A = 'undefined' == typeof GLOBAL.START_DIR_A ? '' : GLOBAL.START_DIR_A;
    var dir_B = 'undefined' == typeof GLOBAL.START_DIR_B ? '' : GLOBAL.START_DIR_B;
    
    FM.TAB_A_CURRENT_PATH = FM.formatPath(GLOBAL.START_DIR_A);
    FM.TAB_B_CURRENT_PATH = FM.formatPath(GLOBAL.START_DIR_B);
    
    FM.open(dir_A, FM.TAB_A, function() {
        var tab = FM.getTabLetter(FM.CURRENT_TAB);
        if (FM['CURRENT_' + tab + '_LINE'] == -1) {
           FM.setActive(0, FM.CURRENT_TAB);
        }
    });
    FM.open(dir_B, FM.TAB_B, function() {
        var tab = FM.getTabLetter(FM.CURRENT_TAB);
        if (FM['CURRENT_' + tab + '_LINE'] == -1) {
           FM.setActive(0, FM.CURRENT_TAB);
        }
    });
    
    
}

FM.setActive = function(index, box) {
    var tab = FM.getTabLetter(box);
    $(box + ' .selected').removeClass('selected');
    $(box).find('li.dir:eq('+index+')').addClass('selected');
    //$(box).find('li:eq('+index+')').addClass('selected');
    //var w_h = $(window).height() - 100;
    var w_offset = $(box).scrollTop();
    var w_height = $(box).height()
    var pos = $(box).find('li.selected').position();
    //console.log(w_height);
    //console.log(w_offset);
    //console.log(pos);
    var wwh = w_height - w_offset + pos.top;
    //console.info(wwh);
    //console.info((pos.top + w_offset) + ' > ' + w_height);
    //console.log((pos.top + w_offset) > w_height);
/*    if (pos.top > w_height) {
        var cur_elm = $(box).find('li.selected').position();
        var cur_elm_height = $(box).find('li.selected').height();
        //$(box).scrollTo(wwh - 350);
        $(box).scrollTo(w_offset + cur_elm.top - w_height/2 + cur_elm_height/2);
        
    }
    else {*/
        var cur_elm = $(box).find('li.selected').position();
        var cur_elm_height = $(box).find('li.selected').height();

        $(box).scrollTo(w_offset + cur_elm.top - w_height/2 + cur_elm_height/2);

    //}

    FM['CURRENT_' + tab + '_LINE'] = index;
    FM.CURRENT_TAB  = box;
    
    $(FM.preselectedItems[tab]).each(function(i, index) {
        $(box).find('.dir:eq(' + index + ')').addClass('selected');
    });
}

FM.setSecondInactive = function(index, box) {
    //$(box + ' .active').removeClass('selected-inactive');
    $(box).find('li:eq('+index+')').addClass('selected-inactive');

    FM.BG_LINE = index;
    FM.BG_TAB  = box;
}

FM.goUp = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var index = FM['CURRENT_' + tab + '_LINE'];
    index -= 1;
    if (index < 0) {
        index = $(FM.CURRENT_TAB).find('li').length - 1;
    }
    
    FM.setActive(index, FM.CURRENT_TAB);
}

FM.goDown = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var index = FM['CURRENT_' + tab + '_LINE'];
    index += 1;
    if (index > ($(FM.CURRENT_TAB).find('li').length - 1)) {
        index = 0;
    }
    
    FM.setActive(index, FM.CURRENT_TAB);
}


FM.open = function(dir, box, callback) {
    var tab = FM.getTabLetter(box);

    FM['TAB_'+tab+'_CURRENT_PATH'] = dir;

    var params = {
        'dir': dir
    };
    App.Ajax.request('cd', params, function(reply) {
        FM.preselectedItems[tab] = [];
        if (reply.result == true) {
            var html = FM.generate_listing(reply.listing, box);
        }
        else {
            FM.directoryNotAvailable(reply);
        }

        callback && callback(reply);
        
        var current_pwd = dir.trim() == '' ? FM.ROOT_DIR : dir;
    
        FM.updateTopLevelPathBar(box, tab, current_pwd);
        
        
        var path_a = FM['TAB_A_CURRENT_PATH'] == '' ? FM.ROOT_DIR : FM['TAB_A_CURRENT_PATH'];
        var path_b = FM['TAB_B_CURRENT_PATH'] == '' ? FM.ROOT_DIR : FM['TAB_B_CURRENT_PATH'];
        var url = '/list/directory/?dir_a='+path_a+'&dir_b='+path_b;
        history.pushState({}, null, url);
        
    });
}

FM.updateTopLevelPathBar = function(box, tab, path) {
    console.log(path);
    var formattedPath = [];
    path = path.replace(FM.ROOT_DIR, '');
    formattedPath.push('<a href="javascript:void(0)" onClick="FM.open(\''+FM.ROOT_DIR+'\', \''+box+'\')">'+FM.ROOT_DIR+'</span>');
    
    $.each(path.split('/'), function(i, part) {
        if (part.trim() == '') {
            return;
        }
        formattedPath.push('<a href="javascript:void(0)" onClick="FM.open(\''+part+'\', \''+box+'\')">'+part+'</span>');
    });
    
    $('.pwd-tab-' + tab).html(formattedPath.join('/'));
}

FM.isItemFile = function(item) {
    return item.type == 'f';
}

FM.isItemDir = function(item) {
    return item.type == 'd';
}

FM.getFileType = function(name) {
    var filetype = name.split('.').pop().toLowerCase();
    return filetype.length > 6 || name.indexOf('.') <= 0 ? '' : filetype;
}

FM.sortItems = function(items, box) {
    var sorted = [];

    var files    = [];
    var dirs     = [];
    var combined = []

    $.each(items, function(i, o) {
        if (i > 0) { // i == 0 means first .. element in list
            if (FM.isItemFile(o)) {
                files.push(o);
            }
            else {
                dirs.push(o);
            }
        }
    });

    //    var sort_type = $(box).parents('.window').find('.menu').find('.sort-by-v').val();
    var sort_type = FM.ORDER_TAB_A;
    if($(box).closest('.window').find('.menu').hasClass('menu-right')){
      sort_type = FM.ORDER_TAB_B;
    }

    switch (sort_type) {
        case 'type_asc':
            files.sort(function (a, b) {
                return a.name.localeCompare( b.name );
            });
            dirs.sort(function (a, b) {
                return a.name.localeCompare( b.name );
            });
            sorted = $.merge(dirs, files);
            break;
        case 'type_desc':
            files.sort(function (a, b) {
                return a.name.localeCompare( b.name );
            });
            dirs.sort(function (a, b) {
                return a.name.localeCompare( b.name );
            });
            sorted = $.merge(files, dirs);
            break;
        case 'size_asc':
            files.sort(function (a, b) {
                var size_a = parseInt(a.size, 10);
                var size_b = parseInt(b.size, 10);
                return ((size_a < size_b) ? -1 : ((size_a > size_b) ? 1 : 0));
            });

            sorted = $.merge(dirs, files);
            break;
        case 'size_desc':
            files.sort(function (a, b) {
                var size_a = parseInt(a.size, 10);
                var size_b = parseInt(b.size, 10);
                return ((size_a > size_b) ? -1 : ((size_a < size_b) ? 1 : 0));
            });

            sorted = $.merge(dirs, files);
            break;
        case 'date_asc':
            sorted = $.merge(dirs, files);
            sorted.sort(function (a, b) {
                var time_a = a.time.split('.')[0];
                var time_b = b.time.split('.')[0];
                var date_a = Date.parseDate(a.date + ' ' + time_a, 'yy-m-d h:i:s');
                var date_b = Date.parseDate(b.date + ' ' + time_b, 'yy-m-d h:i:s');
                return ((date_a < date_b) ? -1 : ((date_a > date_b) ? 1 : 0));
            });

            break;
        case 'date_desc':
            sorted = $.merge(dirs, files);
            sorted.sort(function (a, b) {
                var time_a = a.time.split('.')[0];
                var time_b = b.time.split('.')[0];
                var date_a = Date.parseDate(a.date + ' ' + time_a, 'yy-m-d h:i:s');
                var date_b = Date.parseDate(b.date + ' ' + time_b, 'yy-m-d h:i:s');
                return ((date_a > date_b) ? -1 : ((date_a < date_b) ? 1 : 0));
            });

            break;

        case 'name_asc':
            sorted = $.merge(dirs, files);
            sorted.sort(function (a, b) {
                return a.name.localeCompare(b.name);
            });

            break;
        case 'name_desc':
            sorted = $.merge(dirs, files);
            sorted.sort(function (a, b) {
                return a.name.localeCompare(b.name);
            });

            sorted = sorted.reverse();
            break;
        default:
            files.sort(function (a, b) {
                return a.name.localeCompare( b.name );
            });
            dirs.sort(function (a, b) {
                return a.name.localeCompare( b.name );
            });
            sorted = $.merge(dirs, files);
            break;
    }


    sorted = $.merge([items[0]], sorted);

    return sorted;
}

FM.isFileEditable = function(src) {
    if ('undefined' == typeof src.filetype) {
        return false;
    }
    
    if ($.inArray(src.filetype, FM.EDITABLE_FILETYPES) != -1) {
        return true;
    }
    
    return false;
}

FM.editFileFromSubcontext = function(elm) {
    var elm = $(elm).hasClass('dir') ? $(elm) : $(elm).closest('.dir');
    var src = $.parseJSON($(elm).find('.source').val());

    var myWindow = window.open('/edit/file/?path=' + src.full_path, '_blank');//, src.full_path, "width=900, height=700");
}

FM.downloadFileFromSubcontext = function(elm) {
    var elm = $(elm).hasClass('dir') ? $(elm) : $(elm).closest('.dir');
    var src = $.parseJSON($(elm).find('.source').val());

    var path = src.full_path;
    var win = window.open('/download/file/?path=' + path, '_blank');
    win.focus();
}

FM.openFile = function(dir, box, elm) {
    var tab = FM.getTabLetter(box);

    FM['TAB_'+tab+'_CURRENT_PATH'] = dir;
    
    
    
    var elm = $(elm).hasClass('dir') ? $(elm) : $(elm).closest('.dir');
    var src = $.parseJSON($(elm).find('.source').val());
    console.log(elm);
    if (FM.isItemPseudo(src)) {
        FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], FM['TAB_' + tab]);
    }
    
    if (FM.isFileEditable(src)) {
        var myWindow = window.open('/edit/file/?path=' + src.full_path, '_blank');//, src.full_path, "width=900, height=700");
    }
    else {
        var path = src.full_path;
        var win = window.open('/download/file/?path=' + path, '_blank');
        win.focus();
    }
    
    
}

FM.getTabLetter = function(box) {
    var tab = 'A';
    if (box == FM.TAB_B) {
        tab = 'B';
    }
    
    return tab;
}

FM.toggleSubContextMenu = function(ref) {
    $(ref).find('.subcontext-menu').toggleClass('subcontext-menu-hidden');
}

FM.generate_listing = function(reply, box) {
    var tab = FM.getTabLetter(box);
    FM.IMAGES[tab] = [];
    
    var acc = [];
    if (reply.length == 0) {
        reply = [{
            type: 'd',
            name: '',
            permissions: '',
            owner: '',
            size: '',
            time: '',
            date: ''
        }];
    }
    
    var path_arr = FM['TAB_'+tab+'_CURRENT_PATH'].split('/');
    path_arr = path_arr.filter(function(v){return v!==''});
    path_arr.pop();
    var back_path = '/' + path_arr.join('/');
    if (back_path == FM.ROOT_DIR || path_arr.length < FM.ROOT_DIR.split('/').length) {
        back_path = '';//FM.ROOT_DIR;
    }
    
    reply = FM.sortItems(reply, box);

    $(reply).each(function(i, o) {
        var path = FM.formatPath(FM['TAB_'+tab+'_CURRENT_PATH']+'/'+o.name);
        var cl_act = o.type == 'd' ? 'onClick="FM.open(\'' + path + '\', \'' + box + '\')"' : 'onClick="FM.openFile(\''+path+'\', \'' + box + '\', this)"';
        //var cl_act = o.type == 'd' ? 'onDblClick="FM.open(\'' + path + '\', \'' + box + '\')"' : 'onDblClick="FM.openFile(\''+path+'\', \'' + box + '\', this)"';
        //var cl_act = '';
        
        if (o.name == '') {
            path = FM.formatPath(back_path);
            cl_act = o.type == 'd' ? 'onClick="FM.open(\'' + path + '\', \'' + box + '\')"' : 'onClick="FM.openFile(\''+path+'\', \'' + box + '\', this)"';
            o = {
                type: 'd',
                name: '..',
                permissions: '',
                owner: '',
                size: '',
                time: '',
                date: ''
            }
        }

        var time = o.time.split('.');
        time = time[0];
        
        var psDate = new Date(o.date);

        o.full_path = path;

        o.filetype = FM.getFileType(o.name);
        if(FM.IMG_FILETYPES.indexOf(o.filetype) >= 0 && o.filetype.length > 0) {
            FM.IMAGES[tab][FM.IMAGES[tab].length] = {'img': "/view/file/?path=/home/admin/"+o.name+"&raw=true", 'thumb': "/view/file/?path=/home/admin/"+o.name+"&raw=true", 'id': 'img-'+i};
            cl_act = 'onClick="FM.fotoramaOpen(\'' + tab + '\', \'img-' + i +'\')"';
        }

        var t_index = tab + '_' + i;

        var tpl = Tpl.get('entry_line', 'FM');
        tpl.set(':CL_ACTION_1', cl_act);
        tpl.set(':SOURCE', $.toJSON(o));
        tpl.set(':NAME', o.name);
        tpl.set(':PERMISSIONS', o.permissions);
        tpl.set(':OWNER', o.owner);
        tpl.set(':SIZE_VALUE', o.type == 'f' ? FM.humanFileSizeValue(o.size) : '&nbsp;');
        tpl.set(':SIZE_UNIT', o.type == 'f' ? FM.humanFileSizeUnit(o.size) : '&nbsp;');
        tpl.set(':TIME', (psDate.getFullYear() != new Date().getFullYear()) ? psDate.getFullYear() || "" : time);
        tpl.set(':DATE', o.date.trim() != '' ? psDate.format('mmm d') : '&nbsp;'/*o.date*/);
        
        
        if (o.name == '..' || o.type == 'd') {
            tpl.set(':SUBMENU_CLASS', 'hidden');
        }
        else {
            tpl.set(':SUBMENU_CLASS', '');
        }
        /*tpl.set(':index', t_index);
        tpl.set(':index1', t_index);
        tpl.set(':index2', t_index);*/

        if (FM.isItemDir(o)) {
            tpl.set(':ITEM_TYPE', 'filetype-dir');
        }
        else {
            tpl.set(':ITEM_TYPE', 'filetype-' + o.filetype);
        }
        
        acc.push(tpl.finalize());
    });

    $(box).html(acc.done());
    
    FM['CURRENT_'+tab+'_LINE'] = -1;
}

FM.toggleCheck = function(uid) {
    var ref = $('#check' + uid);
    if (ref.length > 0) {
        //ref.attr('checked', true);
        $(ref).hasClass('checkbox-selected') ? $(ref).addClass('checkbox-selected') : $(ref).removeClass('checkbox-selected');
    }
}

FM.fotoramaOpen = function(tab, img_index) {
    console.log('index: ' + img_index);
    
    $('.fotorama').fotorama({
        nav: 'thumbs',
        arrows: true,
        click: true,
        allowfullscreen: true,
        fit: 'scaledown',
        thumbfit: 'scaledown',
        data: FM.IMAGES[tab]
    });

    $('.fotorama').on('fotorama:fullscreenexit', function (e, fotorama) {
	$('.fotorama').data('fotorama').destroy();
    });

    $('.fotorama').fotorama().data('fotorama').requestFullScreen();
    $('.fotorama').fotorama().data('fotorama').show(img_index);
}

FM.bulkOperation = function(ref) {
    //console.log(ref);
    var box = $(ref).parents('.menu').hasClass('menu-left') ? FM.setTabActive(FM.TAB_A, 'skip_highlights') : FM.setTabActive(FM.TAB_B, 'skip_highlights');
    
    var operation = $(ref).val();
    if ('function' == typeof FM[operation]) {
        FM[operation] && FM[operation](ref);
    }
    
    $(ref).find('option[value=-1]').attr('selected', true);
}

FM.checkBulkStatus = function(bulkStatuses, acc) {
    var status = false;
    var msg    = '';
    if (bulkStatuses.length == acc.length) {
        $.each(bulkStatuses, function(i, o) {
            if (o != true) {
                msg += '<p>'+o+'</p>';
            }
        });

        if (msg == '') {
            status = true;
        }
    }

    if (status == true) {
        $('#popup .results').html(msg);
        $('.controls p').replaceWith('<p class="ok" onClick="FM.bulkPopupClose();">close</p>');
    }
    else {
        $('#popup .results').html(msg);
        $('.controls p').replaceWith('<p class="ok" onClick="FM.bulkPopupClose();">close</p>');
    }
}

FM.bulkPopupClose = function() {
    FM.popupClose();
    FM.open(FM['TAB_A_CURRENT_PATH'], FM['TAB_A']);
    FM.open(FM['TAB_B_CURRENT_PATH'], FM['TAB_B']);
}

FM.humanFileSize = function(size) {
    var sizes = [' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB'];
    for (var i = 1; i < sizes.length; i++) {
        if (size < Math.pow(1024, i)) return (Math.round((size/Math.pow(1024, i-1))*100)/100) + sizes[i-1];
    }
    return size;
}

FM.humanFileSizeValue = function(size) {
    var sizes = ['b', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    for (var i = 1; i < sizes.length; i++) {
        if (size < Math.pow(1024, i)) return (Math.round((size/Math.pow(1024, i-1))*100)/100);
    }
    return size;
}

FM.humanFileSizeUnit = function(size) {
    if (!parseInt(size)) return "&nbsp;";

    var sizes = ['b', ' kb', ' Mb', ' Gb', ' Tb', ' Pb', ' Eb', ' Zb', ' Yb'];
    for (var i = 1; i < sizes.length; i++) {
        if (size < Math.pow(1024, i)) return sizes[i-1];
    }
    return sizes[i];
}


FM.bulkCopy = function() {
    var acc = $(FM.CURRENT_TAB).find('.dir.selected');
    if (acc.length > 0) {
        //FM.popupClose();
        
        var cfr_html = '';
        
        $.each(acc, function(i, o) {
            var ref = $(o).parents('.dir');
            var src = $(ref).find('.source').val();
            src = $.parseJSON(src);
          
            if (!FM.isItemPseudo(o)) {
                cfr_html += '<div>'+src.name+'</div>';
            }
        });
        
        var tpl = Tpl.get('popup_bulk', 'FM');
        tpl.set(':ACTION', 'YOU ARE COPYING');
        tpl.set(':TEXT',   cfr_html);
       
        FM.popupOpen(tpl.finalize());
        
        var bulkStatuses = [];
        $.each(acc, function(i, o) {
            var ref = $(o).parents('.dir');
            var src = $(ref).find('.source').val();
            src = $.parseJSON(src);
          
            /*if (!FM.isItemPseudo(o)) {
                cfr_html += '<div>'+src.name+'</div>';
            }*/
            var tab = FM.getTabLetter(FM.CURRENT_TAB);

            var opposite_tab = 'A';
            if (tab == 'A') {
                opposite_tab = 'B';
            }

            if (FM.isItemPseudo(src)) {
                return FM.displayError(
                    App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
                );
            }
            
            var dest = FM['TAB_' + opposite_tab + '_CURRENT_PATH' ];
            if (dest == '') {
                dest = GLOBAL.ROOT_DIR;
            }
            
            var action = FM.isItemFile(src) ? 'copy_file' : 'copy_directory';
            
            var params = {
                item: src.full_path,
                filename: src.name,
                dir:  FM['TAB_' + tab + '_CURRENT_PATH'],
                dir_target: dest
            };
            
            App.Ajax.request(action, params, function(reply) {
                if (reply.result == true) {
                    bulkStatuses.push(true);
                }
                else {
                    //FM.showError('copy-items', reply.message);
                    bulkStatuses.push(reply.message);
                }
                
                FM.checkBulkStatus(bulkStatuses, acc);
            });
        });

    }
}

FM.bulkRemove = function() {
    var acc = $(FM.CURRENT_TAB).find('.dir.selected');
    if (acc.length > 0) {
        //FM.popupClose();
        
        var cfr_html = '';
        
        $.each(acc, function(i, o) {
            var ref = $(o).parents('.dir');
            var src = $(ref).find('.source').val();
            src = $.parseJSON(src);
          
            if (!FM.isItemPseudo(o)) {
                cfr_html += '<div>'+src.name+'</div>';
            }
        });
        
        var tpl = Tpl.get('popup_bulk', 'FM');
        tpl.set(':ACTION', 'YOU ARE REMOVING');
        tpl.set(':TEXT',   cfr_html);
       
        FM.popupOpen(tpl.finalize());
        
        var bulkStatuses = [];
        $.each(acc, function(i, o) {
            var ref = $(o).parents('.dir');
            var src = $(ref).find('.source').val();
            src = $.parseJSON(src);
          
            /*if (!FM.isItemPseudo(o)) {
                cfr_html += '<div>'+src.name+'</div>';
            }*/
            var tab = FM.getTabLetter(FM.CURRENT_TAB);

            var opposite_tab = 'A';
            if (tab == 'A') {
                opposite_tab = 'B';
            }

            if (FM.isItemPseudo(src)) {
                return FM.displayError(
                    App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
                );
            }
            
            var dest = FM['TAB_' + opposite_tab + '_CURRENT_PATH' ];
            if (dest == '') {
                dest = GLOBAL.ROOT_DIR;
            }
            
            var params = {
                item: src.full_path,
                dir:  FM['TAB_' + tab + '_CURRENT_PATH']
            };
            
            App.Ajax.request('delete_files', params, function(reply) {
                if (reply.result == true) {
                    bulkStatuses.push(true);
                }
                else {
                    //FM.showError('copy-items', reply.message);
                    bulkStatuses.push(reply.message);
                }
                
                FM.checkBulkStatus(bulkStatuses, acc);
            });
        });

    }
}

FM.toggleAllItemsSelected = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    var selected  = $(FM['TAB_' + tab] ).find('.dir.selected');
    var dir_items = $(FM['TAB_' + tab] ).find('.dir');
    if (selected.length == dir_items.length) {
        $(box).find('.dir').removeClass('selected');
        var index = FM['CURRENT_' + tab + '_LINE'];
        $(box).find('.dir:eq(' + index + ')').addClass('selected');
    }
    else {
        $(box).find('.dir').addClass('selected');
    }
}

FM.selectCurrentElementAndGoToNext = function () {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    
    var index = FM['CURRENT_' + tab + '_LINE'];
    
    if ($.inArray(index, FM.preselectedItems[tab]) != -1) {
        FM.preselectedItems[tab] = $.grep(FM.preselectedItems[tab], function(i) {
            return i != index;
        });

        $(box).find('.dir:eq(' + index + ')').removeClass('selected');
        //FM.preselectedItems[tab].push(index);
    }
    else {
        $(box).find('.dir:eq(' + index + ')').addClass('selected');
        FM.preselectedItems[tab].push(index);
    }

    FM.goDown();
}

FM.selectItem = function(item, box) {
    //console.log(item);
    /*if ($(item).hasClass('ch-toggle')) {
        if ($(item).parents('.dir').prev('.dir').length == 0) {
            var checked = $(item).parents('.dir').find('.ch-toggle').is(':checked');
            $(item).parents('.listing').find('.ch-toggle').prop('checked', checked);
        }
        
        return;
    }*/
   
    
    
    if (FM.CURRENT_TAB == FM.TAB_A) {
        FM.setTabActive(FM.TAB_B, 'skip_highlights');
        $(FM.TAB_B).find('.selected-inactive').removeClass('selected-inactive');
        // tmp
        //$(FM.TAB_A).find('.selected-inactive').removeClass('selected-inactive');
        $(FM.TAB_B).find('.selected').removeClass('selected');
        
        //$(FM.TAB_A).find('.selected').addClass('selected-inactive');
        $(FM.TAB_B).find('.selected').addClass('selected-inactive').removeClass('selected');
    }
    else {
        FM.setTabActive(FM.TAB_A, 'skip_highlights');
        $(FM.TAB_A).find('.selected-inactive').removeClass('selected-inactive');
        //$(FM.TAB_B).find('.selected-inactive').removeClass('selected-inactive');
        //$(FM.TAB_B).find('.selected').addClass('selected-inactive');
        $(FM.TAB_A).find('.selected').removeClass('selected');
        
        $(FM.TAB_A).find('.selected').addClass('selected-inactive').removeClass('selected');
    }
    
    $(box).find('.active').removeClass('active');
    $(box).find('.selected').removeClass('selected');
    
    //
    // tmp 
    //$(FM.TAB_A).find('.selected-inactive').removeClass('selected-inactive');
    //$(FM.TAB_B).find('.selected-inactive').removeClass('selected-inactive');
    
    /*if ($(item).hasClass('active')) {
        $(item).removeClass('active');
    }
    else {
        $(item).addClass('active');
    }*/
    if ($(item).hasClass('selected')) {
        $(item).removeClass('selected');
    }
    else {
        $(item).addClass('selected');
    }
    
    

    FM.setTabActive(box);
    
    
    
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
FM.isItemPseudo = function(item) {
    if (item.name == '.' || item.name == '..') {
        return true;
    }
    return false;
}

FM.itemIsArchieve = function(item) {
    
    if ($.inArray(item.filetype, FM.SUPPORTED_ARCHIEVES) != -1) {
        return true;
    }
    
    return false;
}

FM.unpackItem = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (selected.length == 0) {
        return alert('No file selected');
    }
    

    var src = selected.find('.source').val();
    src = $.parseJSON(src);

    if (FM.isItemPseudo(src)) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_SELECTED
        );
    }

    if (!FM.itemIsArchieve(src)) {
        return FM.displayError(
            App.Constants.FM_FILE_TYPE_NOT_SUPPORTED
        );
    }
    
    var dst = FM['TAB_' + tab + '_CURRENT_PATH'];
    if (dst == '') {
        dst = GLOBAL.ROOT_DIR;
    }
    
    var tpl = Tpl.get('popup_unpack', 'FM');
    tpl.set(':FILENAME', src.name);
    tpl.set(':DST_DIRNAME', dst + '/' + src.name + '_extracted');
    FM.popupOpen(tpl.finalize());
}

FM.packItem = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (selected.length == 0) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_SELECTED
        );
    }
    

    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    
    if (FM.isItemPseudo(src)) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }
    
    if (FM.isItemPseudo(src)) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }

    
    var dst = FM['TAB_' + tab + '_CURRENT_PATH'];
    if (dst == '') {
        dst = GLOBAL.ROOT_DIR;
    }
    
    var tpl = Tpl.get('popup_pack', 'FM');
    tpl.set(':FILENAME', src.name);
    tpl.set(':DST_DIRNAME', dst + '/' + src.name + '_packed.tar.gz');
    FM.popupOpen(tpl.finalize());
}



FM.switchTab = function() {
    if (FM.CURRENT_TAB == FM.TAB_A) {
        FM.setTabActive(FM.TAB_B);
        $(FM.TAB_B).find('.selected-inactive').removeClass('selected-inactive');
        $(FM.TAB_A).find('.selected').addClass('selected-inactive');
    }
    else {
        FM.setTabActive(FM.TAB_A);
        $(FM.TAB_A).find('.selected-inactive').removeClass('selected-inactive');
        $(FM.TAB_B).find('.selected').addClass('selected-inactive');
    }
    
    
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    if (FM['CURRENT_' + tab + '_LINE'] == -1) {
       FM.setActive(0, FM.CURRENT_TAB);
    }
}

FM.setTabActive = function(box, action) {
    FM.CURRENT_TAB = box;
    $('.window.active').removeClass('active');
    $('.listing-left.active').removeClass('active');
    $('.listing-right.active').removeClass('active');
    $(FM.CURRENT_TAB).addClass('active');
    $(FM.CURRENT_TAB).closest('.window').addClass('active');
    
    if (action == 'skip_highlights') {
        return true;
    }
    
    if (FM.CURRENT_TAB == FM.TAB_A) {
        $(FM.TAB_B).find('.selected').addClass('selected-inactive').removeClass('selected');
        $(FM.TAB_A).find('.selected-inactive').addClass('selected').removeClass('selected-inactive');
    }
    else {
        $(FM.TAB_A).find('.selected').addClass('selected-inactive').removeClass('selected');
        $(FM.TAB_B).find('.selected-inactive').addClass('selected').removeClass('selected-inactive');
    }
}

FM.confirmRename = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (!selected) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    
    var target_name = $('#rename-title').val();
    
    if (target_name.trim().length == 0) {
        return FM.displayError(
            App.Constants.FM_FILE_NAME_CANNOT_BE_EMPTY
        );
    }
    
    var action = FM.isItemFile(src) ? 'rename_file' : 'rename_directory';

    var params = {
        item: src.name,
        target_name: target_name,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH'] + '/'
    };
    
    App.Ajax.request(action, params, function(reply) {
        if (reply.result == true) {
            FM.popupClose();
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
        }
        else {
            FM.showError('rename-items', reply.message);
        }
    });
}

FM.renameItems = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (selected.length == 0) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    
    if (FM.isItemPseudo(src)) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }

    var tpl = Tpl.get('popup_rename', 'FM');
    tpl.set(':FILENAME', src.name);
    tpl.set(':NEW_NAME', src.name);
    FM.popupOpen(tpl.finalize());
}

FM.popupOpen = function(html) {
    $('<div>').attr('id', 'popup').html(html).flayer({
        afterStart: function(elm) {
            elm.find('input[type="text"]:first').focus();
        }
    });
}

FM.popupClose = function() {
    clearTimeout(FM.Env.errorMessageHideTimeout);
    return $('#popup').flayer_close();
}

FM.copyItems = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (selected.length == 0) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }

    if (selected.length > 1) { // multi operation
        return FM.bulkCopy();
    }
    

    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    
    if (FM.isItemPseudo(src)) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }
    
    var opposite_tab = 'A';
    if (tab == 'A') {
        opposite_tab = 'B';
    }
    
    var dst = FM['TAB_' + opposite_tab + '_CURRENT_PATH'];
    if (dst == '') {
        dst = GLOBAL.ROOT_DIR;
    }

    var tpl = Tpl.get('popup_copy', 'FM');
    tpl.set(':SRC_FILENAME', src.full_path);
    tpl.set(':DST_FILENAME', dst + '/' + src.name);
    FM.popupOpen(tpl.finalize());
}

FM.confirmUnpackItem = function () {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (selected.length == 0) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_SELECTED
        );
    }
    
    var opposite_tab = 'A';
    if (tab == 'A') {
        opposite_tab = 'B';
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    
    if (FM.isItemPseudo(src)) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_SELECTED
        );
    }

    if (!FM.itemIsArchieve(src)) {
        return FM.displayError(
            App.Constants.FM_FILE_TYPE_NOT_SUPPORTED
        );
    }
    
    var dst = FM['TAB_' + tab + '_CURRENT_PATH'];
    if (dst == '') {
        dst = GLOBAL.ROOT_DIR;
    }
    
    var params = {
        item: src.full_path,
        filename: src.name,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH'],
        dir_target: $('#unpack-destination').val()
    };

    App.Ajax.request('unpack_item', params, function(reply) {
        if (reply.result == true) {
            FM.popupClose();
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], FM['TAB_' + tab]);
            FM.open(FM['TAB_' + opposite_tab + '_CURRENT_PATH'], FM['TAB_' + opposite_tab]);
        }
        else {
            FM.showError('unpack_item', reply.message);
        }
    });
}

FM.confirmPackItem = function () {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (selected.length == 0) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }
    
    var opposite_tab = 'A';
    if (tab == 'A') {
        opposite_tab = 'B';
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    
    if (FM.isItemPseudo(src)) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }

    var dst = FM['TAB_' + tab + '_CURRENT_PATH'];
    if (dst == '') {
        dst = GLOBAL.ROOT_DIR;
    }
    
    var params = {
        item: src.full_path,
        filename: src.name,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH'],
        dir_target: $('#pack-destination').val()
    };

    
    App.Ajax.request('pack_item', params, function(reply) {
        if (reply.result == true) {
            FM.popupClose();
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], FM['TAB_' + tab]);
            FM.open(FM['TAB_' + opposite_tab + '_CURRENT_PATH'], FM['TAB_' + opposite_tab]);
        }
        else {
            FM.showError('unpack_item', reply.message);
        }
    });
}

FM.confirmCopyItems = function () {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    
    if (!selected) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }
    
    var opposite_tab = 'A';
    if (tab == 'A') {
        opposite_tab = 'B';
    }
    
    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    
    if (FM.isItemPseudo(src)) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }
    
    var dest = $('#copy_dest').val();
    if (dest == '') {
        dest = GLOBAL.ROOT_DIR;
    }
    
    var action = FM.isItemFile(src) ? 'copy_file' : 'copy_directory';
    
    var params = {
        item: src.full_path,
        filename: src.name,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH'],
        dir_target: dest
    };
    
    App.Ajax.request(action, params, function(reply) {
        if (reply.result == true) {
            FM.popupClose();
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], FM['TAB_' + tab]);
            FM.open(FM['TAB_' + opposite_tab + '_CURRENT_PATH'], FM['TAB_' + opposite_tab]);
        }
        else {
            FM.showError('copy-items', reply.message);
        }
    });
}

FM.downloadFiles = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (!selected) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    
    if (FM.isItemPseudo(src) || FM.isItemDir(src)) {
        alert('Folder downloads are in progress atm');
    }
    
    if (FM.isItemPseudo(src)) {
        if (FM.isItemPseudo(src)) {
            return FM.displayError(
                App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
            );
        }
    }

    var path = src.full_path;
    var win = window.open('/download/file/?path=' + path, '_blank');
    win.focus();
}

FM.uploadFile = function() {
    //return alert('Not available atm..');
    //$('<div>123</div>').flayer();
}

FM.confirmDelete = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (!selected) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    
    if (FM.isItemPseudo(src)) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }

    var params = {
        item: src.full_path,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH']
    };
    
    App.Ajax.request('delete_files', params, function(reply) {
        if (reply.result == true) {
            FM.popupClose();
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
        }
        else {
            FM.showError('delete-items', reply.message);
        }
    });
}

FM.deleteItems = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (selected.length == 0) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }

    if (selected.length > 1) { // multi operation
        return FM.bulkRemove();
    }
    
    

    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    
    if (FM.isItemPseudo(src)) {
        return FM.displayError(
            App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED
        );
    }

    var tpl = Tpl.get('popup_delete', 'FM');
    tpl.set(':FILENAME', src.name);
    FM.popupOpen(tpl.finalize());
}


FM.displayError = function(msg) {
    if (FM.isPopupOpened()) {
        var ref = FM.getPopupReference();
        if (ref.find('.warning-message').length > 0) {
            ref.find('.warning-message').html('<p class="msg-item">' + msg + '</p>');
            ref.find('.warning-message').show();
            
            clearInterval(FM.Env.errorMessageHideTimeout);
            FM.Env.errorMessageHideTimeout =
            setTimeout(function() {
                ref.find('.warning-message').fadeOut();
            }, FM.errorMessageTimeout);
            return;
        }
    }
    
    FM.popupClose();
    var tpl = Tpl.get('popup_alert', 'FM');
    tpl.set(':TEXT', msg);
    
    FM.popupOpen(tpl.finalize());
    //return alert(msg);
}

FM.confirmCreateDir = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    
    var dirname = $('#rename-title').val();
    
    if (dirname.trim().length == 0) {
        return FM.displayError(
            App.Constants.FM_DIRECTORY_NAME_CANNOT_BE_EMPTY
        );
    }

    var params = {
        dirname: dirname,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH']
    };
    
    App.Ajax.request('create_dir', params, function(reply) {
        if (reply.result == true) {
            FM.popupClose();
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
        }
        else {
            FM.showError('create-dir', reply.message);
        }
    });
}

FM.createDir = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    
    var tpl = Tpl.get('popup_create_dir', 'FM');
    FM.popupOpen(tpl.finalize());
}

FM.confirmCreateFile = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    
    var filename = $('#rename-title').val();
    
    if (filename.trim().length == 0) {
        return FM.displayError(
            App.Constants.FM_FILE_NAME_CANNOT_BE_EMPTY
        );
    }

    var params = {
        filename: filename,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH']
    };
    
    App.Ajax.request('create_file', params, function(reply) {
        if (reply.result == true) {
            FM.popupClose();
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
        }
        else {
            FM.showError('create-file', reply.message);
        }
    });
}

FM.createFile = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    
    var tpl = Tpl.get('popup_create_file', 'FM');
    FM.popupOpen(tpl.finalize());
}

FM.showOrderBox = function(elm, tab) {
    var primary_box = FM.ORDER_BOX_A;
    var secondary_box = FM.ORDER_BOX_B;

    if(tab == FM.TAB_B){
        primary_box = FM.ORDER_BOX_B;
        secondary_box = FM.ORDER_BOX_A;
    }


    secondary_box.hide();
    
    if(primary_box.is(':visible')){
        primary_box.hide();
        return;
    }
    
    
    var offset = elm.offset();
    offset.top += elm.outerHeight() + 10;
    offset.left += elm.outerWidth() - primary_box.outerWidth();
    primary_box.css({top: offset.top, left: offset.left});

    primary_box.show();
}

FM.reOrderList = function(elm){
    var tab = FM.TAB_A;
    var menu = $(FM.TAB_A_MENU);
    var path = FM.TAB_A_CURRENT_PATH;
    var primary_box = FM.ORDER_BOX_A;
    var tab_order_type = FM.ORDER_TAB_A;
    if(elm.closest('.context-menu').hasClass('tab-b')){
        tab = FM.TAB_B;
        path = FM.TAB_B_CURRENT_PATH;
        primary_box = FM.ORDER_BOX_B;
        menu = FM.TAB_B_MENU;
        tab_order_type = FM.ORDER_TAB_B;
    }

    var entity = elm.closest('li').attr('entity');
    var direction = 'asc';
    if(elm.hasClass('up')){
        direction = 'desc';
    }


    if(tab == FM.TAB_A){
        FM.ORDER_TAB_A = entity+'_'+direction;
    }else{
        FM.ORDER_TAB_B = entity+'_'+direction;
    }
    
    primary_box.find('span').removeClass('active');
    $(menu).find('.sort-by .entity').html(elm.closest('li').find('span').html());
    $(menu).find('.sort-by').removeClass('desc asc').addClass(direction).addClass('sort-by');

    elm.addClass('active');
    primary_box.hide();

    FM.open(path, tab);
}

FM.isPopupOpened = function() {
    var ref = $('#popup');
    if (ref.length > 0) {
        return true;
    }

    return false;
}

FM.getPopupReference = function() {
    var ref = $('#popup');
    
    return ref;
}

FM.handlePopupSubmit = function() {
    try {
        var method = $('#popup').find('.ok').attr('onClick');
        if (method) {
            method = method.replace('\(\);', '').replace('FM.', '');
            if ('function' == typeof FM[method]) {
                FM[method]();
            }
        }
    }
    catch(e) {
        
    }
}

FM.handlePopupCancel = function() {
    FM.popupClose();
}



FM.init();


$(document).ready(function() {
    $('.progress-container').hide();
    
    //return alert('statechange: Back');
    /*$(document).bind('keydown.up', function() {
        console.log(1);
        //try{FM.goUp();}catch(e){console.log(e);}
        //console.log(FM);
        FM.goUp();
    });
    
    $(document).bind('keydown.down', function() {
        console.log(1);
        //try{FM.goUp();}catch(e){console.log(e);}
        //console.log(FM);
        FM.goDown();
    });*/


    var ph = $('.window .pwd').outerHeight();
    var mh = $('.window .menu').outerHeight();
    var wh = $(window).outerHeight();
    var hgt = wh - (ph + mh) - 8;
    $('.window ul').outerHeight(hgt);


    shortcut.add("Esc",function() {
        if (FM.isPopupOpened()) {
            return FM.handlePopupCancel();
        }
    },{
        'type':             'keydown',
        'propagate':        false,
        'disable_in_input': false,
        'target':           document
    });

    shortcut.add("Down",function() {
        FM.goDown();
    },{
        'type':             'keydown',
        'propagate':        false,
        'disable_in_input': false,
        'target':           document
    });
    
    shortcut.add("Up",function() {
        FM.goUp();
    },{
        'type':             'keydown',
        'propagate':        false,
        'disable_in_input': false,
        'target':           document
    });
    
    shortcut.add("Left",function() {
        FM.setTabActive(FM.TAB_A);
    },{
        'type':             'keydown',
        'propagate':        false,
        'disable_in_input': false,
        'target':           document
    });
    
    shortcut.add("Right",function() {
        FM.setTabActive(FM.TAB_B);
    },{
        'type':             'keydown',
        'propagate':        false,
        'disable_in_input': false,
        'target':           document
    });
    
    shortcut.add("Tab",function() {
        FM.switchTab();
    },{
        'type':             'keydown',
        'propagate':        false,
        'disable_in_input': false,
        'target':           document
    });
    
    shortcut.add("Space",function() {
        FM.selectCurrentElementAndGoToNext();
    },{
        'type':             'keydown',
        'propagate':        false,
        'disable_in_input': false,
        'target':           document
    });
    
    shortcut.add("Insert",function() {
        FM.selectCurrentElementAndGoToNext();
    },{
        'type':             'keydown',
        'propagate':        false,
        'disable_in_input': false,
        'target':           document
    });
    
    
    shortcut.add("ctrl+a",function() {
        FM.toggleAllItemsSelected();
    },{
        'type':             'keydown',
        'propagate':        false,
        'disable_in_input': false,
        'target':           document
    });

    shortcut.add("Enter",function() {
        if (FM.isPopupOpened()) {
            return FM.handlePopupSubmit();
        }
        var tab = FM.getTabLetter(FM.CURRENT_TAB);
        var elm = $(FM.CURRENT_TAB).find('.dir:eq('+FM['CURRENT_'+tab+'_LINE']+')');
        
        if (elm.length == 1) {
            var src = $.parseJSON($(elm).find('.source').val());
            
            if (src.type == 'd') {
                FM.open(src.full_path, FM.CURRENT_TAB);
            }
            else {
                FM.openFile(src.full_path, FM.CURRENT_TAB, elm);
            }
        }
    },{
        'type':             'keydown',
        'propagate':        false,
        'disable_in_input': false,
        'target':           document
    });




    /* is jQuery .live() has been removed in version 1.9 onwards
    $(FM.TAB_A + ' .dir').live('click', function(evt) {
        FM.selectItem(evt.target, FM.TAB_A);
    });
    $(FM.TAB_B + ' .dir').live('click', function(evt) {
        FM.selectItem(evt.target, FM.TAB_B);
    });
    */

    /*$(FM.TAB_A).on('click', '.dir', function(evt) {
        //console.log(evt);
        if (evt.ctrlKey || evt.metaKey || evt.altKey) {
            return;
        }
        FM.selectItem(evt.target, FM.TAB_A);
    });
    $(FM.TAB_B).on('click', '.dir', function(evt) {
        if (evt.ctrlKey || evt.metaKey || evt.altKey) {
            return;
        }
        FM.selectItem(evt.target, FM.TAB_B);
    });*/

    $(FM.TAB_A_MENU).on('click', '.sort-by', function(evt){
        FM.showOrderBox($(evt.target), FM.TAB_A);
    });

    $(FM.TAB_B_MENU).on('click', '.sort-by', function(evt){
        FM.showOrderBox($(evt.target), FM.TAB_B);
    });

    $('.context-menu.sort-order').on('click', 'span', function(evt){
        FM.reOrderList($(evt.target));
    });
    
    $('.warning-box .close').on('click', function(evt){
        $(evt.target).closest('.warning-box').hide();
    });

    $('.menu-A .extract-btn').hide();
    $('.menu-B .extract-btn').hide();
    
    

});

/*$(document).bind('keydown.tab', function() {
    FM.switchTab();
});*/

$(window).bind('statechange', function(evt){
    $(evt).stopPropagation();
    // History.getState() 
    alert('No way back yet');
})
