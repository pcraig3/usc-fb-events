var fJS;

jQuery(document).ready(function($) {

  $('#status :checkbox').prop('checked', true);

  //@TODO: This sucks
  $.get( "http://testwestern.com/api/events/events/2014-04-01", function( data ) {

      var data = data.events;
      $.each(data, function(i, e){ e.id = i+1; });

      fJS = filterInit(data);

  }, "json" );

});

function filterInit(data) {

  var view = function(data){
        return "<div class='row'>" +
            "<span class='name'>" + data.name + "</span>" +
            "<span class='host'>" + data.host + "</span>" +
            //"<span class='status'>" + data.status + "</span>" +
            "</div>";
  }

  var settings = {
    filter_criteria: {
      //amount: ['#price_filter .TYPE.range', 'amount'],
      //status: ['#status :checkbox', 'status']
    },
    search: {input: '#search_box' },
    and_filter_on: true,
    id_field: 'id' //Default is id. This is only for usecase
  };

  return FilterJS(data, "#service_list", view, settings);
}
