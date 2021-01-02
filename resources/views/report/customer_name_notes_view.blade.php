
</div>

<div><br/></div>
<h1>Customer Name &amp; Notes</h1>

<div class="admin2">
    <a href="{!! URL::to('system_reports') !!}">System Reports</a> > <a href="{!! URL::to('system_reports/customer_supply_status') !!}">Customer Supply Status</a> > Customer Name &amp; Notes
    <h3><a href="<?php echo "{$csv_url}"?>">Download CSV</a></h3>
    <div class="cl"></div>

    <table id="sortthistable" class="table table-bordered">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo $customer->first_name." ".$customer->surname; ?></td>
                    <td><?php echo $customer->utility_notes; ?></td>
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