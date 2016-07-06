$(document).ready(function(){
    $('select[name=v_filemanager]').change(function(){
        if($(this).val() == 'yes'){
            $('.filemanager.description').show();
        } else {
            $('.filemanager.description').hide();
        }
    });

    $('select[name=v_sftp]').change(function(){
        if($(this).val() == 'yes'){
            $('.sftp.description').show();
        } else {
            $('.sftp.description').hide();
        }
    });
});
