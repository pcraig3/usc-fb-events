/**
 * Created by Paul on 22/09/14.
 */

jQuery(function ($) {

    var AjaxEventsAdmin = (function (AjaxEvents) {

        /**
         * @since     1.0.0
         * @param events
         */
        var ajax_events_gotten = function ( events ) {

            $('#removed :checkbox').prop('checked', true);

            fJS = filterInit( events );

            $('#event_list').trigger( "change" );

        };

        /**
         * Function sets up all of our filtering.
         * Works now, but seems a bit brittle.
         *
         * @param events    a list of events. Data from FB is merged with information from our database.
         *
         * @since   0.4.0
         *
         * @returns {*} A list of searchable events in the backend.
         */
        function filterInit( events ) {

            var view = function( event ){
                var html_string = "<div class='row ";
                if(event.removed === "removed")
                    html_string += " removed ";
                if( event.removed === "modified" )
                    html_string += " modified ";

                html_string += "' ";

                //console.log(event);

                for (var key in event) {
                    if (event.hasOwnProperty(key)) {
                        if(typeof event[key] != 'undefined')
                        //changed the quotation marks here because apostrophes were breaking the HTML
                            html_string += 'data-' + key + '="' + event[key] + '" ';
                    }
                }

                html_string += ">" +
                    "<span class='name'>" + event.name + "</span>" +
                    "<span class='host'>" + event.host + "</span>" +
                    "<span class='removed'>" + event.removed + "</span>" +
                    "</div>";

                return html_string;
            }

            var settings = {
                filter_criteria: {
                    removed: ['#removed :checkbox', 'removed']
                },
                search: {input: '#search_box' },
                and_filter_on: true,
                id_field: 'id' //Default is id. This is only for usecase
            };

            return FilterJS(events, "#event_list", view, settings);
        }

        /**
         * @since     1.0.0
         */
        return {
            ajax_events_gotten: ajax_events_gotten,
            ajax_get_events: AjaxEvents.ajax_get_events
        };

    })(AjaxEvents || {});

    /**
     * @since     1.0.0
     */
    $(document).ready(function($) {

        console.log( options );

        AjaxEventsAdmin.ajax_get_events( options, AjaxEventsAdmin.ajax_events_gotten );
    });

});