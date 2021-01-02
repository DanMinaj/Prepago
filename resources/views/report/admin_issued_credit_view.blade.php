</div>

<div><br/></div>
<h1>Admin Issued Credit</h1>

<div class="admin2">
    <a href="{{ URL::to('system_reports') }}">System Reports</a> > <a href="{{ URL::to('system_reports/credit_issue_reports') }}">Credit Issue Reports</a> > Admin Issued Credit
    <h3><a href="<?php echo "{$csv_url}"?>">Download CSV</a></h3>
    <div class="cl"></div>

    <table id="sortthistable" class="table table-bordered">
        <thead>
        <tr>
            <th>Customer Name</th>
            <th>Date/Time</th>
            <th>Admin Name</th>
            <th>Amount</th>
            <th>Reason</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($customers as $customer): ?>
        <tr>
            <td>{{{ $customer->first_name." ".$customer->surname }}}</td>
            <td>{{{ $customer->date_time }}}</td>
            <td>{{{ $customer->admin_name }}}</td>
            <td>{{ $currencySign }}{{{ $customer->amount }}}</td>
            <td>{{{ $customer->reason }}}</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>




</div>

<script type="text/javascript">
    $(function () {
        $("#sortthistable").tablesorter();
    });
</script>