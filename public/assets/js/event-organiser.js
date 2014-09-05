(function ( $ ) {
    "use strict";

    $(function () {

        // JavaScript here will only be included on pages with an Event-Organiser fullcalendar

        $('.eo-fullcalendar').on( 'click', '.fc-event', function(e){
            e.preventDefault();
            window.open( $(this).attr('href'), '_blank' );
        });

    });

}(jQuery));