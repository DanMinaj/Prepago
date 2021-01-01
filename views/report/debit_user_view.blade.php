
</div>

<div><br/></div>
<h1>List Of Debit Users</h1>

<div class="admin2">
    <div class="cl"></div>
    <a href="{{ URL::to('system_reports') }}">System Reports</a> > <a href="{{ URL::to('system_reports/customer_supply_status') }}">Customer Supply Status</a> > List of debit users
    <h3><a href="<?php echo $csv_url?>">Download CSV</a></h3>

    <table id="sortthistable" class="table table-bordered">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Barcode</th>
                <th>Balance</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo $customer->first_name." ".$customer->surname; ?></td>
                    <td><?php echo $customer->barcode; ?></td>
                    <td><?php echo $customer->balance; ?></td>
                    <td>
                        <?php
                        echo $customer->house_number_name . ', ' . $customer->street1 . ', ' . $customer->street2 . ', ' . $customer->town . ', ' . $customer->county;
                        ?>
                    </td>
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