
    var fJS;

    var AjaxEvents = {
        /**
         * Function sets up our list in the backend.
         * Gets the eids of removed events and merges them with the data from Facebook.
         *
         * @param events    a JSON list of Facebook Events
         *
         * @since   0.9.6
         */
        ajax_get_events: function( options ) {

                var api_url = options.api_url || "testwestern.com/api/events/events/2014-04-01"; //@TODO: this date is sort of arbitrary
                var limit = options.limit || 0;

                // Assign handlers immediately after making the request,
                // and remember the jqxhr object for this request
                var jqxhr = jQuery.post(
                    options.ajax_url,
                    {
                        action:         "get_events",
                        api_url:        api_url,
                        attr_id:        "event_list",
                        nonce:          jQuery("#event_list").data("nonce"),
                        remove_events:  1
                        //we don't need this column because it defaults to false.
                        //whitelist: 0
                    },

                    function( data ) {

                        //console.log(data);

                        if(! data['success']) {
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

                        AjaxEvents.ajax_events_gotten( events, limit );

                    }, "json");
                /*.fail(function() {
                 alert( "error" );
                 })
                 .always(function() {
                 alert( "finished" );
                 });*/
            },

        /* @TODO: Duplicating this is pretty obvious bad practice */
        limit_events: function( events, limit ) {

                if(limit < 1)
                    return events;

                if( limit < events.length )
                    events = events.slice(0, limit);

                return events;
            },

        /* Figure out if the event has passed or not. */
        is_upcoming_event: function( event ) {

            var current_timestamp = Math.floor((new Date()).getTime() );
            var event_timestamp = parseInt(event.start_time) * 1000;

            return ( event_timestamp > current_timestamp ) ? true : false;
        }

    };


