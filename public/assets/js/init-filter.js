
    var fJS;

    var AjaxEvents = (function () {

        //public method
        /**
         * @since     1.0.0
         *
         * @param options
         * @param ajax_events_gotten
         */
        var ajax_get_events =  function( options, ajax_events_gotten ) {

            options.limit = options.limit || 0;

            // Assign handlers immediately after making the request,
            // and remember the jqxhr object for this request
            var jqxhr = jQuery.post(
                options.ajax_url,
                {
                    action:         "get_events",
                    //this exists for the wp_nonce check
                    attr_id:        "event_list",
                    nonce:          jQuery("#event_list").data("nonce"),
                    remove_events:  1,
                    transient_name: options.transient_name,

                    start:          options.start,
                    end:            options.end,
                    calendars:      options.calendars,
                    limit:          options.limit
                    //we don't need this column because it defaults to false.
                    //whitelist: 0
                },

                function( data ) {

                    ///.log(data);

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

        //public method
        /**
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

                    if(! data['success']) {
                        console.log('WordPress transient DB has NOT been updated.');
                    }
                    else
                        console.log('Yay! WordPress transient DB has been updated.');


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

            return ( event_timestamp > current_timestamp ) ? true : false;
        }

        /**
         * @since     1.0.0
         */
        return {
            ajax_get_events: ajax_get_events,
            ajax_update_wordpress_transient_cache: ajax_update_wordpress_transient_cache,
            is_upcoming_event: is_upcoming_event
        };

    })();


