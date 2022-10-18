$(document).ready(function(){
    $('.js-add-ns-button').click(function(){
        var n = $('input[name^=v_ns]').length;
        if(n < 8){
            var t = $($('input[name=v_ns1]').parents('tr')[0]).clone(true, true);
            t.find('input').attr({value:'', name:'v_ns'+(n+1)});
            t.find('span').show();
            $('tr.js-add-ns').before(t);
        }
        if( n == 7 ) {
            $('.js-add-ns').addClass('u-hidden');
        }
    });

    $('.js-remove-ns').click(function(){
        $(this).parents('tr')[0].remove();
        $('input[name^=v_ns]').each(function(i, ns){
            $(ns).attr({name: 'v_ns'+(i+1)});
            i < 2 ? $(ns).parent().find('span').hide() : $(ns).parent().find('span').show();
        })
        $('.js-add-ns').removeClass('u-hidden');
    });

    $('input[name^=v_ns]').each(function(i, ns){
        i < 2 ? $(ns).parent().find('span').hide() : $(ns).parent().find('span').show();
    });
});
