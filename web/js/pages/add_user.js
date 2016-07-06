$(function() {
    $('#v_email').change(function() {
        document.getElementById('v_notify').value = document.getElementById('v_email').value;
    });
});


randomString = function() {
    var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
    var string_length = 10;
    var randomstring = '';
    for (var i = 0; i < string_length; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        randomstring += chars.substr(rnum, 1);
    }
    document.v_add_user.v_password.value = randomstring;
}
