</div>

<div><br/></div>
<h1>Customer Name &amp; Address</h1>

<a href="{{ URL::to('system_reports') }}">System Reports</a> > <a href="{{ URL::to('system_reports/customer_supply_status') }}">Customer Supply Status</a> > Customer Name &amp; Address
<h3><a href="<?php echo "{$csv_url}"?>">Download CSV</a></h3>
<div class="cl"></div>

<table id="sortthistable" class="table table-bordered">
    <thead>
        <tr>
            <th>Customer Name</th>
            <th>House Name/Number</th>
            <th>Street1</th>
            <th>Street2</th>
            <th>Town</th>
            <th>County</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?php echo $customer->first_name." ".$customer->surname; ?></td>
                <td><?php echo $customer->house_number_name; ?></td>
                <td><?php echo $customer->street1; ?></td>
                <td><?php echo $customer->street2; ?></td>
                <td><?php echo $customer->town; ?></td>
                <td><?php echo $customer->county; ?></td>
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