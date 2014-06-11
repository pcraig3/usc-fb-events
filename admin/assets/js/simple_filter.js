var fJS;

jQuery(document).ready(function($) {

  $('#removed :checkbox').prop('checked', true);

  //@TODO: This sucks
  $.get( "http://testwestern.com/api/events/events/2014-04-01")
      .done(function( data ) {

      var events = data.events;
      $.each(events, function(i, e){ e.id = i+1; });

      fJS = filterInit( events );

      $('#event_list').trigger( "change" );

  });

});

function filterInit( events ) {

  var view = function( events ){
        return "<div class='row' data-eid='" + events.eid + "' data-start-time='" + events.start_time + " '>" +
            "<span class='name'>" + events.name + "</span>" +
            "<span class='host'>" + events.host + "</span>" +
            "<span class='removed'>display</span>" +
            "</div>";
  }

  var settings = {
    filter_criteria: {
      //amount: ['#price_filter .TYPE.range', 'amount'],
      removed: ['#removed :checkbox', 'removed']
    },
    search: {input: '#search_box' },
    and_filter_on: true,
    id_field: 'id' //Default is id. This is only for usecase
  };

  return FilterJS(events, "#event_list", view, settings);
}
