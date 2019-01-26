require('jquery-ui');
require('jqrangeslider');

// import jqrangeslider from 'jquery-ui/ui/widgets/jqrangeslider';

// $("#slider").html("I'm a slider!");

$(function () {

    var url = $('#myPieChart').data('url');


    $('#searchForm').change(function(event) {
        drawChart();
    });

    google.charts.load('current', {packages: ['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {

        values = $('#searchForm').serialize();
        url =  $('#myPieChart').data('url') + '?' + values;
        $('#url_link').attr('href', url); // for debugging
        // $('#url_link').html( url);
        options = {
            // title: 'Rappnet Posters  ',
            // is3D: true,
            height: 800,
            reverseCategories: true,
            pieStartAngle: 180,
            // chartArea:{left:20,top:0,width:'70%',height:'600'},
            // pieSliceText: 'labeled',
            legend: {position: 'labeled'}
        };


        console.log(url, values);

        // Instantiate and draw the chart.
        var chart = new google.visualization.PieChart(document.getElementById('myPieChart'));


        // Define the data for the chart
        var data = new google.visualization.DataTable();

        // Every time the table fires the "select" event, it should call your
// selectHandler() function.
        // google.visualization.events.addListener(chart, 'select', selectHandler);
        google.visualization.events.addListener(chart, 'click', selectHandler);

        function selectHandler(e) {
            console.log(e);
            // alert('A table row was selected');
        }

        var d = [];
        // d.push(['account', 'count']);

        data.addColumn('string', 'account');
        data.addColumn('number', 'count');

        var jqxhr = $.getJSON( url, function(jsonData) {
            data.addRows(jsonData.topAccounts);

            console.log(jsonData);
            /*
            $('#total_message_count').html(jsonData.total);
            $('#top_posters_message_count').html(jsonData.topTotal);
            $('#top_posters').html(jsonData.topAccounts.length - 1);
            */
            let topPostersCount = jsonData.topAccounts.length - 1;
            let startDate = $('#startDate').val();
            let endDate = $('#endDate').val();
            let percentage = Math.round((jsonData.topTotal / jsonData.total) * 100);
            // console.log(startDate);

            options.title = `${topPostersCount} People posted ${percentage}% (${jsonData.topTotal} of ${jsonData.total}), `;
            options.title += `${startDate} to ${endDate}`;

            if (false) {
                $.each( jsonData.topAccounts, function( i, item ) {
                    console.log(item);
                    e = [item.id + '.xx', item.cnt];

                    console.log(e);
                    // data.addRow(e);

                    d.push(e);
                });
                console.log(d);
            }

            // data.addRows(d);
            chart.draw(data, options);

            // update the DOM

            console.log( "success" );
        })
            .done(function(data) {

                console.log( "second success" );
            })
            .fail(function(error) {
                console.log(error);
                console.log( "error" );
            })
            .always(function() {
                console.log( "complete" );
            });



        /*
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'account');
        data.addColumn('number', 'count');
        data.addRows([
            ['Nitrogen', 0.78],
            ['Oxygen', 0.21],
            ['Other', 0.01]
        ]);
        */
        /*
        data.addRows([
            ['Genre', 'Fantasy & Sci Fi', 'Romance', 'Mystery/Crime', 'General',
                'Western', 'Literature', { role: 'annotation' } ],
            ['2010', 10, 24, 20, 32, 18, 5, ''],
            ['2020', 16, 22, 23, 30, 16, 9, ''],
            ['2030', 28, 19, 29, 30, 12, 13, '']
        ]);
        */


        // chart.draw(data, options);

    }

    // $("#slider").dateRangeSlider();
    // $("#slider").rangeSlider("values", 10, 20);
});

// $("#slider").rangeSlider('x');
