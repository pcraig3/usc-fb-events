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

//console.log(eventorganiser);
//console.log(EOAjaxFront);

/**
 * Big arse bloody JS module almost totally responsible for the mobile event list under the fullcalendar
 * as well as the written almost in pure JavaScript.
 *
 * The AjaxFullCalendarList object creates a listing of dates, fills it with events, and builds its own header
 * for the fullcalendar view on mobile screens
 *
 * @param options       localised variables defined in class-usc-fb-events.php in USC_FB_EVENTS::event_organiser_mobile_view_for_fullcalender()
 * @param AjaxEvents    JS module defined in init-filter.js.  Has a method lets us cache the response from Facebook
 *
 * @since     1.1.0
 */
var AjaxFullCalendarList = (function ( options, AjaxEvents, eventorganiser, EOAjaxFront ) {

    //variable containing the .eo-fullcalendar html object
    var _eo_fullcalendar;
    //array of event ids already put on the mobile event list.  starts empty
    var _ids = [];

    //div containing the mobile events list.  Starts empty.
    var _list_div;
    //id for the div containing the mobile events list
    var _list_id = options.plugin_prefix + options.id;

    //div containing the mobile header
    var _mobile_header_div;
    //id for the div containing the mobile header
    var _mobile_header_id = options.plugin_prefix + 'fullcalendar__list__header';

    //name of the current calendar (for example, "September 2014")
    var _calendar_name = '';

    var _AjaxEvents =  AjaxEvents;
    var _eventorganiser = eventorganiser;
    //this would use useful if we wanted month names or something, which we never did.
    var _locale = EOAjaxFront.locale;

    //variable that lets us know if we should reset the calendar the next time we get an event
    var _reset_calendar = true;


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
     *
     * @since     1.1.0
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
     * function clears away any previous mobile header divs that may exist, and then creates a mobile header
     * container <div> and fills it with a title and some buttons
     *
     * @param view      the current view object for the calendar.  has the start and end times for the month.
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _create_mobile_header = (function ( view ) {

        var if_sticky = false;

        /*
        basically, if we find a sticky-wrapper on the mobile_header, it's been stuck using the jquery-sticky
        plugin, so we have to unstick it to remove the .sticky-wrapper element inserted by the plugin
         */
        if( _mobile_header_div )
            if( _mobile_header_div.parentNode )
                if( _mobile_header_div.parentNode.classList.contains('sticky-wrapper') )
                    if_sticky = true;

        //rare use of jQuery
        if( if_sticky )
            jQuery( _mobile_header_div ).unstick();

        //remove existing mobile header(s).
        _remove( _mobile_header_div );

        var __start     = new Date( view.start );
        var __today     = new Date();
        var __fragment  = document.createDocumentFragment();

        _mobile_header_div = document.createElement("div");
        _mobile_header_div.id = _mobile_header_id;

        //method sets title of mobile header to current month and year (or whatever else your title is)
        _mobile_header_div = _add_or_update_mobile_header_title( _mobile_header_div );

        //add a 'back to top' button to the sticky header.  Only revealed on smaller screens after the user has started scrolling.
        var class_button_top = 'usc_fc_events_header__button__top';
        _mobile_header_div = _add_button_top( _mobile_header_div, class_button_top );
        _event_handler_reveal_button_when_list_touches_top_of_browser( _mobile_header_div, class_button_top );

        //only show the 'jump to today button if it's the current month
        if(     __start.getFullYear() === __today.getFullYear()
            &&  __start.getMonth() === __today.getMonth() ) {

            var class_button_date_today = 'usc_fc_events_header__button__date_today';
            _mobile_header_div = _add_button_todays_date( _mobile_header_div, class_button_date_today );
            _event_handler_hide_button_when_list_touches_top_of_browser( _mobile_header_div, class_button_date_today );
        }

        __fragment.appendChild(_mobile_header_div);

        _eo_fullcalendar.parentNode.insertBefore(__fragment, _eo_fullcalendar);
    });

    /**
     * function that sets the calendar title for the mobile event listing based on the title on the
     * real (vanilla) fullcalendar.  Takes the innerHTML of the div containing the title, so it includes
     * the header tags.  For example, we've been taking a string like '<h2>September 2014</h2>' for our title
     *
     * @param _mobile_header_div  node the mobile header where we need to append our new title div
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _add_or_update_mobile_header_title = (function ( _mobile_header_div ) {

        //remove existing title whether it exists or not
        _remove( _mobile_header_div.querySelector('.usc_fb_events_header__title') );

        var _mobile_header_title_inner_HTML = '';
        var _header_title_element = document.querySelector('.fc-header-title');
        if( _header_title_element )
            if( _header_title_element.innerHTML )
                _mobile_header_title_inner_HTML = _header_title_element.innerHTML;

        var _mobile_header_title = document.createElement("span");

        //maybe isolate this if you want some more control over the header class
        _mobile_header_title.classList.add('usc_fb_events_header__title');

        //might be some weird shit going on here.
        _mobile_header_title.innerHTML = ( _mobile_header_title_inner_HTML ) ? _mobile_header_title_inner_HTML : _calendar_name;

        _mobile_header_div.appendChild(_mobile_header_title);

        //exploiting the fact that inserting an element already on the DOM removes it from its current location.
        _mobile_header_div.insertBefore(_mobile_header_title, _mobile_header_div.firstChild);

        return _mobile_header_div;
    });

    /**
     * function adds a 'back to top' button to the header.  Button is initially hidden, meant only to be
     * revealed on smaller screens.  Uses the event-organiser classes and icons so that it looks similar to the
     * other navigation buttons.
     *
     * @param _mobile_header_div    node the mobile_header_div where to attach our new button
     * @param button_class          string the className of our new button
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _add_button_top = (function ( _mobile_header_div, button_class ) {

        _remove( _mobile_header_div.querySelector('.' + button_class ) );

        var _mobile_header_button_top = document.createElement("a");
        //main-content

        //I've hardcoded this because of a lack of reliable element ids in the eo-calendar
        var button_href = '#fb_usc_events_fullcalendar__back_to_top';

        _mobile_header_button_top.href = button_href;
        _mobile_header_button_top.title = 'Back to top.';
        _mobile_header_button_top.className = 'fc-button ui-state-default hidden'; // ui-state-disabled';
        _mobile_header_button_top.classList.add(button_class);

        //creates a button with an icon pointing 'up';
        _mobile_header_button_top.innerHTML = '<span class="fc-button-content"><span class="fc-icon-wrap"><span class="ui-icon .ui-icon-arrow-1-s"></span></span></span>';

        _mobile_header_div.appendChild( _mobile_header_button_top );

        return _mobile_header_div;
    });

    /**
     * function adds a 'jump to today' button to the header.  Button is initially hidden, meant only to be
     * revealed on smaller screens.  Uses the event-organiser classes and icons so that it looks similar to the
     * other navigation buttons.
     *
     * @param _mobile_header_div    node the mobile_header_div where to attach our new button
     * @param button_class          string the className of our new button
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _add_button_todays_date = (function ( _mobile_header_div, button_class ) {

        _remove( _mobile_header_div.querySelector('.' + button_class ) );

        var _mobile_header_button_date_today = document.createElement("a");
        //main-content

        var button_href = '#usc_fb_events_fullcalendar__date__today';

        _mobile_header_button_date_today.href = button_href;
        _mobile_header_button_date_today.title = 'Jump to today\'s date.';
        _mobile_header_button_date_today.className = 'fc-button ui-state-highlight';// ui-state-disabled';
        _mobile_header_button_date_today.classList.add( button_class );

        //creates a button with a downward-pointing triangle in a circle
        _mobile_header_button_date_today.innerHTML = '<span class="fc-button-content"><span class="fc-icon-wrap"><span class="ui-icon ui-icon-circle-triangle-s"></span></span></span>';

        _mobile_header_div.appendChild( _mobile_header_button_date_today );

        return _mobile_header_div;
    });

    /**
     * Small method creates a window.scroll event that tracks where the top of the event list is in relation to the top
     * of the viewport.  Our button will be reveale when the list touches the top of or moves up
     * under the top of the viewport
     *
     * @param _mobile_header_div    the mobile header div which contains our buttons
     * @param class_button          the class on the element (ie, button) to reveal
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _event_handler_reveal_button_when_list_touches_top_of_browser
        = (function( _mobile_header_div, class_button ) {

        var button = _mobile_header_div.querySelector('.' + class_button);

        jQuery( window ).scroll(function() {
            //if the header

            var _list = document.querySelector('#' + _list_id );

            ( _list.getBoundingClientRect().top < 0 )
                ?   button.classList.remove('hidden')
                :   button.classList.add('hidden');

        });
    });

    /**
     * Small method creates a window.scroll event that tracks where the top of the event list is in relation to the top
     * of the viewport.  Our button will be HIDDEN when the list touches or
     * moves up under the top of the viewport
     *
     * @param _mobile_header_div    the mobile header div which contains our buttons
     * @param class_button          the class on the element (ie, button) to HIDE
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _event_handler_hide_button_when_list_touches_top_of_browser
        = (function( _mobile_header_div, class_button ) {

        var button = _mobile_header_div.querySelector('.' + class_button);

        jQuery( window ).scroll(function() {
            //if the header

            var _list = document.querySelector('#' + _list_id );

            ( _list.getBoundingClientRect().top < 0 )
                ?   button.classList.add('hidden')
                :   button.classList.remove('hidden');

        });
    });

    /**
     * function clears away any previous lists that may exist, and then creates a new list container <div>
     * and fills it with a new <ul> element.
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _create_date_list = (function ( view ) {

        //remove existing list(s).
        _remove( _list_div );

        var __fragment = document.createDocumentFragment();

        _list_div = document.createElement("div");
        _list_div.id = _list_id;

        //our nonce is generated in class-usc-fb-events.php and then passed in as a localised variable
        _list_div.setAttribute( "data-nonce", options.nonce );

        _list_div.appendChild( _create_date_list_items( view ) );
        __fragment.appendChild(_list_div);

        _insert_after(__fragment, _eo_fullcalendar);
    });

    /**
     * This function is pretty fun.
     * Okay, so basically, we never know how many events are coming -- we only get notified once per event.
     * So it doesn't make sense to build try and build a list of events based on how many events there are.
     * Rather, the first time through we just build a list of all dates in a month and hide them all.
     * Then, when we get events, we can use their start times and end times to find the appropriate list
     * items to slot themselves under.
     * A 'date' item is an <li> element with a <h3> date_title element and an empty <ul> events element
     *
     * @param view  object what knows the start and end of the month
     *
     * @type {Function}
     * @private
     *
     *
     * @since     1.1.0
     */
    var _create_date_list_items = (function ( view ) {

        var __date_list =  document.createElement('ul');
        __date_list.classList.add('dates');

        //okay, so now find the start and the end dates
        //var __start     = new Date( view.start );  //didn't need this, I guess
        var __end       = new Date( view.end );
        var __current   = new Date( view.start );
        var __today     = new Date();

        //while the current time is past the end of the month
        while( __current.getTime() < __end.getTime() ) {

            var __date_list_item = document.createElement('li');
            __date_list_item.classList.add('date');

            var __past_upcoming_or_today = 'today';

            //if last year, or last month, or before today's date
            if(     __current.getFullYear() < __today.getFullYear()
                ||  __current.getMonth() < __today.getMonth()
                ||  __current.getDate() < __today.getDate() )
                __past_upcoming_or_today = 'past';

            //if next year, or next month, or after today's date
            else if(    __current.getFullYear() > __today.getFullYear()
                ||      __current.getMonth() > __today.getMonth()
                ||      __current.getDate() > __today.getDate() )
                __past_upcoming_or_today = 'upcoming';

            __date_list_item.classList.add( 'date-' + __past_upcoming_or_today );
            __date_list_item.classList.add( 'date-' + _return_ATOM_date_string_without_time( __current ) );
            __date_list_item.classList.add( 'clearfix' );
            //never hide today's date in the mobile listing
            if( __past_upcoming_or_today !== 'today' )
                __date_list_item.classList.add( 'hidden' );

            else {
                //add an ID that our 'jump-to-today' button can latch onto
                __date_list_item.id = 'usc_fb_events_fullcalendar__date__today';
                //if this item has no events under it, it needs a bottom border
                __date_list_item.classList.add('bottom-border')
            }

            //data-date like the event-calendar uses: YYYY-mm-dd format
            __date_list_item.setAttribute( 'data-date', _return_ATOM_date_string_without_time( __current ) );
            __date_list_item.setAttribute( 'data-timestamp_start', __current.getTime().toString() );

            //now create the title and the unordered list container
            var __date_title = document.createElement('h3');
            __date_title.classList.add("date__title");
            __date_title.innerHTML =  _return_day_of_month( __current );

            //no events as of yet.  We only get events once at a time
            var __event_list_container = document.createElement('ul');
            __event_list_container.classList.add('events');
            __event_list_container.classList.add('clearfix');

            __date_list_item.appendChild(__date_title);
            __date_list_item.appendChild(__event_list_container);

            __date_list.appendChild(__date_list_item);

            //increment the current date by 1 day's worth
            __current.setDate(__current.getDate() + 1);

            //set data-timestamp_end after the date has been incremented
            __date_list_item.setAttribute( 'data-timestamp_end', __current.getTime().toString() );
        }

        return __date_list;
    });

    /**
     * Super-simple function returns the day of the month as a number
     *
     * @param start     date the date for which we just want the day number
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _return_day_of_month = (function( start ) {

        //we want to return 6
        return start.getDate();
        //we want to return Monday 6
        //return _locale.dayNames[start.getDay()] + ' ' + start.getDate();

    });

    //DATE_ATOM http://php.net/manual/en/class.datetime.php#datetime.constants.types
    /**
     * Still pretty-simple function returns the date as YYYY-mm-dd, or 'Y-m-d' in phpspeak
     *
     * @param start     date the date for which we want the formatted date
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _return_ATOM_date_string_without_time = (function( start ) {

        //we want to return YYYY-mm-dd
        __date = [];
        __date.push( start.getFullYear() );
        __date.push( start.getMonth() + 1);
        __date.push( start.getDate() );

        return __date.join('-');
    });

    /**
     * Heart and soul of our whole mobile event list.  Function creates an event item for each event,
     * and formats them and handles multi-day events and all that fun stuff
     *
     * @param event     the event to add to the mobile events list
     * @param view      the view information, with the start/end dates and tons of other stuff we don't use
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _create_event_item = (function( event, view ) {

        //first, make sure we don't have any duplicates by pushing events into an array. because we used to
        //id is auto generated by the fullcalendar or something: both facebook events and EO events will have one
        if( typeof( event._id ) !== "undefined" ) {

            if( _ids.indexOf( event._id) >= 0 )
                return false;

            _ids.push( event._id );
        }

        var __list_item = document.createElement('li');

        var __start_date = new Date( event.start );

        //okay, so get the midnight date.
        var __start_date_midnight = new Date( event.start );
        __start_date_midnight.setHours( 0, 0, 0, 0 );

        var __end_date = new Date( event.end );
        var __end_of_month = new Date( view.end );

        //non-facebook events won't have this.  EO events will.
        if( !event.fbDescription )
            event.fbDescription = event.description;

        __list_item.className = event.className.join(' ');
        __list_item.className += " eo-fb-eid-" + event.eid + " eo-fb-event-list";//fb-four-fifth";
        __list_item.setAttribute( 'data-timestamp_start', __start_date.getTime().toString() );
        __list_item.setAttribute( 'data-timestamp_end', __end_date.getTime().toString() );

        var __prefix = "event__info event__";

        //keep the name, host, and time in here
        var __visible_div = document.createElement('div');

        //keep the description, location, link and tickets (if any) in here.
        var __hidden_div = document.createElement('div');
        __hidden_div.className = "meta event-meta-closed";
        //initially hide this event with the style tag so that we can use jQuery slideToggle to reveal it
        __hidden_div.style.display = 'none';

        var __event_atts = {
            'start':            __visible_div,
            'title':            __visible_div,
            'host':             __visible_div,
            'location':         __hidden_div,
            'fbDescription':    __hidden_div,
            'url':              __hidden_div,
            'ticket_uri':       __hidden_div
        };

        //this little method quickly fills our events with spans with classnames and whatever content.
        //a little further down we have a lot of other methods that have to fix much of the innerHTML.
        //as seen in http://stackoverflow.com/questions/17264182/javascript-efficiently-insert-multiple-html-elements
        for (var key in __event_atts) {
            if (__event_atts.hasOwnProperty(key)) {
                _create_event_info_span_and_append_to_element( event, key, __prefix, __event_atts[key] );
            }
        }

        __list_item.appendChild(  __visible_div.cloneNode(true) );
        __list_item.appendChild(  __hidden_div.cloneNode(true) );

        //__start_string_midnight.
        var __event_list_classes = [];

        //this loop is super cool.  Find all of the classnames of the dates we're going to have to push our event into.
        //normal events will just need the one date, but multi-day events span several days.
        do {

            //first day is a freebie because it's the start_date
            __event_list_classes.push( '#' + _list_id + ' .date-' + _return_ATOM_date_string_without_time( __start_date_midnight ) + ' .events' );

            __start_date_midnight.setDate(__start_date_midnight.getDate() + 1);

        } while ( __end_date.getTime() > __start_date_midnight.getTime() && __start_date_midnight.getTime() < __end_of_month.getTime() );

        var __event_list_array = document.querySelectorAll( __event_list_classes.join(', ') );

        var max = __event_list_array.length;
        for (var i = 0; i < max; i++) {

            /* basically all of these functions modify some span's innerHTML because of the crude way we filled them up before */

             //because the max value is the total number of days
            var item_to_add = _format_start_date_return_list_item( __list_item.cloneNode(true), (i + 1), max );
            item_to_add = _format_location_return_list_item( item_to_add );
            /* item_to_add.querySelector('.event__title').addEventListener('click', _toggle_hidden_div_onclick ); */
            _format_event_urls( item_to_add, '.event__url', 'view' );
            _format_event_urls( item_to_add, '.event__ticket_uri', 'ticket' );
            _add_classes_to_classes_in_list_item( item_to_add, [ '.event__title', '.event__url a', '.event__ticket_uri a' ], [ 'category__color', 'fade_on_hover', 'etmodules' ] );
            __event_list_array[i].appendChild( item_to_add );

            //reveal the node. *wink*
            //date nodes are hidden until they have at least one event
            __event_list_array[i].parentNode.classList.remove( 'hidden' );

            //this is more obscure, but basically, if our 'today' date has no events under it, it needs a bottom border
            //if this is today's node and we're giving it an event, we can remove the bottom border pretty safely (or
            //the class that applies it).  If we remove a class that's not there, no problems.
            __event_list_array[i].parentNode.classList.remove( 'bottom-border' );
        }

        return event;
    });

    /**
     * utility function to quickly populate events with event__info spans.
     * for each attribute (if this event has this attribute), an span will be created with the attribute in its
     * classname, content of the event attribute, and be appended to an input element
     *
     * @param event     object the event returned from the fullcalendar
     * @param attribute string the key for a value in an event object. if this key isn't set, the function returns false
     * @param __prefix  string to prefix the classname with.  As we're setting the className directly,
     *                  we can actually include other classes in the prefix
     * @param to_append node to append our new span to
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
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
     * This function does tons of work because JS date formatting is generally sucky and because it
     * has to both set the time string and the number of days, for multi-day events
     *
     * @param list_item         node the event list item just before it is added to the list
     * @param index             int the index of this event item (out of a maximum number_of_days items)
     * @param number_of_days    int the number of event items to be added to the list (ie, for a 3-day event, this number is '3')
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.3
     */
    var _format_start_date_return_list_item = (function( list_item, index, number_of_days ) {

        var start_span = list_item.querySelector('.event__start');
        var start_date = new Date( parseInt( list_item.getAttribute( 'data-timestamp_start' ) ) );
        var end_date  = new Date( parseInt( list_item.getAttribute( 'data-timestamp_end' ) ) );
        var time_string = '';
        var day_string = '';

        //if this is just a one-day event, just set the time string
        if(number_of_days === 1) {

            time_string = AjaxEvents.return_12_hour_AMPM_time_string( start_date );
        }
        //else, if this is the last day of a multi-day event
        else if( index === number_of_days ) {

            time_string = "Until </br>" +  AjaxEvents.return_12_hour_AMPM_time_string( end_date );
            day_string = 'Final Day';
        }
        //this is the first up to second-last day of a multi-day event
        else {

            //get the time if it starts today
            //start at one because we incremented index when it was passed in
            if( index === 1 )
                time_string = AjaxEvents.return_12_hour_AMPM_time_string( start_date );

            //if the time string is not set, it means this is not the first day.  Thus, there is no 'time' for today
            if( time_string === '' )
                time_string = 'Ongoing';

            //set the day number we're on
            day_string =  'Day ' + index;
        }

        //some EO events can be set as as 'All Day' events.  It's unlikely, but there it is.
        if( list_item.classList.contains('eo-all-day') )
            start_span.innerHTML = 'All Day';
        else
            start_span.innerHTML = time_string;

        //return the list item after adding the date string to the event title
        return _add_days_to_title_for_ongoing_events_and_return_list_item( list_item, day_string );
    });

    /**
     * Function receives an event list item and a date string and appends the date string to the title if not empty
     *
     * @param list_item         node the event list item just before it is added to the list
     * @param day_string        day string indication day number for multi-day events
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _add_days_to_title_for_ongoing_events_and_return_list_item = (function( list_item, day_string ) {

        if( !day_string )
            return list_item;

        var title_span = list_item.querySelector('.event__title');

        title_span.innerHTML = title_span.innerHTML + ' (' + day_string + ')';
        return list_item;
    });

    /**
     * Simple-as-you-like function that appends a '@' symbol to whatever is in the location span (if a location span)
     *
     * @param list_item         node the event list item just before it is added to the list
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _format_location_return_list_item = (function( list_item ) {

        var location_span = list_item.querySelector('.event__location');
        if( location_span )
            location_span.innerHTML = '@ ' + location_span.innerHTML;

        return list_item;
    });


    /**
     * This is a bit of a weird one, but basically it takes a list item, the className of the span where we'd like
     * to add an event-name url, and then a final string parameter so that we call the right url-formatting method.
     *
     * @param list_item         node the event list item just before it is added to the list
     * @param class_to_format   the class containing the href information into which we inject a link
     * @param link_type         call another function based on what type of link this is
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _format_event_urls = (function( list_item, class_to_format, link_type ) {

        //we want them to say 'View Event' with an optional facebook class.
        var to_format = list_item.querySelector(class_to_format);

        if( !to_format )
            return list_item;

        var link = document.createElement('a');
        link.target="_blank";
        link.href = to_format.innerHTML;

        switch(link_type){
            case 'view':

                link = _view_format_event_urls( list_item, link );
                break;

            case 'ticket':

                link = _ticket_format_event_urls( list_item, link );
                break;

            default:

                link = _default_format_event_urls( list_item, link );
        }

        to_format.innerHTML = '';
        to_format.appendChild(link);

        return list_item;
    });

    /**
     * Format an event link.
     * If it has a .eo-fb-event class, then add a Facebook icon and modify the link text to reference facebook.
     *
     * @param list_item         node the event list item just before it is added to the list
     * @param link              the link node which we've just created and are formatting
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _view_format_event_urls = (function( list_item, link ) {

        var if_facebook = list_item.classList.contains('eo-fb-event');

        link.title = 'Learn more about this event';
        link.innerHTML = 'View Event';

        if( if_facebook ) {
            link.title += ' on Facebook';
            link.innerHTML += ' on Facebook';
            link.classList.add('social_facebook_square');
        }

        link.title += '.';

        return link;

    });

    /**
     * Format a ticket link.  Add an 'external link' icon and explicitly reference tickets in the link text
     *
     * @param list_item         node the event list item just before it is added to the list
     * @param link              the link node which we've just created and are formatting
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _ticket_format_event_urls = (function( list_item, link ) {

        link.title = 'Buy tickets for this event.';
        link.innerHTML = 'Buy tickets';
        link.classList.add('icon_link_alt');

        return link;
    });

    /**
     * Format an generic link.
     * We're not using this method, but whenever you have a switch statement, you should have a fallback
     *
     * @param list_item         node the event list item just before it is added to the list
     * @param link              the link node which we've just created and are formatting
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _default_format_event_urls = (function( list_item, link ) {

        link.innerHTML = 'View Event Link';

        return link;
    });

    /**
     * Sort of a funny method adds classes to html elements.
     * Basically, takes an array of classNames and accepts an array of classNames
     * and add the latter array to all elements found using the former array
     *
     * @param list_item         node the event list item just before it is added to the list
     * @param classes_for_which_to_add_more_classes
     *                          array of classNames.  Elements found with these will have the second array of classNames added to them
     * @param additional_classes
     *                          array of classNames.  Add these classNames to elements foudn using former array
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
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

    /**
     * function collects the values that AjaxEvents needs to run its
     * ajax_update_wordpress_transient_cache method and then calls it.
     *
     * If all goes well, we'll update our WP cache and everything will go FASTER.
     * Long story short: all went well.
     *
     * For more context, check out init-filter.js
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
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

        /*console.log(ajax_options);*/

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
     *
     * @since     1.1.0
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

        //whenever you click the calendar buttons, remove existing list events and update the mobile header
        var buttons = document.querySelectorAll('.fc-button-prev, .fc-button-next');
        for (var i in buttons) {
            if ( buttons.hasOwnProperty(i) && buttons[i].nodeType == 1 )
                buttons[i].addEventListener('click', function(event) {

                    _mobile_header_div = _add_or_update_mobile_header_title( _mobile_header_div );
                    _reset_events_list();
                });
        }

        /*
         our change event listener doesn't fire quickly enough
         (it only shows up AFTER the events have come through again)
         so I've come up with a 'delayed' updating method.

         Basically, set a flag to reset the calendar, and then the next time
         the calendar receives events, clear out the ones that already exist.
         filters[j].addEventListener('change', function(event) {

         });
         */
        var filters = document.querySelectorAll(".eo-cal-filter");
        for (var j in filters) {
            if ( filters.hasOwnProperty(j) && filters[j].nodeType == 1 ) {

                filters[j].addEventListener('click', function(event) {

                    _reset_calendar = true;
                });
            }
        }

    };

    /**
     * utility function wipes clear the events list and clears the _calendar_name variable so that
     * the ::run_once_per_calendar method can rebuild it
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _reset_events_list = (function() {

        //remove existing list items when calendar is pressed.
        _remove( document.querySelectorAll( '#' + _list_id + ' li' ) );
        _calendar_name = "";
        //clear array of ids of events already on list
        while( _ids.length )
            _ids.pop();

        _reset_calendar = false;
    });

    /**
     * Function that we're not using anymore.
     *
     * Would add and remove the 'hidden' div from our events so that we could open and close them.
     * Instead, we went with some prettier jQuery stuff in event-organiser.js
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
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

    /**
     * Horrible jQuery-dependant function that adds a sticky-element to our mobile header.
     * * Unsticks the mobile_header if it's already stuck
     * * Sets the sticky element on the mobile_header
     * * Sets the width to the same size as the fc-header (because its wont to go fullscreen)
     * * Sets up an event listener on window resize so that the header is the same(ish) width as the event
     *      listing event if the window is resized.
     * * Sets up an event listener on scroll so that the header is pushed above the viewport when the
     *      end of the list has been past and returns afterwards.
     *
     *  It seems like potentially pretty inefficient stuff (especially the scroll method), but it
     *  works and looks totally awesome, especially in a destop browser.
     *
     * @type {Function}
     * @private
     *
     * @since     1.1.0
     */
    var _add_jquery_sticky_element_to_head = (function() {

        var header_title = document.getElementById('usc_fb_events_fullcalendar__list__header');
        var $header_title = jQuery( header_title );

        var eo_list = document.getElementById(_list_id);
        var $eo_list = jQuery( eo_list );

        $header_title.unstick();
        $header_title.sticky({topSpacing:0});

        $header_title.on('sticky-start', function() { $header_title.css("width", $eo_list.width() ); });
        $header_title.on('sticky-end', function() { $header_title.css("width", '' ); });

        //basically, we need to check when the window resizes if the thing is visible, and, if so, adjust its size.
        jQuery( window ).resize(function() {

            var header_title = document.getElementById('usc_fb_events_fullcalendar__list__header');
            var $header_title = jQuery( header_title );

            var eo_list = document.getElementById( _list_id );
            var $eo_list = jQuery( eo_list );

            if( header_title.parentNode.classList.contains('is-sticky') )
                $header_title.css("width", $eo_list.width() );

            if( $header_title.height() > 0 )
                $header_title.parent().css( "height" , $header_title.height() );

        });

        jQuery( window ).scroll(function() {

            var header_title = document.getElementById('usc_fb_events_fullcalendar__list__header');
            var $header_title = jQuery( header_title );

            var eo_list = document.getElementById( _list_id );

            if( header_title.parentNode.classList.contains('is-sticky') ) {

                var eo_list_bottom = eo_list.getBoundingClientRect().bottom;
                var header_title_height = $header_title.height();

                //if we have scrolled pas the list, hide the header entirely
                if( eo_list_bottom < 0 )
                    $header_title.css('top', - header_title_height - 2 + "px" );

                //if we have not scrolled past the list and it's still higher than the height of the header, top = 0;
                else if( eo_list_bottom >= header_title_height )
                    $header_title.css('top', '0' );

                /*
                 for everything in between (like there's 40px left before the bottom of the list and our header is 50 px high),
                 then set the top equal to its height minus how many pixels left between the bottom of the list and the
                 top of the window. (ie, 50 - 40 = top : 10px)
                 */
                else
                    $header_title.css('top', ( eo_list_bottom - header_title_height ) + "px" );

            }

        });

    });

    /**
     * function to run once per calendar.
     * Since we don't know how many events are coming, we run this on the first event and then don't run it again
     * unless the calendar_name variable changes (or is reset).
     *
     * function looks for the _reset_calendar flag, and, if true, resets the events list
     * (removes the list and clear the calendar name)
     *
     * If the calendar_name is different to what we've stored, function:
     *  * sets the _eo_fullcalendar variable
     *  * creates the initial date list (all the dates without events)
     *  * creates a mobile-friendly header to augment the existing header
     *  * adds a sticky element to the new header_title, so as to make it mobile friendlier
     *  * _updates the TRANSIENT CACHE.  YES!  https://www.youtube.com/watch?v=fXW02XmBGQw
     *
     * @param view      the view information, with the start/end dates and tons of other stuff we don't use
     * @type {Function}
     *
     * @since     1.1.0
     */
    var run_once_per_calendar = (function( view ) {

        if( _reset_calendar )
            _reset_events_list();

        if( _calendar_name !== view.title ) {

            _eo_fullcalendar = document.querySelector('.eo-fullcalendar');

            _create_date_list( view );

            _calendar_name = view.title;

            _create_mobile_header( view );

            _add_jquery_sticky_element_to_head();

            _ajax_update_wordpress_transient_cache();

        }
    });

    /**
     * function to run each event.  takes an event and appends it onto the scaffolding created by the
     * run_once_per_calendar method (ie, an empty events list).
     *
     * @param event     the event to add to the mobile events list
     * @param view      the view information, with the start/end dates and tons of other stuff we don't use
     *
     * @type {Function}
     *
     * @since     1.1.0
     */
    var run_each_event = (function( event, view ) {

        _create_event_item( event, view );
    });

    /**
     * These are the only public functions from our module
     */
    return {
        run_once_per_calendar: run_once_per_calendar,
        run_each_event: run_each_event
    };

})(options, AjaxEvents, eventorganiser, EOAjaxFront );


