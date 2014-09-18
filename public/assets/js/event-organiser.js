(function ( $ ) {
    "use strict";

    $(function () {

        // JavaScript here will only be included on pages with an Event-Organiser fullcalendar

        var $eo_fullcalendar = $('.eo-fullcalendar');

        $eo_fullcalendar
            .on( 'click', '.fc-event', function(e){
                e.preventDefault();
                window.open( $(this).attr('href'), '_blank' );
        });

        loading_horse( $eo_fullcalendar );

    });

    function loading_horse( $eo_fullcalendar ) {

        var loading = document.getElementById('eo_fullcalendar_1_loading');
        var $loading = $( loading );

        var loading_img = loading.firstChild;
        loading_img.src = 'http://westernusc.org/wp-content/plugins/usc-fb-events/assets/horse.gif';

        var loading_gif_styling = {
            //position: absolute; //this is the default
            //z-index: 5;  //this is the default
            //display: none
            //background-color: white

            textAlign:      'center',
            width:          '100%',
            boxSizing:      'border-box',
            borderTop:      '1px dashed #ddd',
            borderBottom:   '1px dashed #ddd',
            visibility:     'visible',
            backgroundColor:'rgba(255,255,255, .8)',
            height:         51 + 'px'
        };

        //get rid of anything after the image (ie, the 'Loading...' phrase)
        loading.innerHTML = loading.innerHTML.substring(0, loading.innerHTML.indexOf('>') + 1);

        $loading.css( loading_gif_styling );

        //basically, click on any of the buttons and get a new height
        $eo_fullcalendar
            .on( 'click', '.fc-button', function(){
                //set a new height for the loading div
                console.log('CLICK!');
                var height_sticky_title = $('#usc_fb_events_fullcalendar__list__header').height();

                //some weird bug keeps giving me the wrong height
                var height_header = $eo_fullcalendar.find('.fc-header').height() + 2; 

                $loading.css('height', height_header + height_sticky_title + 'px');
                $loading.css('padding-top', ( ( height_header + height_sticky_title - 46 ) / 2) + 'px');
        });

    }

}(jQuery));