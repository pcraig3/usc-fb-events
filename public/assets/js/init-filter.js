/**
 * init-filter.js declares the fJS variable that filterJS needs, as well as the AjaxEvents module that
 * gets extended in various ways across our plugin.
 *
 * Basically, AjaxEvents contains the method to get Facebook events, as well as to update the cache afterwards
 */
    var fJS;

    var AjaxEvents = (function () {

        /**
         * This method, together with 'get_events' in WP_AJAX, are the two methods the make the whole plugin work.
         * This is the one place where our Javascript calls Facebook events, and future modules that extend this
         * one can define their own methods for what to do once events have been returned.
         *
         * Also calls the 'ajax_update_wordpress_transient_cache' method after events have been returned
         *
         * @since     1.1.0
         *
         * @param options               array a bunch of options passed in from PHP.
         *                      mostly parameters for which events to get back or how to process them once we have them
         * @param ajax_events_gotten    function a passed-in method to execute once events have been returned.
         */
        var ajax_get_events =  function( options, ajax_events_gotten ) {

            options.limit = options.limit || 0;
            options.calendars = options.calendars || '';

            options.remove_events = options.remove_events || 1;
            options.whitelist = options.whitelist || 0;

            // Assign handlers immediately after making the request,
            // and remember the jqxhr object for this request
            var jqxhr = jQuery.post(
                options.ajax_url,
                {
                    action:         "get_events",
                    //this exists for the wp_nonce check
                    attr_id:        "event_list",
                    nonce:          jQuery("#event_list").data("nonce"),
                    remove_events:  options.remove_events,
                    transient_name: options.transient_name,

                    start:          options.start,
                    end:            options.end,
                    calendars:      options.calendars,
                    limit:          options.limit,
                    whitelist:      options.whitelist
                },

                function( data ) {

                    if(! data['success'] || typeof( ajax_events_gotten ) !== "function" ) {
                        alert("Ack! Problems getting your removed events back from the database.")
                    }

                    var events = data['response']['events'];

                    jQuery.each(events, function(i, event){
                        event.id = i+1;

                        if( parseInt(event.removed) === 1 ) {
                            event.removed = "removed";
                        }
                        else
                        if ( parseInt(event.modified) === 1 ){
                            event.removed = "modified";
                        }
                        else
                            event.removed = "display";
                    });

                    ajax_events_gotten( events );

                    /* This is a bit sneaky.  Update the cache for the next person. */
                    options.transient_name = data['transient_name'];

                    options.start       = data['start'];
                    options.end         = data['end'];
                    options.calendars   = data['calendars'];
                    options.limit       = data['limit'];

                    ajax_update_wordpress_transient_cache( options );

                    /*
                    if( data['if_cached'] ) {

                        console.log('data was gotten from cache');
                    }
                    else {

                        console.log('data was NOT cached');
                    }
                    */

                }, "json");
            /*.fail(function() {
             alert( "error" );
             })
             .always(function() {
             alert( "finished" );
             });*/
        };

        /**
         * This method, called after events have been returned, calls a php method which updates the cache.
         * Basically, events saved as WordPress transients are returned more quickly but are then stale by definition.
         * However, once we have the start, end, calendars, and limit, we can locate the transient, delete it,
         * and then make a new call to Facebook and cache those results right away, all while the unsuspecting user
         * So this method just passes all those parameters to the PHP method which then does the hard work.
         *
         * @since     1.0.0
         *
         * @param options
         */
        var ajax_update_wordpress_transient_cache = function( options ) {

            // Assign handlers immediately after making the request,
            // and remember the jqxhr object for this request
            options.attr_id = options.attr_id || 'event_list';

            var jqxhr = jQuery.post(
                options.ajax_url,
                {
                    action:         "update_wordpress_transient_cache",
                    attr_id:        options.attr_id,
                    nonce:          jQuery("#" + options.attr_id).data("nonce"),
                    transient_name: options.transient_name,

                    start:          options.start,
                    end:            options.end,
                    calendars:      options.calendars,
                    limit:          options.limit
                },

                function( data ) {

                    /*
                    if(! data['success']) {
                        console.log('WordPress transient DB has NOT been updated.');
                    }
                    else
                        console.log('Yay! WordPress transient DB has been updated.');
                    */

                }, "json");
            /*.fail(function() {
             alert( "error" );
             })
             .always(function() {
             alert( "finished" );
             });*/
        };

        /**
         * Figure out if the event has passed or not.
         *
         * @since     1.0.0
         *
         * @param event
         * @returns {boolean}
         */
        var is_upcoming_event = function( event ) {

            var current_timestamp = Math.floor((new Date()).getTime() );
            var event_timestamp = parseInt(event.start_time) * 1000;

            return ( event_timestamp > current_timestamp );
        };

        /**
         * Function takes a JS date object and returns '10:00 pm' or '3:00 am' or whatever.
         * Spoiler alert: I didn't write it.
         *
         * @author bbrame
         * @see http://stackoverflow.com/questions/8888491/how-do-you-display-javascript-datetime-in-12-hour-am-pm-format
         *
         * @param start     date the date for which we want the 12-hour formatted time
         *
         * @type {Function}
         * @private
         *
         * @since     1.1.3
         */
        var return_12_hour_AMPM_time_string = function( date ) {
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            minutes = minutes < 10 ? '0'+minutes : minutes;
            return hours + ':' + minutes + ' ' + ampm;
        };

        /**
         * @since     1.0.0
         */
        return {
            ajax_get_events: ajax_get_events,
            ajax_update_wordpress_transient_cache: ajax_update_wordpress_transient_cache,
            is_upcoming_event: is_upcoming_event,
            return_12_hour_AMPM_time_string: return_12_hour_AMPM_time_string
        };

    })();