/**
 * filter that saved our butt.
 * Basically, the Event Organiser plugin has some internal filters, and one of them is this echo whenever
 * an event is rendered on the calendar.
 * We don't know if NO events are returned and we don't know how many events will be returned.
 *
 * In any case, the first event that is returned will trigger building the list (and adding itself to it),
 * and any subsequent events will be added onto the list as well.
 *
 * @see: http://wp-event-organiser.com/forums/topic/callback-hook-for-events-returned-by-ajax/
 *
 * @param bool      bool not really sure what this does, but if you return false, the events don't load on the calendar
 * @param event     object containing information for a single event
 * @param element   object but I dunno what
 * @param view      object containing a bunch of methods as well as start and end dates for this particular month.
 *
 * @since     1.1.0
 */
window.wp.hooks.addFilter( 'eventorganiser.fullcalendar_render_event', function( bool, event, element, view ){

    //pass in the view, which contains the start and end dates of the calendar. if it changes, we update.
    AjaxFullCalendarList.run_once_per_calendar( view );
    AjaxFullCalendarList.run_each_event( event, view );

    return bool;
}, 4 );

/**
 * Function that adds the colors for our event categories to the head of the HTML document.
 * Colors are available as to the .category-{name} .category__color class, so any elements with this
 * class hierarchy can inherit the color.
 *
 * NOTE** as this is appended to the head, these override all of the other CSS files, so just be aware
 *
 * @see: http://stackoverflow.com/questions/524696/how-to-create-a-style-tag-with-javascript
 *
 * @param eventorganiser      object containing all of our categories, venues, calendars. It's just full of stuff
 *
 * @since     1.1.0
 */
(function add_category_color_css_to_head( eventorganiser ) {

    //all of the categories that we know about
    var all_categories = eventorganiser.fullcal.categories;
    var categories_css_string = '';

    //for each of our categories (if we don't want them all) check if they are in our categories_used array before returning them
    for (var i in all_categories) {
        if ({}.hasOwnProperty.call(all_categories, i)) {
            //make sure any event-category containing the word 'ticket' comes before all of the others (so that it
            //can be overwritten, because we can only do one colour, and calendar category is more important)
        if( all_categories[i].slug.indexOf('ticket') < 0 )
            categories_css_string += ' .category-' + all_categories[i].slug + ' .category__color { color: ' + all_categories[i].color + ' } \n';
        else
            categories_css_string = ' .category-' + all_categories[i].slug + ' .category__color { color: ' + all_categories[i].color + ' } \n' + categories_css_string;
        }
    }

    var head = document.head || document.getElementsByTagName('head')[0];
    var style = document.createElement('style');

    style.type = 'text/css';
    if (style.styleSheet){
        style.styleSheet.cssText = categories_css_string;
    } else {
        style.appendChild(document.createTextNode(categories_css_string));
    }

    head.appendChild(style);
})( eventorganiser );