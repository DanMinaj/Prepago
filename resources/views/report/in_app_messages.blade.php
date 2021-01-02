</div>

<div><br/></div>
<h1>In-App Messages Sent</h1>
            
<div style="float: right">

        <form method="post" action="<?php echo URL::to('system_reports/search_in_app_message_by_date') ?>"class="form-inline" style="float:left">
            <label>To</label>
            <input id="from" name="from" type="text">
            <label>From</label>
            <input id="to" name="to" type="text">
            <input type="submit" value="search" class="btn-success"/>

        </form>

</div>

<script type="text/javascript">
    $(document).ready(function()
    {
        $("#to").datepicker({ dateFormat: 'dd-mm-yy' });
        $("#from").datepicker({ dateFormat: 'dd-mm-yy' });
    });

</script>

<div class="admin2">
<a href="{!! URL::to('system_reports') !!}">System Reports</a> > <a href="{!! URL::to('system_reports/messaging_reports') !!}">Messaging Reports</a> > In-App Messages Sent
<h3>Total Messages:<?php echo $sms_count;?></h3>
<h3>Total Charge:<?php echo $total_amount;?></h3>
<h3><a href="<?php echo $csv_url?>">Download CSV</a></h3>

    <table id="sortthistable" class="table table-bordered">
    <thead>
        <tr>
            <th>Customer Name</th>
            <th>Smart Phone Id</th>
            <th>Message</th>
            <th>Date/Time</th>
            <th>Charge</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?php echo $customer->first_name." ".$customer->surname; ?></td>
                <td><?php echo $customer->smart_phone_id; ?></td>
                <td><?php echo $customer->message; ?></td>
                <td><?php echo $customer->date_time; ?></td>
                <td><?php echo $customer->charge; ?></td>
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