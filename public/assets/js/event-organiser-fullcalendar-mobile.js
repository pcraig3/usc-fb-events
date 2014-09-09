//(function ($) {

    ///

    //var wp = $(window.wp);

   /* console.log(window);
    console.log(window.wp);
    console.log(window[0].wp);

    console.log("test");*/
    /*wp.hooks.addFilter( 'eventorganiser.fullcalendar_render_event', function( bool, event, element, view ){


        console.log("something");
    }, 4 );*/

//})(jQuery);

    var eventorganiser = window.eventorganiser || {};

    window.wp.hooks.addFilter( 'eventorganiser.fullcalendar_render_event', function( bool, event, element, view ){

        console.log(bool);
        console.log(event);
        console.log(element);
        console.log(view);

        return bool;

    }, 4 );
