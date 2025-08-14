(function ($) {
 "use strict";
	 $("span.pie").peity("pie", {
        fill: ['#006DF0', '#d7d7d7', '#ffffff'],
        height: 128,
        max: null,
        min: 0,
        stroke: "#4d89f9",
        strokeWidth: 1,
        width: 128
    })

    $(".line").peity("line",{
        fill: '#006DF0',
        stroke:'#169c81',
    })

    $(".bar").peity("bar", {
        fill: ["#006DF0", "#d7d7d7"]
    })

    $(".bar_dashboard").peity("bar", {
        fill: ["#006DF0", "#d7d7d7"],
        width:100
    })
})(jQuery); 