<br />
<div class="cl"></div>
<h1>Weather vs Top Ups</h1>

<div style="float: right">

    <form method="post" action="" class="form-inline" style="float:right">
        <label>From</label>
        <input id="from" name="from" type="text">
        <label>To</label>
        <input id="to" name="to" type="text">
        <a class="btn btn-info" type="button" id="dateRange1" href="#">Go</a>
    </form>
    
    <br />

    <form method="post" action="" class="form-inline" style="float:right">
        <input type="checkbox" id="clear2" style="margin-bottom: 5px; margin-right: 10px;" disabled="disabled">
        <label>From</label>
        <input id="from2" name="from" type="text">
        <label>To</label>
        <input id="to2" name="to" type="text">
        <a class="btn btn-info" type="button" id="dateRange2" href="#">Go</a>
    </form>

</div>

<div class="admin2">

    <h3><a href="#" style="display: none;" id="downloadCSV">Download CSV</a></h3>

    <div id="chart_div" style="width: 100%; height: 500px; margin-top: 4em; margin-bottom: 1em;"></div>
    <div id="chart_div_extra" style="width: 100%;"></div>
    <div id="chart_compare_div" style="width: 100%; height: 500px; margin-top: 4em; margin-bottom: 1em;"></div>
    <div id="chart_compare_div_extra" style="width: 100%;"></div>

</div>

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
        dataTable.addColumn('number', 'Top Ups');
        dataTable.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
        dataTable.addRows(data);
        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(dataTable, {title: 'Weather vs Top Ups - Date Range 1', legend: { position: 'bottom' }, tooltip: { isHtml: true }});
    }

    function drawCompareChart(data){
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Date');
        dataTable.addColumn('number', 'Temperature');
        dataTable.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
        dataTable.addColumn('number', 'Top Ups');
        dataTable.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
        dataTable.addRows(data);
        var chart = new google.visualization.LineChart(document.getElementById('chart_compare_div'));
        chart.draw(dataTable, {title: 'Weather vs Top Ups - Date Range 2', legend: { position: 'bottom' }, tooltip: { isHtml: true }});
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

            $.post("{{ URL::to('weather_reports/topups/get') }}",
            {
                from : from,
                to : to
            })
            .done(function(data)
            {
                drawChart(data);
                downloadCSV();
                loadExtraData('#chart_div_extra', from, to);
            });
        }

        if(from&&to&&from2&&to2){

            $('#clear2').attr('disabled', false);
            $('#clear2').attr('checked', true);

            $.post("{{ URL::to('weather_reports/topups/get') }}",
            {
                from : from2,
                to : to2
            })
            .done(function(data)
            {
                drawCompareChart(data);
                downloadCSV();
                loadExtraData('#chart_compare_div_extra', from2, to2);
            });

        }

    }

    function loadExtraData(div, from, to){
        $.post("{{ URL::to('weather_reports/topups/extra') }}",
            {
                from : from,
                to : to
            })
            .done(function(data)
            {
                $(div).html('<table style="width: 50%; margin: 0 auto 2em; font-weight:bold; font-size: 1.2em;"><tr><td>Mean: '+data.mean+'</td><td>Median: '+data.median+'</td><td>Mode: '+data.mode+'</td></tr></table>');
            });
    }

    function downloadCSV(){

        $('#downloadCSV').show();

        var from = $('#from').val();
        var to = $('#to').val();
        var from2 = $('#from2').val();
        var to2 = $('#to2').val();

        $.post("{{ URL::to('weather_reports/topups/csv') }}",
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