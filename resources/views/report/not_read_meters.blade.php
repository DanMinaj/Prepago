</div>

<div><br/></div>
<h1>Not Read Meters Reports</h1>


<div class="admin2">

    <a href="{{ URL::to('system_reports') }}">System Reports</a> > Not Read Meters Reports
    <h3><a href="<?php echo $csv_url?>">Download CSV</a></h3>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Meter Number</th>
                <th>Latest Reading</th>
                <th>Latest Reading Date/Time</th>
                <th>Scheme Number</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($meters as $meter)
                <tr>
                    <td>{{ $meter->meter_number }}</td>
                    <td>{{ $meter->latest_reading }}</td>
                    <td>{{ $meter->latest_reading_time }}</td>
                    <td>{{ $meter->scheme_number }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>