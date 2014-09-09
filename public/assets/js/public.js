(function ( $ ) {
	"use strict";

	$(function () {

		// Place your public-facing JavaScript here

        $('.eo-fullcalendar').on('eventorganiser.fullcalendar_render_event fullcalendar_render_event eventAfterAllRender eventAfterRender eventRender viewDisplay loading change load', function() {
            console.log('.eo-fullcalendar');
        });
        //calendar on change

        //loading thing on change
        $('.fc-view').on('eventorganiser.fullcalendar_render_event fullcalendar_render_event eventAfterAllRender eventAfterRender eventRender viewDisplay loading change load', function() {
            console.log('.fc-view');
        });

        $('.fc-content').on('eventorganiser.fullcalendar_render_event fullcalendar_render_event eventAfterAllRender eventAfterRender eventRender viewDisplay loading change load', function() {
            console.log('.fc-content');
        });

        $('#eo_fullcalendar_1_loading').on('eventorganiser.fullcalendar_render_event fullcalendar_render_event eventAfterAllRender eventAfterRender eventRender viewDisplay loading change load', function() {
            console.log('loading');
        });


        //events on change
        $('.eo-fullcalendar').on( 'eventorganiser.fullcalendar_render_event fullcalendar_render_event eventAfterAllRender eventAfterRender eventRender viewDisplay loading change load', '.fc-event', function(e){
            console.log('.fc-event');
        });

        $('body').children().on('eventorganiser.fullcalendar_render_event fullcalendar_render_event eventAfterAllRender eventAfterRender eventRender viewDisplay loading change load', '.fc-event', function(e){
        console.log('amazing');
        });

	});

}(jQuery));