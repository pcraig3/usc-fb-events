// Production steps of ECMA-262, Edition 5, 15.4.4.14
// Reference: http://es5.github.io/#x15.4.4.14

//(function ($) {

//})(jQuery);

/*
 var EOAjaxFront = {
 "adminajax":"http:\/\/westernusc.org\/wp-admin\/admin-ajax.php",
 "locale":{
 "locale":"en",
 "isrtl":false,
 "monthNames":["January","February","March","April","May","June","July","August","September","October","November","December"],
 "monthAbbrev":["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
 "dayNames":["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
 "dayAbbrev":["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],
 "ShowMore":"Show More",
 "ShowLess":"Show Less",
 "today":"today",
 "day":"day",
 "week":"week",
 "month":"month",
 "gotodate":"go to date",
 "cat":"View all categories",
 "venue":"View all venues",
 "tag":"View all tags",
 "nextText":">"
 ,"prevText":"<"
 }
 };
 var eventorganiser = {
 "ajaxurl":"http:\/\/westernusc.org\/wp-admin\/admin-ajax.php",
 "calendars":[
 {
 "headerleft":"prev,next today",
 "headercenter":"title",
 "headerright":"category",
 "defaultview":"month",
 "event-category":"",
 "event_category":"",
 "event-venue":"",
 "event_venue":"",
 "timeformat":"h:mm tt",
 "axisformat":"h:mm tt",
 "tooltip":true,
 "weekends":true,
 "mintime":"0",
 "maxtime":"24",
 "alldayslot":true,
 "alldaytext":"All Day",
 "columnformatmonth":"ddd",
 "columnformatweek":"ddd M\/d",
 "columnformatday":"dddd M\/d",
 "titleformatmonth":"MMMM yyyy",
 "titleformatweek":"MMM d[ yyyy]{ '&#8212;'[ MMM] d, yyyy}",
 "titleformatday":"dddd, MMM d, yyyy",
 "year":false,
 "month":false,
 "date":false,
 "users_events":false,
 "event_occurrence__in":[],
 "theme":true,
 "isrtl":false,
 "timeformatphp":"g:i a",
 "axisformatphp":"g:i a",
 "columnformatdayphp":"l n\/j",
 "columnformatweekphp":"D n\/j",
 "columnformatmonthphp":"D",
 "titleformatmonthphp":"F Y",
 "titleformatdayphp":"l, M j, Y",
 "titleformatweekphp":"M j[ Y]{ '&#8212;'[ M] j, Y}"
 }
 ],
 "widget_calendars":[],
 "fullcal":{
 "firstDay":1,
 "venues":[
 {
 "term_id":"560",
 "name":"Univeristy Community Centre",
 "slug":"univeristy-community-centre",
 "term_group":"0",
 "term_taxonomy_id":"560",
 "taxonomy":"event-venue",
 "description":"",
 "parent":"0",
 "count":"1",
 "venue_address":"",
 "venue_city":"",
 "venue_state":"",
 "venue_postcode":"",
 "venue_country":"",
 "venue_lat":"0.000000",
 "venue_lng":"0.000000"
 }
 ],
 "categories":{
 "570":{
 "term_id":"570",
 "name":"Clubs",
 "slug":"clubs-2",
 "term_group":"0",
 "term_taxonomy_id":"570",
 "taxonomy":"event-category",
 "description":"",
 "parent":"0",
 "count":"0",
 "color":"#cb87cd"
 },
 "566":{
 "term_id":"566",
 "name":"Faculty Councils",
 "slug":"faculty-councils",
 "term_group":"0",
 "term_taxonomy_id":"566",
 "taxonomy":"event-category",
 "description":"",
 "parent":"0",
 "count":"0",
 "color":"#f078d2"
 },
 "562":{
 "term_id":"562",
 "name":"USC",
 "slug":"usc-2",
 "term_group":"0",
 "term_taxonomy_id":"562",
 "taxonomy":"event-category",
 "description":"",
 "parent":"0",
 "count":"1",
 "color":"#5A4099"
 },
 "567":{
 "term_id":"567",
 "name":"Western",
 "slug":"western-2",
 "term_group":"0",
 "term_taxonomy_id":"567",
 "taxonomy":"event-category",
 "description":"",
 "parent":"0",
 "count":"0",
 "color":"#e99d12"
 }
 },
 "tags":[
 {
 "term_id":"565",
 "name":"Fun",
 "slug":"fun",
 "term_group":"0",
 "term_taxonomy_id":"565",
 "taxonomy":"event-tag",
 "description":"",
 "parent":"0",
 "count":"1"
 }
 ]
 },
 "map":[]
 };
 */
