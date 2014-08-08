
    var fJS;

    var AjaxEvents = {


        ajax_get_events: function( options ) {

            options.api_url = options.api_url || "testwestern.com/api/events/events/2014-04-01"; //@TODO: this date is sort of arbitrary
            options.limit = options.limit || 0;

            // Assign handlers immediately after making the request,
            // and remember the jqxhr object for this request
            var jqxhr = jQuery.post(
                options.ajax_url,
                {
                    action:         "get_events",
                    api_url:        options.api_url,
                    //this exists for the wp_nonce check
                    attr_id:        "event_list",
                    nonce:          jQuery("#event_list").data("nonce"),
                    remove_events:  1,
                    transient_name: options.transient_name
                    //we don't need this column because it defaults to false.
                    //whitelist: 0
                },

                function( data ) {

                    console.log(data);

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

                    AjaxEvents.ajax_events_gotten( events, options.limit );

                    if( data['if_cached'] ) {

                        console.log('data was gotten from cache');
                    }
                    else {

                        console.log('data not cached');
                        AjaxEvents.ajax_update_wordpress_transient_cache( options );
                    }


                }, "json");
            /*.fail(function() {
             alert( "error" );
             })
             .always(function() {
             alert( "finished" );
             });*/
        },


        ajax_update_wordpress_transient_cache: function( options ) {

            console.log(options);

            // Assign handlers immediately after making the request,
            // and remember the jqxhr object for this request
            var jqxhr = jQuery.post(
                options.ajax_url,
                {
                    action:         "update_wordpress_transient_cache",
                    api_url:        options.api_url,
                    attr_id:        "event_list",
                    nonce:          jQuery("#event_list").data("nonce"),
                    transient_name: options.transient_name
                },

                function( data ) {

                    console.log('<pre>');
                    console.log(data);
                    console.log('</pre>');

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
        },

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


