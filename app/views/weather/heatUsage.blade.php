<br />
<div class="cl"></div>
<h1>Weather vs Heat Usage</h1>

<div style="float: right">

    <form method="post" action="" class="form-inline" style="float:right">
        <label>From</label>
        <input id="from" name="from" value="2018-07-16" type="text">
        <label>To</label>
        <input id="to" name="to" value="2018-07-20" type="text">
        <a class="btn btn-info" type="button" id="dateRange1" href="#">Go</a>
    </form>
    
	<!--
    <br />
    <div style="text-align: center;"><label><b>Comparison Dates</b></label></div>
    <br />

    <form method="post" action="" class="form-inline" style="float:right">
        <input type="checkbox" id="clear2" style="margin-bottom: 5px; margin-right: 10px;" disabled="disabled">
        <label>From</label>
        <input id="from2" name="from" type="text">
        <label>To</label>
        <input id="to2" name="to" type="text">
        <a class="btn btn-info" type="button" id="dateRange2" href="#">Go</a>
    </form>
	-->
</div>

<div class="admin2">

<!--
    <h3><a href="#" style="display: none;" id="downloadCSV">Download CSV</a></h3>

    <div id="chart_div" style="width: 100%; height: 500px; margin-top: 4em; margin-bottom: 1em;"></div>
    <div id="chart_div_extra" style="width: 100%;"></div>
    <div id="chart_compare_div" style="width: 100%; height: 500px; margin-top: 4em; margin-bottom: 1em;"></div>
    <div id="chart_compare_div_extra" style="width: 100%;"></div>
-->
<canvas id="usage_vs_weather_context" width="1000" height="400"></canvas>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js"></script>
<script type="text/javascript" src="/resources/js/util/graphs/weather_reports.js?<?php echo time(); ?>"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

    $(document).ready(function(){
		
        $("#from").datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $("#to").datepicker({
            dateFormat: 'yy-mm-dd'
        });

        $("#to2").datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $("#from2").datepicker({
            dateFormat: 'yy-mm-dd'
        });
		
		return;

        $('#clear2').change(function(){
            $('#clear2').attr('disabled', true);
            $('#clear2').attr('checked', false);
            $('#from2').val('');
            $('#to2').val('');
            loadChart();
        });

        $('#dateRange1').on('click', function(event){
            event.preventDefault();
            loadChart();
        });
        $('#dateRange2').on('click', function(event){
            event.preventDefault();
            loadChart();
        });

    });


    google.load("visualization", "1", {packages:["corechart"]});

    function drawChart(data){
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Date');
        dataTable.addColumn('number', 'Temperature');
        dataTable.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
        dataTable.addColumn('number', 'Heat Usage');
        dataTable.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
        dataTable.addRows(data);
        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(dataTable, {title: 'Weather vs Heat Usage - Date Range 1', legend: { position: 'bottom' }, tooltip: { isHtml: true }});
    }

    function drawCompareChart(data){
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Date');
        dataTable.addColumn('number', 'Temperature');
        dataTable.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
        dataTable.addColumn('number', 'Heat Usage');
        dataTable.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
        dataTable.addRows(data);
        var chart = new google.visualization.LineChart(document.getElementById('chart_compare_div'));
        chart.draw(dataTable, {title: 'Weather vs Heat Usage - Date Range 2', legend: { position: 'bottom' }, tooltip: { isHtml: true }});
    }

    function loadChart(){

        var from = $('#from').val();
        var to = $('#to').val();
        var from2 = $('#from2').val();
        var to2 = $('#to2').val();

        if(!from2&&!to2){
            $('#chart_compare_div').html('');
            $('#chart_compare_div_extra').html('');
        }

        if(from&&to&&!from2&&!to2){

            $.post("{{ URL::to('weather_reports/heat_usage/get') }}",
            {
                from : from,
                to : to
            })
            .done(function(data)
            {
                if(data != ''){
                    drawChart(data);
                    downloadCSV();
                    loadExtraData('#chart_div_extra', from, to);
                }else{
                    $('#chart_div').html('There is no data in the selected date range.');
                }
            });
        }

        if(from&&to&&from2&&to2){

            $('#clear2').attr('disabled', false);
            $('#clear2').attr('checked', true);

            $.post("{{ URL::to('weather_reports/heat_usage/get') }}",
            {
                from : from2,
                to : to2
            })
            .done(function(data)
            {
                if(data != ''){
                    drawCompareChart(data);
                    downloadCSV();
                    loadExtraData('#chart_compare_div_extra', from2, to2);
                }else{
                    $('#chart_compare_div').html('There is no data in the selected date range.');
                }
            });

        }

    }

    function loadExtraData(div, from, to){
        $.post("{{ URL::to('weather_reports/heat_usage/extra') }}",
            {
                from : from,
                to : to
            })
            .done(function(data)
            {
                $(div).html(
                    '<table style="width: 50%; margin: 0 auto 2em; font-size: 1.2em; text-align: center;">'+
                    '    <tr style="font-weight:bold;"><th> </th><th>Day 1</th><th>Day 2</th><th>Day 3</th><th>Day 4</th><th>Day 5</th></tr>'+
                    '    <tr><td style="font-weight:bold;">Min</td><td>'+data.day1min+'</td><td>'+data.day2min+'</td><td>'+data.day3min+'</td><td>'+data.day4min+'</td><td>'+data.day5min+'</td></tr>'+
                    '    <tr><td style="font-weight:bold;">Max</td><td>'+data.day1max+'</td><td>'+data.day2max+'</td><td>'+data.day3max+'</td><td>'+data.day4max+'</td><td>'+data.day5max+'</td></tr>'+
                    '</table>'
                    );
            });
    }

    function downloadCSV(){

        $('#downloadCSV').show();

        var from = $('#from').val();
        var to = $('#to').val();
        var from2 = $('#from2').val();
        var to2 = $('#to2').val();

        $.post("{{ URL::to('weather_reports/heat_usage/csv') }}",
        {
            from : from,
            to : to,
            from2 : from2,
            to2 : to2
        })
        .done(function(data)
        {
            $('#downloadCSV').attr('href', data);
        });
    }

</script>