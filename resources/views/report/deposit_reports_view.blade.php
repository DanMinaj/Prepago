

</div>

<div><br/></div>
<h1>Deposit Reports</h1>

<div class="admin2">
    <div class="cl"></div>
    <a href="{!! URL::to('system_reports') !!}">System Reports</a> > <a href="{!! URL::to('system_reports/customer_supply_status') !!}">Customer Supply Status</a> > Deposit Reports
    <h3>Total Deposits: <?php echo $deposite_count;?></h3>
    <h3>Total Deposit Amount: {!! $currencySign !!}<?php echo $total_amount;?></h3>
    <h3><a href="<?php echo $csv_url?>">Download CSV</a></h3>

    <table id="sortthistable" class="table table-bordered">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Deposit Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo $customer->first_name." ".$customer->surname; ?></td>
                    <td>{!! $currencySign !!}<?php echo $customer->deposit_amount; ?></td>
                    <td><?php echo $customer->date; ?></td>
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