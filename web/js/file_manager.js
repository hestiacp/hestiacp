var FM = {};

GLOBAL.ajax_url = '/file_manager/fm_api.php';

FM.DIR_MARKER = '&nbsp;&nbsp;/';

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


FM.directoryNotAvailable = function(reply) {
    alert('Directory not available'); // todo: translate
}

FM.showError = function(type, message) {
    alert(message);
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
    
    FM.open(dir_A, FM.TAB_A);
    FM.open(dir_B, FM.TAB_B);
}

FM.setActive = function(index, box) {
    var tab = FM.getTabLetter(box);
    $(box + ' .selected').removeClass('selected');
    $(box).find('li:eq('+index+')').addClass('selected');
    //$(box).find('li:eq('+index+')').addClass('selected');
    var w_h = $(window).height();
    var pos = $(box).find('li:eq('+index+')').position();
    console.log(w_h);
    console.log(pos);
    if (pos.top > w_h) {
        $(box).scrollTo($(box).find('li:eq('+index+')'));
    }
    else {
        if (Math.abs(pos.top) > w_h) {
            $(box).scrollTo($(box).find('li:eq('+index+')'));
        }
    }

    FM['CURRENT_' + tab + '_LINE'] = index;
    FM.CURRENT_TAB  = box;
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


FM.open = function(dir, box) {
    var tab = FM.getTabLetter(box);

    FM['TAB_'+tab+'_CURRENT_PATH'] = dir;

    var params = {
        'dir': dir
    };
    App.Ajax.request('cd', params, function(reply) {
        if (reply.result) {
            var html = FM.generate_listing(reply.listing, box);
        }
        else {
            FM.directoryNotAvailable(reply);
        }
    });
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

FM.openFile = function(dir, box, elm) {
    var tab = FM.getTabLetter(box);

    FM['TAB_'+tab+'_CURRENT_PATH'] = dir;
    
    var elm = $(elm).hasClass('dir') ? $(elm) : $(elm).closest('.dir');
    var src = $.parseJSON($(elm).find('.source').val());
    
    var myWindow = window.open('/edit/file/?path=' + src.full_path, '_blank');//, src.full_path, "width=900, height=700");
    /*var params = {
        'dir': dir
    };
    App.Ajax.request('open_file', params, function(reply) {
        if (reply.result) {
            
            //var html = FM.generate_listing(reply.listing, box);
        }
        else {
            //FM.directoryNotAvailable(reply);
            alert('Cannot open file');
        }
    });*/
}

FM.getTabLetter = function(box) {
    var tab = 'A';
    if (box == FM.TAB_B) {
        tab = 'B';
    }
    
    return tab;
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
        
        o.full_path = path;

	o.filetype = FM.getFileType(o.name);
	if(FM.IMG_FILETYPES.indexOf(o.filetype) >= 0 && o.filetype.length > 0) {
	    FM.IMAGES[tab][FM.IMAGES[tab].length] = {'img': "/view/file/?path=/home/admin/"+o.name+"&raw=true", 'thumb': "/view/file/?path=/home/admin/"+o.name+"&raw=true", 'id': 'img-'+i};
	    cl_act = 'onClick="FM.fotoramaOpen(\'' + tab + '\', \'img-' + i +'\')"';
	}
	

        var tpl = Tpl.get('entry_line', 'FM');
        tpl.set(':CL_ACTION_1', cl_act);
        tpl.set(':SOURCE', $.toJSON(o));
        tpl.set(':NAME', o.name);
        tpl.set(':PERMISSIONS', o.permissions);
        tpl.set(':OWNER', o.owner);
        tpl.set(':SIZE', o.size);
        tpl.set(':TIME', time);
        tpl.set(':DATE', o.date);

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

FM.selectItem = function(item, box) {
    $(box).find('.active').removeClass('active');
    $(box).find('.selected').removeClass('selected');
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
}

FM.setTabActive = function(box) {
    FM.CURRENT_TAB = box;
    $('.window.active').removeClass('active');
    $('.listing-left.active').removeClass('active');
    $('.listing-right.active').removeClass('active');
    $(FM.CURRENT_TAB).addClass('active');
    $(FM.CURRENT_TAB).closest('.window').addClass('active');
}

FM.confirmRename = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    var selected = $(FM['TAB_' + tab] ).find('.dir.active');
    if (!selected) {
        return alert('No file selected');
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    
    var target_name = $('#rename-title').val();
    
    if (target_name.trim().length == 0) {
        return alert('Cannot be renamed.');
    }

    var params = {
        item: src.name,
        target_name: target_name,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH']
    };
    
    App.Ajax.request('rename_file', params, function(reply) {
        if (reply.result) {
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
        }
        else {
            FM.showError('rename-items', reply.message);
        }
        FM.popupClose();
    });
}

FM.renameItems = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var selected = $(FM['TAB_' + tab] ).find('.dir.active');
    if (selected.length == 0) {
        return alert('No file selected');
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);

    var tpl = Tpl.get('popup_rename', 'FM');
    tpl.set(':FILENAME', src.name);
    FM.popupOpen(tpl.finalize());
    /*var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM.CURRENT_TAB;
    
    var delete_list = $(box).find('.active');
    if (delete_list.length == 0) {
        return FM.showError('hint', 'No selected items');
    }
    
    $(delete_list).each(function(i, o) {
        var opt = $(o).find('.source').val();
        opt = $.parseJSON(opt);
        prompt('Rename "' + opt.name + '" to:');
    });*/
}

FM.popupOpen = function(html) {
    $('<div>').attr('id', 'popup').html(html).flayer();
}

FM.popupClose = function() {
    return $('#popup').flayer_close();
}

FM.copyItems = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    
    if (!selected) {
        return alert('No file selected');
    }
    
    var opposite_tab = 'A';
    if (tab == 'A') {
        opposite_tab = 'B';
    }
    
    var src = selected.find('.source').val();
    src = $.parseJSON(src);
    console.log(src);
    
    var params = {
        item: src.full_path,
        filename: src.name,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH'],
        dir_target: FM['TAB_' + opposite_tab + '_CURRENT_PATH']
    };
    
    App.Ajax.request('copy_files', params, function(reply) {
        if (reply.result) {
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], FM['TAB_' + tab]);
            FM.open(FM['TAB_' + opposite_tab + '_CURRENT_PATH'], FM['TAB_' + opposite_tab]);
        }
        else {
            FM.showError('delete-items', reply.message);
        }
        //FM.popupClose();
    });
}

