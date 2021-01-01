
</div>

<div><br/></div>
<h1>Customer Name</h1>

<div class="admin2">
<a href="{{ URL::to('system_reports') }}">System Reports</a> > <a href="{{ URL::to('system_reports/customer_supply_status') }}">Customer Supply Status</a> > Customer Name
<h3><a href="<?php echo "{$csv_url}"?>">Download CSV</a></h3>
<div class="cl"></div>

    <table class="table table-bordered">
        <tr>
            <th>Customer Name</th>

        </tr>
        <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?php echo $customer->first_name." ".$customer->surname; ?></td>
               
            </tr>
        <?php endforeach; ?>

    </table>




</div>