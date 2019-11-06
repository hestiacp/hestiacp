function randomString(target) {
    var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
    var string_length = 16;
    var randomstring = '';
    for (var i = 0; i < string_length; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        randomstring += chars.substr(rnum, 1);
    }
    document.getElementById(target).value = randomstring;
}

$(document).ready(function() {

});
