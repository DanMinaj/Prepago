@if (count($topups))
    <table class="table table-bordered sortthistable">
        <thead>
        <tr>
            <th class="header">Date/Time</th>
            <th class="header">Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($topups as $key => $topup)
            <tr>
                <td>{{{ $topup->time_date }}}</td>
                <td>{{ $currencySign }} {{{ $topup->amount }}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>No Entries</p>
@endif