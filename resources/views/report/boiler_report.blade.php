</div>

<div><br/></div>
<h1>Boiler Report</h1>

<div style="float: right">

    <form method="post" action="<?php echo URL::to('system_reports/boiler_report') ?>"class="form-inline" style="float:left">
        <label>From</label>
        <input id="from" name="from" type="text">
        <label>To</label>
        <input id="to" name="to" type="text">
        <input type="submit" value="search" class="btn-success"/>

    </form>

</div>

<div class="cl">&nbsp;</div>
<script type="text/javascript">
    $(document).ready(function()
    {
        $("#to").datepicker({ dateFormat: 'dd-mm-yy' });
        $("#from").datepicker({ dateFormat: 'dd-mm-yy' });
    });

</script>


<div class="admin2">
    <a href="{!! URL::to('system_reports') !!}">System Reports</a> > Boiler Report
    <div class="cl"></div>
    <h3><a href="{{ $csv_url }}">Download CSV</a></h3>

    @include('includes.notifications')

    <table id="sortthistable" class="table table-bordered">
        <thead>
            <tr>
                <th>Meter Number</th>
                <th>Date</th>
                <th>Reading</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($meters as $meter)
                @if ($meter->latestReadings && $meter->latestReadings->count())
                    @foreach ($meter->latestReadings as $meterReading)
                    <tr>
                        <td>{{ $meter->meter_number }}</td>
                        <td>{{ $meterReading->time_date }}</td>
                        <td>{{ $meterReading->reading1 }}</td>
                    </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
</div>

<script type="text/javascript">
    $(function () {
        $("#sortthistable").tablesorter();
    });
</script>