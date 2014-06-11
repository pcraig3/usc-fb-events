jQuery(function ($) {
    /* You can safely use $ in this code block to reference jQuery */

    var fJS;

    $(document).ready(function($) {

        $('#removed :checkbox').prop('checked', true);

        //@TODO: This sucks
        $.get( "http://testwestern.com/api/events/events/2014-04-01")
            .done(function( data ) {

                var events = data.events;

                ajax_get_removed_events(events);
            });

    });

    /**
     * Function sets up our list in the backend.
     * Gets the eids of removed events and merges them with the data from Facebook.
     *
     * @param events    a JSON list of Facebook Events
     *
     * @since   0.4.0
     */
    function ajax_get_removed_events(events) {

        // Assign handlers immediately after making the request,
        // and remember the jqxhr object for this request
        var jqxhr = $.post(
            "admin-ajax.php",

            { action: "get_removed_events" },

            function( data ) {

                if(! data['success']) {
                    alert("Ack! Problems getting your removed events back from the database.")
                }

                var eids = [];
                var total = data['response'].length;

                for (var i = 0; i < total; i++) {

                    eids.push( parseInt(data['response'][i]['eid']) );
                }

                $.each(events, function(i, e){
                    e.id = i+1;

                    if( eids.indexOf( parseInt(e.eid) ) >= 0 ) {
                        e.removed = "removed";
                    }
                    else {
                        e.removed = "display";
                    }
                });

                fJS = filterInit( events );

                $('#event_list').trigger( "change" );

        }, "json");
            /*.fail(function() {
                alert( "error" );
            })
            .always(function() {
                alert( "finished" );
            });*/
    }

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

        var view = function( events ){
            var html_string = "<div class='row ";
            if(events.removed === "removed")
                html_string += " removed ";

            html_string += "' data-eid='" + events.eid + "' data-start-time='" + events.start_time + " '>" +
                "<span class='name'>" + events.name + "</span>" +
                "<span class='host'>" + events.host + "</span>" +
                "<span class='removed'>" + events.removed + "</span>" +
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

});

