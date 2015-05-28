(function($) {
    var gallery = new Array();
    function init() {	
        var viewer = $("#iviewer .viewer").
        width($(window).width() - 80).
        height($(window).height()).
        iviewer({
            ui_disabled : true,
            zoom : 'fit',
            onFinishLoad : function(ev) {
                $("#iviewer .loader").fadeOut();
                $("#iviewer .viewer").fadeIn();			
            }
        }
        );

        $("#iviewer .zoomin").click(function(e) {
            e.preventDefault();
            viewer.iviewer('zoom_by', 1);
        });

        $("#iviewer .zoomout").click(function(e) {
            e.preventDefault();
            viewer.iviewer('zoom_by', -1);
        });

        /*
         * Populate gallery array.
         * NOTE: In order to add image to gallery, Anchor tag of images that are to be opened in lightbox, should have attribute 'rel' set to 'gallery'.
         */
        $( "a[rel='gallery']" ).each(function( index ) {
            gallery[index] =  $( this ).attr("href");	    
        });
    }

    function open(src) {
        $("#iviewer").fadeIn().trigger('fadein');
        $("#iviewer .loader").show();
        $("#iviewer .viewer").hide();

        var viewer = $("#iviewer .viewer")
        .iviewer('loadImage', src)
        .iviewer('set_zoom', 'fit');
    }

    function close() {
        $("#iviewer").fadeOut().trigger('fadeout');
    }

    $('.go').click(function(e) {
        e.preventDefault();
        var src = $(this).attr('href');
        open(src);
        // Refresh next and prev links
        refreshNextPrevLinks(src);
    });

    $("#iviewer .close").click(function(e) {
        e.preventDefault();
        close();
    });

    $("#iviewer").bind('fadein', function() {
        $(window).keydown(function(e) {
            if (e.which == 27) close();
        });
    });

    /*
    *  refreshNextPrevLinks() refreshes Next and previous links
    */
    function refreshNextPrevLinks(src){
	console.log('RefreshNextPrevLinks called. src: '+src);
        var imageIndex = 0;
        //Iterate over gallery and find the index of current image.
        for (i=0;i<gallery.length;i++)
        {         
	 if(src == gallery[i]){
                imageIndex = i;
            }
        }
	
        //Setting Next link
        var nextLink = document.getElementById('nextLink');
        if(gallery.length > imageIndex+1){
            nextLink.href =  gallery[imageIndex+1];
            nextLink.style.visibility = 'visible';
        }else{
            nextLink.href = "#";
            nextLink.style.visibility = 'hidden';
        }	

        //Setting Prev link
        var prevLink = document.getElementById('prevLink');
        if(imageIndex > 0){
            prevLink.href = gallery[imageIndex-1];
            prevLink.style.visibility = 'visible';
        }else{
            prevLink.setAttribute("href", "#");
            prevLink.style.visibility = 'hidden';
        }	

        document.getElementById('imageCount').innerHTML= "Image: "+ (imageIndex+1) + "/" + gallery.length;	
    }

    //Binding keypress event of left arrow and right arrow button. Image would be changed, if right arrow or left arrow button is pressed.
    $(document).keyup(function(e) {
        //left arrow key
        if (e.keyCode == 37) { 
            if($("#prevLink").attr("href") != "#"){
                $("#prevLink").click();
            }
        }
        //right arrow
        if (e.keyCode == 39) {
            if($("#nextLink").attr("href") != "#"){
                $("#nextLink").click();
            }
        }
    });

    init();
})(jQuery);
