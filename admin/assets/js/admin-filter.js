/**
 * Created by Paul on 22/09/14.
 */

jQuery(function ($) {

    var AjaxEventsAdmin = (function (AjaxEvents) {

        var _$filterjs = $('.filterjs');
        var _$event_list = _$filterjs.find('#event_list');

        /**
         * @since     1.0.0
         * @param events
         */
        var ajax_events_gotten = (function ( events ) {

            $('#removed :checkbox').prop('checked', true);

            fJS = filterInit( events );

            _$event_list.trigger( "change" );

            update_event_count();

            listen_for_searches();
            listen_for_checkboxes();

            });

        var listen_for_searches = (function () {

            //keyup event listener updates 'x clubs' string
            _$filterjs.find('#search_box').on('keyup', function() {

                typewatch(function () {
                    update_event_count();
                }, 50);
            });
        });

        var listen_for_checkboxes = (function () {

            //keyup event listener updates 'x clubs' string
            _$filterjs.find('.filterjs__filter__checkbox__wrapper li').on('click', function() {

                    update_event_count();
            });

        });

        var update_event_count = (function () {

            var num_events = _$event_list.find('.row:visible').length;

            num_events = ( num_events > 0 ) ? num_events : 'no';

            _$filterjs.find('.event_list__counter').text(num_events.toString());
        });

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
            };

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

        /** Wait a bit after a jquery event to do your action, basically.
         *  Found this on stackoverflow, written by this CMS guy
         *
         *  @see http://stackoverflow.com/questions/2219924/idiomatic-jquery-delayed-event-only-after-a-short-pause-in-typing-e-g-timew
         *  @author CMS
         *
         * @since    2.0.0
         */
        var typewatch = (function(){
            var timer = 0;
            return function(callback, ms){
                clearTimeout (timer);
                timer = setTimeout(callback, ms);
            }
        })();

        /**
         * @since     1.0.0
         */
        return {
            //update_event_count: update_event_count,
            ajax_events_gotten: ajax_events_gotten,
            ajax_get_events: AjaxEvents.ajax_get_events
        };

    })(AjaxEvents || {});

    /**
     * @since     1.0.0
     */
    $(document).ready(function($) {

        //set the start and end options here
        $ajax_year = $('#ajax__year');

        options.start = $ajax_year.data('start');
        options.end = $ajax_year.data('end');

        console.log($ajax_year.data('start'));

        console.log( options );

        AjaxEventsAdmin.ajax_get_events( options, AjaxEventsAdmin.ajax_events_gotten );
    });

});