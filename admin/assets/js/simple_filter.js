var fJS;

var services = [
    {
        "permalink": "1-mr-ona-howe",
        "title": "Mr. Ona Howe",
        "amount": 100,
        "status": "active",
        "is_public": true,
        "id": 1,
        "nonprofit": "Eldon Zulauf"
    },
    {
        "permalink": "2-darien-hoeger",
        "title": "Darien Hoeger",
        "amount": 201,
        "id": 2,
        "status": "inactive",
        "nonprofit": "Beryl McDermott"
    },
    {
        "permalink": "3-mrs-frederique-kris",
        "title": "Mrs. Frederique Kris",
        "amount": 500,
        "id": 3,
        "status": "active",
        "nonprofit": "Eldon Zulauf"
    },
    {
        "permalink": "4-jedediah-pouros",
        "title": "Jedediah Pouros",
        "amount": 300,
        "id": 4,
        "status": "inactive",
        "nonprofit": "Mabel Tillman I"
    },
    {
        "permalink": "5-mrs-daisy-macejkovic",
        "title": "Mrs. Daisy Macejkovic",
        "amount": 410,
        "id": 5,
        "status": "active",
        "nonprofit": "Mr. Ayden O'Keefe"
    } ];


jQuery(document).ready(function($) {

  $('#status :checkbox').prop('checked', true);

  fJS = filterInit();
});

function filterInit() {

  var view = function(services){
        return "<div class='row'>" +
            "<span class='name'>" + services.title + "</span>" +
            "<span class='age'>" + services.amount + "</span>" +
            "<span class='country'>" + services.status + "</span>" +
            "</div>";
  }

  var settings = {
    filter_criteria: {
      //amount: ['#price_filter .TYPE.range', 'amount'],
      status: ['#status :checkbox', 'status']
    },
    search: {input: '#search_box' },
    and_filter_on: true,
    id_field: 'id' //Default is id. This is only for usecase
  };

  return FilterJS(services, "#service_list", view, settings);
}