FM.downloadFiles = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (!selected) {
        return alert('No file selected');
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);

    if (src.type != 'f') {
        return alert('Only files can be dosnloaded in this version');
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
        return alert('No file selected');
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);

    var params = {
        item: src.full_path,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH']
    };
    
    App.Ajax.request('delete_files', params, function(reply) {
        if (reply.result) {
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
        }
        else {
            FM.showError('delete-items', reply.message);
        }
        FM.popupClose();
    });
}

FM.deleteItems = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (selected.length == 0) {
        return alert('No file selected');
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);

    var tpl = Tpl.get('popup_delete', 'FM');
    tpl.set(':FILENAME', src.name);
    FM.popupOpen(tpl.finalize());

    
    /*var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var confirmed = confirm(App.i18n.ARE_YOU_SURE);
    var box = FM.CURRENT_TAB;
    
    var delete_list = $(box).find('.selected');
    if (delete_list.length == 0) {
        return FM.showError('hint', 'No selected items');
    }

    var params = {
        items: [],
        dir:  FM['TAB_' + tab + '_CURRENT_PATH']
    };
    $(delete_list).each(function(i, opt){
        var opt = $(o).find('.source').val();
        opt = $.parseJSON(opt);
        params.items.push(opt.name);
    });
    
    App.Ajax.request('delete_files', params, function(reply) {
        if (reply.result) {
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
        }
        else {
            FM.showError('delete-items', reply.message);
        }
    });*/
}


