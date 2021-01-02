</div>

<div><br/></div>
<h1>Carlinn Permanent Meter Readings (30 September 2016)</h1>
</div>

<div class="admin2">
    <div id="all_customers">
        <table id="sortthistable" class="table" style="width: 50%; border: 1px solid #ddd;">
            <thead>
            <tr>
                <th>Permanent Meter</th>
                <th>Reading</th>
                <th>Date time</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($readings as $reading)
                    <tr>
                        <td>{{{ $reading->username }}}</td>
                        <td>{{{ $reading->reading1 }}}</td>
                        <td>{{{ $reading->time_date }}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>



    </div>
</div>
</div>
</div>