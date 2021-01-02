</div>

<div><br/></div>
<h1>Customer Pending Top-ups</h1>

<div style="float: right">
    
    <form method="post" action="<?php echo URL::to('system_reports/topup_reports/pending_topups_search_by_date') ?>"class="form-inline" style="float:left">
        <label>From</label>
        <input id="from" name="from" type="text">
        <label>To</label>
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
    <a href="{!! URL::to('system_reports') !!}">System Reports</a> > <a href="{!! URL::to('system_reports/topup_reports') !!}">Top-Up Reports</a> > Customer Pending Top-ups
    <h3>Total Top-Up Amount: {!! $currencySign !!}<?php echo $total_amount ?></h3>
    <div class="cl"></div>
    <h3><a href="<?php echo $csv_url ?>">Download CSV</a></h3>
    <div id="all_customers">
        <table id="sortthistable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Time</th>
                    <th>Amount</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer->first_name . " " . $customer->surname; ?></td>
                        <td><?php echo $customer->time_date ?></td>
                        <td>{!! $currencySign !!}<?php echo $customer->amount; ?></td>
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>



    </div>
</div>
</div>
</div>
<script type="text/javascript">
    $(document).ready(function() {

        $("#sortthistable").tablesorter(); 


        $('#allitem').click(function() {
            if ($(this).is(':checked')) {
                
                //alert('clicked');
                var url = "<?php echo URL::to('system_reports/topup_reports/customer_topup_history_by_ajax'); ?>";
                $.ajax({
                    type: 'GET',
                    url: url,
                    data: 'html',
                    success: function(html, textStatus) {

                        $('#all_customers').html(html);
                        //location.reload();
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        alert('An error occurred! ' + textStatus);
                    }
                });
            }
        });
    });



</script>