var eventorganiser = window.eventorganiser || {};
var EOAjaxFront = EOAjaxFront || {};
var AjaxEvents = AjaxEvents || {};

console.log(eventorganiser);
console.log(EOAjaxFront);

var AjaxFullCalendarList = (function ( options, AjaxEvents, eventorganiser, EOAjaxFront ) {

    //var $ = jQuery;
    var _eo_fullcalendar;
    var _eids = [];

    var _list_div;
    var _list_id = options.plugin_prefix + options.id;

    var _calendar_name = '';

    var _AjaxEvents =  AjaxEvents;
    var _eventorganiser = eventorganiser;
    var _locale = EOAjaxFront.locale;


    /**
     * Function inserts a newNode after another node.  This isn't a native JS function, but thankfully a guy on
     * StackOverflow has has this problem before.
     *
     * @see: http://stackoverflow.com/questions/4793604/how-to-do-insert-after-in-javascript-without-using-a-library
     * @author: karim79
     *
     * @param newNode           HTML node ready to insert
     * @param referenceNode     DOM node to insert the new node after
     * @private
     */
    var _insert_after = function (newNode, referenceNode) {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    };

    var _remove = (function (node_to_remove) {

        if( !node_to_remove || node_to_remove.length === null || node_to_remove.length <= 0)
            return false;

        //if d.length equals something, it means we have an array of nodes
        if ( typeof(node_to_remove) === "object" ) {

            if( node_to_remove.length ) {

                for (var index in node_to_remove) {
                    if (node_to_remove.hasOwnProperty(index) && node_to_remove[index].nodeType == 1)
                        _remove(node_to_remove[index]);
                }

            }
            else
                node_to_remove.parentNode.removeChild(node_to_remove);

        }

        return node_to_remove;
    });

    /**
     * function clears away any previous lists that may exist, and then creates a new list container <div>
     * and fills it with a new <ul> element.
     *
     * @type {Function}
     * @private
     */
    var _create_date_list = (function ( view ) {

        //remove existing list(s).
        _remove( _list_div );

        var __fragment = document.createDocumentFragment();

        _list_div = document.createElement("div");
        _list_div.id = _list_id;

        //the nonce will always be the same, right?  so no reason to keep the <div> and only remove the <li>s
        _list_div.setAttribute( "data-nonce", options.nonce );

        _list_div.appendChild( _create_date_list_items( view ) );
        __fragment.appendChild(_list_div);

        _insert_after(__fragment, _eo_fullcalendar);
    });

    var _create_date_list_items = (function ( view ) {

        var __date_list =  document.createElement("ul");
        __date_list.classList.add('dates');

        //okay, so now find the start and the end dates
        var __start     = new Date( view.start );
        var __end       = new Date( view.end );
        var __current   = new Date( view.start );
        var __today     = new Date();

        while( __current.getTime() < __end.getTime() ) {

            var __date_list_item = document.createElement('li');
            __date_list_item.classList.add('date');

            var __past_upcoming_or_today = ( __current.getDate() < __today.getDate() ) ? 'past' :
                ( __current.getDate() > __today.getDate() ) ? 'upcoming' : 'today';
            __date_list_item.classList.add( 'date-' + __past_upcoming_or_today );
            __date_list_item.classList.add( 'date-' + _return_ATOM_date_string_without_time( __current ) );
            __date_list_item.classList.add( 'clearfix' );
            __date_list_item.classList.add( 'hidden' );

            //data-date like the event-calendar uses: YYYY-mm-dd format
            __date_list_item.setAttribute( 'data-date', _return_ATOM_date_string_without_time( __current ) );
            __date_list_item.setAttribute( 'data-timestamp_start', __current.getTime().toString() );
            //set the data-timestamp_end below, after the date has been incremented

            //now create the title and the unordered list container
            var __date_title = document.createElement('h3');
            __date_title.classList.add("date__title");
            __date_title.innerHTML =  _return_day_name_day_of_month( __current );

            //no events as of yet.  We only get events once at a time
            var __event_list_container = document.createElement('ul');
            __event_list_container.classList.add('events');
            __event_list_container.classList.add('clearfix');

            __date_list_item.appendChild(__date_title);
            __date_list_item.appendChild(__event_list_container);

            __date_list.appendChild(__date_list_item);

            __current.setDate(__current.getDate() + 1);

            //set data-timestamp_end after the date has been incremented
            __date_list_item.setAttribute( 'data-timestamp_end', __current.getTime().toString() );
        }

        return __date_list;
    });

    //DATE_ATOM http://php.net/manual/en/class.datetime.php#datetime.constants.types
    var _return_day_name_day_of_month = (function( start ) {

        return start.getDate();
        //we want to return Monday 6
        //return _locale.dayNames[start.getDay()] + ' ' + start.getDate();

    });

    //DATE_ATOM http://php.net/manual/en/class.datetime.php#datetime.constants.types
    var _return_ATOM_date_string_without_time = (function( start ) {

        //we want to return YYYY-mm-dd
        __date = [];
        __date.push( start.getFullYear() );
        __date.push( start.getMonth() + 1);
        __date.push( start.getDate() );

        return __date.join('-');
    });

    var _return_12_hour_AMPM_time_string = (function(date) {
        var hours = date.getHours();
        var minutes = date.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0'+minutes : minutes;
        return hours + ':' + minutes + ' ' + ampm;
    });

    /**
     * Nothing too glamourous.  Function creates a list item for our mobile events list.
     *
     * @type {Function}
     * @private
     */
    var _create_event_item = (function( event, view ) {

        //first, make sure we don't have any duplicates by pushing facebook events into an array.
        //if not a facebook event, then who cares.
        if( typeof( event.eid ) !== "undefined" ) {

            if( _eids.indexOf( event.eid) >= 0 )
                return false;

            _eids.push( event.eid );
        }


        //basically, create a list item with the event name and attach it to the corresponding list

        //okay, so get the midnight date.

        var __list_item = document.createElement('li');

        var __start_date = new Date( event.start );
        var __start_date_midnight = new Date( event.start );
        __start_date_midnight.setHours( 0, 0, 0, 0 );

        var __end_date = new Date( event.end );
        var __end_of_month = new Date( view.end );

        if( !event.fbDescription )
            event.fbDescription = event.description;

        //http://stackoverflow.com/questions/17264182/javascript-efficiently-insert-multiple-html-elements

        __list_item.className = event.className.join(' ');
        __list_item.className += " eo-fb-eid-" + event.eid + " eo-fb-event-list";//fb-four-fifth";
        __list_item.setAttribute( 'data-timestamp_start', __start_date.getTime().toString() );
        __list_item.setAttribute( 'data-timestamp_end', __end_date.getTime().toString() );

        //now we fill the list item with stuff
        var __prefix = "event__info event__";

        //keep the name, host, and time in here
        var __visible_div = document.createElement('div');

        //keep the description, location, link and tickets (if any) in here.
        var __hidden_div = document.createElement('div');
        __hidden_div.className = "meta hidden"

        var __event_atts = {
            'start':            __visible_div,
            'title':            __visible_div,
            'host':             __visible_div,
            'location':         __hidden_div,
            'fbDescription':    __hidden_div,
            'url':              __hidden_div,
            'ticket_uri':       __hidden_div
        };

        for (var key in __event_atts) {
            if (__event_atts.hasOwnProperty(key)) {
                _create_event_info_span_and_append_to_element( event, key, __prefix, __event_atts[key] );
            }
        }

        __list_item.appendChild(  __visible_div.cloneNode(true) );
        __list_item.appendChild(  __hidden_div.cloneNode(true) );

        //__start_string_midnight.
        var __event_list_classes = [];

        do {

            //first day is a freebie because it's the start_date
            __event_list_classes.push( '#' + _list_id + ' .date-' + _return_ATOM_date_string_without_time( __start_date_midnight ) + ' .events' );

            __start_date_midnight.setDate(__start_date_midnight.getDate() + 1);

        } while ( __end_date.getTime() > __start_date_midnight.getTime() && __start_date_midnight.getTime() < __end_of_month.getTime() );

        var __event_list_array = document.querySelectorAll( __event_list_classes.join(', ') );

        var max = __event_list_array.length;
        for (var i = 0; i < max; i++) {

            //because the max value is the total number of days
            var item_to_add = _format_start_date_return_list_item( __list_item.cloneNode(true), (i + 1), max );
            item_to_add.querySelector('.event__title').addEventListener('click', _toggle_hidden_div_onclick );
            _format_event_urls( item_to_add, '.event__url' );
            _add_classes_to_classes_in_list_item( item_to_add, [ '.event__title', '.event__url a' ], [ 'category__color', 'fade_on_hover', 'etmodules' ] );
            __event_list_array[i].appendChild( item_to_add );

            //reveal the node. *wink*
            __event_list_array[i].parentNode.classList.remove( 'hidden' );
        }

        return event;
    });

        var _format_start_date_return_list_item = (function( list_item, index, number_of_days ) {

        var start_span = list_item.querySelector('.event__start');
        var start_date = new Date( parseInt( list_item.getAttribute( 'data-timestamp_start' ) ) );
        var end_date  = new Date( parseInt( list_item.getAttribute( 'data-timestamp_end' ) ) );
        var time_string = '';
        var day_string = '';

        if(number_of_days === 1) {

            time_string = _return_12_hour_AMPM_time_string( start_date );
        }
        else if( index === number_of_days ) {

            //time_date_string = "Ends at " + _return_12_hour_AMPM_time_string( end_date ) + " | Final Day";
            time_string = "Until </br>" +  _return_12_hour_AMPM_time_string( end_date );
            day_string = 'Final Day';
        }
        else {

            //get the time if it starts today //start at one because we incremented index when it was passed in
            if( index === 1 )
                //time_date_string = "Starts at " + _return_12_hour_AMPM_time_string( start_date ) + " | ";
                time_string = _return_12_hour_AMPM_time_string( start_date );


            if( time_string === '' )
                time_string = 'Ongoing';

            day_string =  'Day ' + index;
            //time_date_string += "Day " + index + " of " + number_of_days;
        }

        start_span.innerHTML = time_string;
        return _add_days_to_title_for_ongoing_events_and_return_list_item( list_item, day_string );
    });

    var _add_days_to_title_for_ongoing_events_and_return_list_item = (function( list_item, day_string ) {

        if( !day_string )
            return list_item;

        var title_span = list_item.querySelector('.event__title');

        title_span.innerHTML = title_span.innerHTML + ' (' + day_string + ')';
        return list_item;
    });

    var _format_event_urls = (function( list_item, class_to_format ) {

        //we want them to say 'View Event' with an optional facebook class.
        var to_format = list_item.querySelector(class_to_format);

        if( !to_format )
            return list_item;

        var link = document.createElement('a');
        link.target="_blank";
        link.href = to_format.innerHTML;

        var if_facebook = list_item.classList.contains('eo-fb-event');

        link.title = 'Learn more about this event';
        link.innerHTML = 'View Event';

        if( if_facebook ) {
            link.title += ' on Facebook';
            link.innerHTML += ' on Facebook';
            link.classList.add('facebookUrl');
        }

        link.title += '.';

        to_format.innerHTML = '';
        to_format.appendChild(link);

        return list_item;
    });



    var _add_classes_to_classes_in_list_item = (function( list_item, classes_for_which_to_add_more_classes, additional_classes ) {

        var max_classes_to_add_to = classes_for_which_to_add_more_classes.length;

        for(var i = 0; i < max_classes_to_add_to; i++) {
            //so this selects only ONE element in the list node.  More logic would be needed to one or more elements
            var temp = list_item.querySelector(classes_for_which_to_add_more_classes[i]);
            if ( temp ) {

                var max_additional_classes = additional_classes.length;

                for(var j = 0; j < max_additional_classes; j++)
                    temp.classList.add(additional_classes[j]);
            }
        }

        return list_item;
    });

    var _create_event_info_span_and_append_to_element = (function( event, attribute, __prefix, to_append ) {

        if( event[attribute] ) {
            var temp = document.createElement('span');
            temp.className = __prefix + attribute;
            temp.innerHTML = event[attribute];
            to_append.appendChild( temp.cloneNode( true ) );
            return temp;
        }

        return false;
    });

    /**
     * function collects the values that AjaxEvents needs to run its
     * ajax_update_wordpress_transient_cache method and then calls it.
     *
     * If all goes well, we'll update our WP cache and everything will go FASTER.
     *
     * @type {Function}
     * @private
     */
    var _ajax_update_wordpress_transient_cache = (function() {

        /*var ajax_update_wordpress_transient_cache = function( options ) {

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
         */
        var ajax_options = {};

        ajax_options.ajax_url = options.ajax_url;
        ajax_options.attr_id = options.id;

        //var days = document.querySelectorAll( '.fc-day:not(.fc-other-month)' );
        var days = document.querySelectorAll( '.fc-day' );

        ajax_options.start = days[0].getAttribute('data-date');
        ajax_options.end = days[days.length - 1].getAttribute('data-date');
        ajax_options.calendars = _get_calendars_as_string();
        ajax_options.limit = 0;

        console.log(ajax_options);

        _AjaxEvents.ajax_update_wordpress_transient_cache( ajax_options );

    });

    /**
     * function that returns a calendars string compatible with the API.
     * works whether categories have been specified or not
     *
     * @type {Function}
     * @private
     *
     * @return   string comma-separated calendar names
     */
    var _get_calendars_as_string = (function () {

        //this is all of the categories that we know about
        var all_categories = _eventorganiser.fullcal.categories;

        //if this is valid, it means that we've picked certain categories to include on the calendar
        var we_want_all_categories = ( _eventorganiser.calendars[0].event_category === '' );
        var categories_used = _eventorganiser.calendars[0].event_category.split(',');//: "faculty-councils,usc-2"

        var calendar_names = [];

        //for each of our categories (if we don't want them all) check if they are in our categories_used array before returning them
        for (var i in all_categories) {
            if ({}.hasOwnProperty.call(all_categories, i))
                if( we_want_all_categories || categories_used.indexOf( all_categories[i].slug ) >= 0 )
                    calendar_names.push( all_categories[i].name.replace(' ', '%20').toLowerCase() );
        }

        return calendar_names.join();
    });

    /*
     Basic event-adding function.  Most of the code stolen from:
     http://jsperf.com/jquery-click-event-vs-pure-javascript/2
     */
    window.onload = function() {
        //whenever you click the calendar buttons, remove existing list events
        var buttons = document.querySelectorAll('.fc-button:not( .fc-button-today )');
        for (var i in buttons) {
            if ( buttons.hasOwnProperty(i) && buttons[i].nodeType == 1 )
                buttons[i].addEventListener('click', function(event) {

                    //remove existing list items when calendar is pressed.
                    _remove( document.querySelectorAll( '#' + _list_id + ' li' ) );
                    _calendar_name = "";
                    while( _eids.length )
                        _eids.pop();

                });
        }
    };

    var _toggle_hidden_div_onclick = (function() {

        var el = this;

        while( !el.classList.contains('eo-fb-event-list') && el !== null )
            el = el.parentNode;

        if( !el ) {
            console.err('Yikes!  Couldn\'t find the list item in question.  Sorry!');
            return false;
        }


        var meta_div = el.querySelector('.meta');

        var is_hidden = meta_div.classList.contains('hidden');

        if( is_hidden ) {

            this.classList.add('clicked');
            meta_div.classList.remove('hidden');
        }

        else {

            this.classList.remove('clicked');
            meta_div.classList.add('hidden');
        }
    });

    var run_once_per_calendar = (function( view ) {

        if( _calendar_name !== view.title ) {

            _eo_fullcalendar = document.querySelector('.eo-fullcalendar');

            _create_date_list( view );

            _calendar_name = view.title;

            _ajax_update_wordpress_transient_cache();
        }
    });

    var run_each_event = (function( event, view ) {

        _create_event_item( event, view );
    });

    return {
        run_once_per_calendar: run_once_per_calendar,
        run_each_event: run_each_event
    };

})(options, AjaxEvents, eventorganiser, EOAjaxFront );


