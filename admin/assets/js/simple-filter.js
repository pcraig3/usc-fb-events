jQuery(function ($) {
    /* You can safely use $ in this code block to reference jQuery */

    var fJS;

    $(document).ready(function($) {

        $('#removed :checkbox').prop('checked', true);

        ajax_get_events();
});

    /**
     * Function sets up our list in the backend.
     * Gets the eids of removed events and merges them with the data from Facebook.
     *
     * @param events    a JSON list of Facebook Events
     *
     * @since   0.4.0
     */
    function ajax_get_events() {

        // Assign handlers immediately after making the request,
        // and remember the jqxhr object for this request
        var jqxhr = $.post(
            ajax.url,
            {
                action:         "get_events",
                attr_id:        "event_list",
                nonce:          $("#event_list").data("nonce"),
                remove_events:  0,
                whitelist:      1

            },

            function( data ) {

                if(! data['success']) {
                    alert("Ack! Problems getting your removed events back from the database.")
                }

                var events = data['response']['events'];

                $.each(events, function(i, event){
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

