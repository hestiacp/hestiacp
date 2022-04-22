$('#backup_type').change(function (){
   if(this.value == 'b2'){
       $('#backup_bucket').show();
       $('#backup_sftp').hide();
   }else{
       $('#backup_bucket').hide();
       $('#backup_sftp').show();
   }
});

$('#api, #api-system').change(function () {
    var api = $('#api').val();
    var apiSystem = $('#api-system').val();

    if (api === 'yes' || apiSystem > 0) {
        $('#security_ip').show();
    } else {
        $('#security_ip').hide();
    }
});
