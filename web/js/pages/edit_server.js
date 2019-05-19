    $('select[name=v_sftp]').change(function(){
        if($(this).val() == 'yes'){
            $('.sftp.description').show();
        } else {
            $('.sftp.description').hide();
        }
    });

    $('input[name=v_mail_relay]').change(function(){
        if($(this).is(':checked')){
            $('.mail-relay').show();
        } else {
            $('.mail-relay').hide();
        }
    });
