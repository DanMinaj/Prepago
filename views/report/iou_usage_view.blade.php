</div>

<div><br/></div>
<h1>IOU Usage Display</h1>

<div class="admin2">
    <a href="{{ URL::to('system_reports') }}">System Reports</a> > <a href="{{ URL::to('system_reports/credit_issue_reports') }}">Credit Issue Reports</a> > IOU Usage Display
    <h3><a href="<?php echo "{$csv_url}"?>">Download CSV</a></h3>
    <div class="cl"></div>

    <table id="sortthistable" class="table table-bordered">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Date/Time</th>
                <th>Charge</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo $customer->first_name." ".$customer->surname; ?></td>
                    <td><?php echo $customer->time_date; ?></td>
                    <td>{{ $currencySign }}<?php echo $customer->charge; ?></td>
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