FM.confirmCreateDir = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    /*var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (!selected) {
        return alert('No file selected');
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);*/
    
    var dirname = $('#rename-title').val();
    
    if (dirname.trim().length == 0) {
        return alert('Cannot be created.');
    }

    var params = {
        dirname: dirname,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH']
    };
    
    App.Ajax.request('create_dir', params, function(reply) {
        if (reply.result) {
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
        }
        else {
            FM.showError('create-file', reply.message);
        }
        FM.popupClose();
    });
}

FM.createDir = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    
    var tpl = Tpl.get('popup_create_dir', 'FM');
    FM.popupOpen(tpl.finalize());
    /*var dirname = prompt('Enter dir name:');
    if (dirname.trim() != '') {
        
        var box = FM.CURRENT_TAB;
        var tab = FM.getTabLetter(box);
        var params = {
            'dirname': dirname,
            'dir':      FM['TAB_' + tab + '_CURRENT_PATH']
        };
        App.Ajax.request('create_dir', params, function(reply) {
            if (reply.result) {
                FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
            }
            else {
               FM.showError('create-dir', reply.message);
            }
        });
    }
    else {
        FM.showError('dirname-empty', 'Dirname cannot be empty');
    }*/
}

FM.confirmCreateFile = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    var box = FM['TAB_' + tab];
    /*var selected = $(FM['TAB_' + tab] ).find('.dir.selected');
    if (!selected) {
        return alert('No file selected');
    }

    var src = selected.find('.source').val();
    src = $.parseJSON(src);*/
    
    var filename = $('#rename-title').val();
    
    if (filename.trim().length == 0) {
        return alert('Cannot be created.');
    }

    var params = {
        filename: filename,
        dir:  FM['TAB_' + tab + '_CURRENT_PATH']
    };
    
    App.Ajax.request('create_file', params, function(reply) {
        if (reply.result) {
            FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
        }
        else {
            FM.showError('create-file', reply.message);
        }
        FM.popupClose();
    });
}

FM.createFile = function() {
    var tab = FM.getTabLetter(FM.CURRENT_TAB);
    
    var tpl = Tpl.get('popup_create_file', 'FM');
    FM.popupOpen(tpl.finalize());
    /*var filename = prompt('Enter file name:');
    if (filename.trim() != '') {
        
        var box = FM.CURRENT_TAB;
        var tab = FM.getTabLetter(box);
        var params = {
            'filename': filename,
            'dir':      FM['TAB_' + tab + '_CURRENT_PATH']
        };
        App.Ajax.request('create_file', params, function(reply) {
            if (reply.result) {
                FM.open(FM['TAB_' + tab + '_CURRENT_PATH'], box);
            }
            else {
               FM.showError('create-file', reply.message);
            }
        });
    }
    else {
        FM.showError('filename-empty', 'Filename cannot be empty');
    }*/
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
    
    primary_box.find('span').removeClass('selected');
    $(menu).find('.sort-by .entity').html(elm.closest('li').find('span').html());
    $(menu).find('.sort-by').removeClass('desc asc').addClass(direction).addClass('sort-by');

    elm.addClass('selected');
    primary_box.hide();

    FM.open(path, tab);
}



FM.init();


$(document).ready(function() {
    
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
    
    shortcut.add("Tab",function() {
        FM.switchTab();
    },{
        'type':             'keydown',
        'propagate':        false,
        'disable_in_input': false,
        'target':           document
    });

    shortcut.add("Enter",function() {
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

    $(FM.TAB_A).on('click', '.dir', function(evt) {
        FM.selectItem(evt.target, FM.TAB_A);
    });
    $(FM.TAB_B).on('click', '.dir', function(evt) {
        FM.selectItem(evt.target, FM.TAB_B);
    });

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


});

/*$(document).bind('keydown.tab', function() {
    FM.switchTab();
});*/

$(window).bind('statechange', function(evt){
    $(evt).stopPropagation();
    // History.getState() 
    alert('No way back yet');
})
