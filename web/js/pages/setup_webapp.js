randomString = function(target, min_length = 16) {
    elm = document.getElementById(target);
    $(elm).val(randomString2(min_length));
}