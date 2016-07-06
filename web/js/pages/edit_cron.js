$(document).ready(function(){
    $( "#tabs" ).tabs();
    $('.context-helper').click(function(){ $('#tabs').toggle(); $('.context-helper').toggle();  });
    $('.context-helper-close').click(function(){ $('#tabs').toggle(); $('.context-helper').toggle(); });

    $('.helper-container form').submit(function(){
        $('#vstobjects input[name=v_min]').val($(this).find(':input[name=h_min]').val()).effect('highlight');
        $('#vstobjects input[name=v_hour]').val($(this).find(':input[name=h_hour]').val()).effect('highlight');
        $('#vstobjects input[name=v_day]').val($(this).find(':input[name=h_day]').val()).effect('highlight');
        $('#vstobjects input[name=v_month]').val($(this).find(':input[name=h_month]').val()).effect('highlight');
        $('#vstobjects input[name=v_wday]').val($(this).find(':input[name=h_wday]').val()).effect('highlight');

        return false;
    });
})

