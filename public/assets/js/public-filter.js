jQuery(function ($) {
    /* You can safely use $ in this code block to reference jQuery */

    var AjaxEventsPublic = (function (AjaxEvents) {

        /**
         * @since     1.0.0
         * @param events
         */
        var ajax_events_gotten = function( events ) {

        $('.filterjs__loading').addClass('hidden');

        fJS = filterInit( events );

        $('#event_list').trigger( "change" );
    };

    /**
     * Function sets up all of our filtering.
     * Works now, but seems a bit brittle.
     *
     * @param events    a list of events. Data from FB is merged with information from our database.
     *
     * @since    1.0.0
     *
     * @returns {*} A list of searchable events in the backend.
     */
    var filterInit = function( events ) {

        var view = function( event ){

            //at this point we have ONE EVENT.  This sets up the loop.
            var html_string = "";

            var img_url = ( event.pic_big ) ? event.pic_big : "";
            var ticket_uri = ( event.ticket_uri ) ? event.ticket_uri : "";

            /* Figure out if the event has passed or not. */
            var past_or_upcoming_event = (AjaxEvents.is_upcoming_event( event )) ? "upcoming" : "past";

            html_string += '<div class="events__box clearfix ' + past_or_upcoming_event + '">';
            html_string +=  '<div class="flag">';
            html_string +=      '<div class="flag__image">';

            if(img_url) {
                html_string +=      '<img src="' + img_url + '">';
            }

            html_string +=      '</div><!--end of .flag__image-->';

            html_string +=      '<div class="flag__body">';
            html_string +=          '<h3 class="alpha" title="'
                                    + event.host + ": " + event.name + '">'
                                    + event.name + '</h3>';

            var date = new Date( parseInt(event.start_time) * 1000);

            html_string +=          '<p class="lede" data-start_time="' + event.start_time + '">' + date.toLocaleDateString() + ' | '
                                    + event.host + '</p>';

            html_string +=      '</div><!--end of .flag__body-->';

            html_string += '</div><!--end of .flag-->';

            if( ticket_uri ) {

                html_string += '<a href="' + ticket_uri + '" target="_blank">';
                html_string +=      '<div class="event__button" style="background:palevioletred;color:white;">Get Tickets</div>';
                html_string += '</a>';
            }

            html_string +=      '<a href="' + event.url + '" target="_blank">';
            html_string +=          '<span class="events__box__count">' + event.id + '</span>';
            html_string +=      '</a>';

            html_string += '</div><!--end of events__box-->';

            return html_string;
        }

        var settings = {
            /*filter_criteria: {
                removed: ['#removed :checkbox', 'removed']
            },*/
            search: {input: '#search_box' },
            //and_filter_on: true,
            id_field: 'id' //Default is id. This is only for usecase
        };

        return FilterJS(events, "#event_list", view, settings);
    }

        return {
            ajax_events_gotten: ajax_events_gotten,
            ajax_get_events: AjaxEvents.ajax_get_events
        };

    })(AjaxEvents || {});

    $(document).ready(function($) {

        //$('#removed :checkbox').prop('checked', true);

        console.log(options);

        AjaxEventsPublic.ajax_get_events( options, AjaxEventsPublic.ajax_events_gotten );
    });

});

