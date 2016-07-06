$(document).ready(function(){
    $('.add-ns-button').click(function(){
        var n = $('input[name^=v_ns]').length;
        if(n < 8){
            var t = $($('input[name=v_ns1]').parents('tr')[0]).clone(true, true);
            t.find('input').attr({value:'', name:'v_ns'+(n+1)});
            t.find('span').show();
            $('tr.add-ns').before(t);
        }
        if( n == 7 ) {
            $('.add-ns').hide();
        }
    });

    $('.remove-ns').click(function(){
        $(this).parents('tr')[0].remove();
        $('input[name^=v_ns]').each(function(i, ns){
            $(ns).attr({name: 'v_ns'+(i+1)});
            i < 2 ? $(ns).parent().find('span').hide() : $(ns).parent().find('span').show();
        })
        $('.add-ns').show()
    });

    $('input[name^=v_ns]').each(function(i, ns){
        i < 2 ? $(ns).parent().find('span').hide() : $(ns).parent().find('span').show();
    });
});