//var AjaxEvents;// =

/*
 jQuery('.eo-fullcalendar').on('click', function() {

 jQuery(this).hide();
 });
 */

window.wp.hooks.addFilter( 'eventorganiser.fullcalendar_render_event', function( bool, event, element, view ){

    console.log(bool);
    console.log(event);
    //console.log(element);
    console.log(view);

    //okay, so we have the time.  And, if this makes a difference, the time difference is off.

    //pass in the title of the calendar. if it changes, we update.
    AjaxFullCalendarList.run_once_per_calendar( view );
    AjaxFullCalendarList.run_each_event( event, view );

    return bool;
}, 4 );

(function add_category_css_to_head( eventorganiser ) {

    //all of the categories that we know about
    var all_categories = eventorganiser.fullcal.categories;
    var categories_css_string = '';

    //for each of our categories (if we don't want them all) check if they are in our categories_used array before returning them
    for (var i in all_categories) {
        if ({}.hasOwnProperty.call(all_categories, i))
            categories_css_string += ' .category-' + all_categories[i].slug + ' .category__color { color: ' + all_categories[i].color + ' } \n';
    }

     head = document.head || document.getElementsByTagName('head')[0];
     style = document.createElement('style');

     style.type = 'text/css';
     if (style.styleSheet){
     style.styleSheet.cssText = categories_css_string;
     } else {
     style.appendChild(document.createTextNode(categories_css_string));
     }

     head.appendChild(style);
})( eventorganiser );
