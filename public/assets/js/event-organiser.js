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


        //if you click one of the fc-put-in-by-me buttons, slide to them instead of jumping.
        var $html_body = $( 'html, body' );

        $html_body
            .on( 'click', '#usc_fb_events_fullcalendar__list__header .fc-button', function(e) {

                //hrefs are ids of elements on the same page -- not external links
                var href = $( this ).attr( 'href' );
                var height_of_sticky_header = $( this).parents( '#usc_fb_events_fullcalendar__list__header' ).height();

                $html_body.animate( { scrollTop : $( href ).offset().top - height_of_sticky_header }, 800 );
                e.preventDefault();
            } );

        //finally, get it so that the events slide open like every other accordion on the site.

        /**
         * Don't want to usc Divi theme's toggle function (because of the styling and how animations have to be
         * 700 ms, but I think it's probably a good idea to be using similar code.
         * So I've taken their code and I'm working it for me.
         */
        $html_body
            .on( 'click', '.event__title', function() {

            var $this_heading = $(this),
                $list_item = $this_heading.parents( '.eo-fb-event-list' ),
                $meta_div = $list_item.find('.meta');

            if ( $meta_div.is( ':animated' ) ) {
                return;
            }

            $meta_div.slideToggle( 200, function() {
                if ( $meta_div.hasClass('event-meta-closed') ) {
                    $this_heading.addClass('clicked');
                    $meta_div.removeClass('event-meta-closed').addClass( 'event-meta-open' );
                }
                else {
                    $this_heading.removeClass('clicked');
                    $meta_div.removeClass( 'event-meta-open' ).addClass( 'event-meta-closed' );
                }
            } );
        } );

        /** What would it need to do? **/
        /**
         * So, click a button, start a countdown.
         * Click again, restart the countdown.
         * Check for the loading thing.  If you haven't seen the loading thing and the time
         * runs out, then print a 'no events found' notice.
         * If you have seen the loading thing, wait a bit longer.
         * If you have seen the loading thing and then an event list appears, then stop everything.
         * If you have seen the loading thing and NO event list appears, then print a 'no events found' message
         * ** At any point this can be interrupted by another button press.  Reset everything on another button press **
         */

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
        //just to note, this won't be triggered if you click the 'back to top' or 'jump to today' buttons (
        //in the header beside the month on mobile) because they're not contained in the $eo_fullcalendar object
        $eo_fullcalendar
            .on( 'click', '.fc-button', function(){
                //set a new height for the loading div
                var height_sticky_title = $('#usc_fb_events_fullcalendar__list__header').height();

                //some weird bug keeps giving me the wrong height
                var height_header = $eo_fullcalendar.find('.fc-header').height() + 2;

                $loading.css('height', height_header + height_sticky_title + 'px');
                $loading.css('padding-top', ( ( height_header + height_sticky_title - 46 ) / 2) + 'px');
        });

    }

}(jQuery));