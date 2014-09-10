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


    //var AjaxEvents;// =

/*
    jQuery('.eo-fullcalendar').on('click', function() {

        jQuery(this).hide();
    });
    */

    console.log(EOAjaxFront);

    window.wp.hooks.addFilter( 'eventorganiser.fullcalendar_render_event', function( bool, event, element, view ){

        console.log(bool);
        console.log(event);
        console.log(element);
        console.log(view);

        return bool;

    }, 4 );


/*
so, we need a run_once function


Run_once removes .whatever_name_of_list

Run_once adds .whatever name of list

Run_once adds listeners to click buttons
Run_once adds listeners to events so that they can open and close.


Run_once sets title. (for now)  or day or whatever.



 */

/*
and we need a Run_for_each_event function.

 Run_for_each_event generates event template and adds it to list.

 */
