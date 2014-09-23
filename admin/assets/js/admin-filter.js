/**
 * Created by Paul on 22/09/14.
 */

jQuery(function ($) {

    var AjaxEventsAdmin = (function (AjaxEvents, options) {

        var _$filterjs      = $('.filterjs');
        var _$event_list    = _$filterjs.find('#event_list');
        var _$title_row     = $('.title_row ');

        var _options = options;

        var _initiated = false;

        /**
         * @since     1.0.0
         * @param events
         */
        var ajax_events_gotten = (function ( events ) {

            //remove current events
            _$event_list.empty();

            $('#removed :checkbox').prop('checked', true);

            fJS = filterInit( events );

            _$event_list.trigger( "change" );

            run_once();
            end_ajax_load_more_events();
        });

        var update_button_start_end_times = (function() {

            var year_in_seconds = _$title_row.find('#ajax__year').data('year_in_seconds');
            var current_year = _$title_row.find('#ajax__year').data('start');

            _$title_row.find('#prev_year_button, #next_year_button').each(function() {

                var next_or_past_year = current_year + ( year_in_seconds * $(this).data( 'direction' ) );
                $(this).data( 'start', next_or_past_year );
                $(this).data( 'end', (next_or_past_year + year_in_seconds - 1) )
            });
        });

        var run_once = (function () {

            if( _initiated )
                return false;

            listen_for_searches();
            listen_for_checkboxes();
            listen_for_buttons();

            _initiated = true;
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

        /*
         So the algorithm to do past or next events should be to
         1. get the start time
         2. get the end time
         3. update the options start time
         4. update the options end time
         5. clear the options transient name
         */
        var listen_for_buttons = (function () {

            _$title_row.find('#prev_year_button, #next_year_button').on('click', function() {

                _options.start  = $(this).data('start');
                _options.end    = $(this).data('end');
                delete _options.transient_name;

                start_ajax_load_more_events();

                AjaxEvents.ajax_get_events( _options, ajax_events_gotten );
            });
        });

        var update_event_count = (function () {

            var num_events = _$event_list.find('.row:visible').length;

            num_events = ( num_events === 0 ) ? 'no events' : ( num_events === 1 ) ? num_events + ' event' : num_events + ' events' ;

            _$filterjs.find('.event_list__counter').text(num_events.toString());
        });


        var start_ajax_load_more_events = (function() {

            //disable the buttons
            _$title_row.find('#prev_year_button, #next_year_button').prop("disabled", true);
            //loading horse
            _$filterjs.find('.filterjs__loading').removeClass('hidden');
        });

        var end_ajax_load_more_events = (function() {

            //update date in title and metadata as well
            var $ajax_events = _$title_row.find('#ajax__year');
            var date = new Date( parseFloat( _options.start ) * 1000 );

            $ajax_events.data('start', _options.start);
            $ajax_events.data('end', _options.end);
            $ajax_events.text( date.getUTCFullYear() );

            //update the button metadata
            update_button_start_end_times();

            //disable the buttons
            _$title_row.find('#prev_year_button, #next_year_button').prop("disabled", false);
            //loading horse
            _$filterjs.find('.filterjs__loading').addClass('hidden');

            //update number of events
            update_event_count();

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
            ajax_events_gotten: ajax_events_gotten,
            ajax_get_events: AjaxEvents.ajax_get_events
        };

    })(AjaxEvents || {}, options);

    /**
     * @since     1.0.0
     */
    $(document).ready(function($) {

        //set the start and end options here
        var $ajax_year = $('#ajax__year');

        options.start = $ajax_year.data('start');
        options.end = $ajax_year.data('end');

        AjaxEventsAdmin.ajax_get_events( options, AjaxEventsAdmin.ajax_events_gotten );
    });

});