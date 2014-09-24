/**
 * AjaxEventsAdmin is a JS module that uses the AjaxEvents module for getting events and saving transients,
 * but then adds a bunch of its own methods for templating and controlling the FB event list in the backend.
 */

jQuery(function ($) {

    var AjaxEventsAdmin = (function (AjaxEvents, options) {

        var _$filterjs      = $('.filterjs');
        var _$event_list    = _$filterjs.find('#event_list');
        var _$title_row     = $('.title_row ');

        var _options = options;

        var _initiated = false;

        /**
         * method to trigger after the facebook events have been received, either initially after first loading
         * the backend 'Manage Facebook Events' Page or afterwards when getting events for different years.
         *
         * Method:
         * 1. Clears existing events
         * 2. Checks all checkboxes (otherwise, events will BE there, but won't show up.)
         * 3. Runs the filterInit method which builds the filter-able event list
         * 4. Triggers a 'change' event on the event list itself (for admin.js)
         * 5. Runs a run_once method (so, the body of this method is only executed on initial page load.
         * 6. end_ajax_load_more_events cleans up the interface so that we can use it again.
         *
         * @since     1.1.0
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

        /**
         * okay, so check it out: to get new events, we need to send a new start + new end time.
         * So, we can just use the number of seconds in a year and the initial timestamp in the span in the
         * .title and set prev/next year timestamps on the buttons.
         *
         * Runs when the page is first loaded or when the page is updated after getting events for a different year
         *
         * @since     1.1.0
         * @type {Function}
         */
        var update_button_start_end_times = (function() {

            var year_in_seconds = _$title_row.find('#ajax__year').data('year_in_seconds');
            var current_year = _$title_row.find('#ajax__year').data('start');

            _$title_row.find('#prev_year_button, #next_year_button').each(function() {

                var next_or_past_year = current_year + ( year_in_seconds * $(this).data( 'direction' ) );
                $(this).data( 'start', next_or_past_year );
                $(this).data( 'end', (next_or_past_year + year_in_seconds - 1) )
            });
        });

        /**
         * function is only executed once after the first crop of facebook events are loaded. Exclusively attaches
         * event handlers to various UI elements, because before the events are returned they can't be used.
         *
         * @since     1.1.0
         * @type {Function}
         */
        var run_once = (function () {

            if( _initiated )
                return false;

            listen_for_searches();
            listen_for_checkboxes();
            listen_for_buttons();

            _initiated = true;
        });

        /**
         * function sets an event listener on the search box.  Waits fifty milliseconds after keyup events
         * before updating the event count.
         *
         * @since     1.1.0
         * @type {Function}
         */
        var listen_for_searches = (function () {

            //keyup event listener updates 'x events' string
            _$filterjs.find('#search_box').on('keyup', function() {

                typewatch(function () {
                    update_event_count();
                }, 50);
            });
        });

        /**
         * function sets an event listener on the checkboxes.  Updates event count when they're clicked.
         *
         * @since     1.1.0
         * @type {Function}
         */
        var listen_for_checkboxes = (function () {

            //click event listener updates 'x events' string
            _$filterjs.find('.filterjs__filter__checkbox__wrapper li').on('click', function() {

                update_event_count();
            });
        });

        /**
         * function sets a click event to the next/prev year buttons.
         * Gets a new start time, a new end time, clears the transient name (the one in the
         * options would be the transient that the last returned events are saved under),
         * starts the UI signal that more events are being loaded, and then gets the events for real.
         *
         * Last line is very similar to line at the bottom of this file.
         *
         * @since     1.1.0
         * @type {Function}
         */
        var listen_for_buttons = (function () {

            _$title_row.find('#prev_year_button, #next_year_button').on('click', function() {

                /*
                 So the algorithm to do past or next events should be to
                 1. get the start time
                 2. get the end time
                 3. update the options start time
                 4. update the options end time
                 5. clear the options transient name
                 */
                _options.start  = $(this).data('start');
                _options.end    = $(this).data('end');
                delete _options.transient_name;

                start_ajax_load_more_events();

                AjaxEvents.ajax_get_events( _options, ajax_events_gotten );
            });
        });

        /**
         * Pretty basic method that's also a pretty useful method.
         * Count the number of visible events and update the counter above and below the event list
         *
         * 'no events', '1 event', '2+ events'
         *
         * @since     1.1.0
         * @type {Function}
         */
        var update_event_count = (function () {

            var num_events = _$event_list.find('.row:visible').length;

            num_events = ( num_events === 0 ) ? 'no events' : ( num_events === 1 ) ? num_events + ' event' : num_events + ' events' ;

            _$filterjs.find('.event_list__counter').text(num_events.toString());
        });

        /**
         * function makes a couple of UI changes that signal we are loading events.
         *
         * 1. 'next/prev' year event buttons are disabled
         * 2. loading horse is unveiled.
         *
         * @since     1.1.0
         * @type {Function}
         */
        var start_ajax_load_more_events = (function() {

            //disable the buttons
            _$title_row.find('#prev_year_button, #next_year_button').prop("disabled", true);
            //loading horse
            _$filterjs.find('.filterjs__loading').removeClass('hidden');
        });

        /**
         * function takes care of the UI after events have been returned to the event list
         *
         * 1. start + end times in title are updated
         * 2. Year in title is updated
         * 3. start + end times of next/prev buttons are updated
         * 4. 'next/prev' event buttons are enabled
         * 5. loading horse is hid.
         * 6. event counter is updated
         *
         * @since     1.1.0
         * @type {Function}
         */
        var end_ajax_load_more_events = (function() {

            //update date in title and metadata as well
            var $ajax_events = _$title_row.find('#ajax__year');
            var date = new Date( parseFloat( _options.start ) * 1000 );

            $ajax_events.data('start', _options.start);
            $ajax_events.data('end', _options.end);
            $ajax_events.text( date.getUTCFullYear() );

            //update the button metadata
            update_button_start_end_times();

            //enable the buttons
            _$title_row.find('#prev_year_button, #next_year_button').prop("disabled", false);
            //loading horse
            _$filterjs.find('.filterjs__loading').addClass('hidden');

            //update number of events
            update_event_count();

        });


        /**
         * Function sets up all of our filtering.
         * Event UI template and filter options are set here.
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
         * @since     1.1.0
         */
        var typewatch = (function(){
            var timer = 0;
            return function(callback, ms){
                clearTimeout (timer);
                timer = setTimeout(callback, ms);
            }
        })();

        /**
         * publicly accessible methods.  Everything else is contained in our module.
         *
         * @since     1.1.0
         */
        return {
            ajax_events_gotten: ajax_events_gotten,
            ajax_get_events: AjaxEvents.ajax_get_events
        };

    })(AjaxEvents || {}, options);

    /**
     * @since     1.1.0
     */
    $(document).ready(function($) {

        //set the start and end options here
        var $ajax_year = $('#ajax__year');

        options.start = $ajax_year.data('start');
        options.end = $ajax_year.data('end');

        AjaxEventsAdmin.ajax_get_events( options, AjaxEventsAdmin.ajax_events_gotten );
    });

});