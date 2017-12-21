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

    $('select[name=v_softaculous]').change(function(){
        if($(this).val() == 'yes'){
            $('.softaculous.description').show();
        } else {
            $('.softaculous.description').hide();
        }
    });

    $('input[name=v_mail_relay]').change(function(){
        if($(this).is(':checked')){
            $('.mail-relay').show();
        } else {
            $('.mail-relay').hide();
        }
    });
});
