</div>

<div><br/></div>
<h1>Tariff History</h1>
            
<div style="float: right">

        <form method="post" action="<?php echo URL::to('system_reports/topup_reports/tarrif_history_by_date') ?>"class="form-inline" style="float:left">
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
<a href="{{ URL::to('system_reports') }}">System Reports</a> > <a href="{{ URL::to('system_reports/topup_repots') }}">Top-Up Reports</a> > Tariff History
 <?php foreach ($tarrifs as $tarrif): ?>
        <tr><td>
            <table class="table table-bordered">
                <caption style="font-weight: bold;text-decoration: underline;"><?php echo date("d-m-Y",strtotime($tarrif->record_date));?></caption>
                <tr>
                    <th>Tariff Name</th>
                    <th>Tariff Value</th>
                    <th>Revenue Earned</th>
                </tr>
                <tr>
                    <td><?php echo $tarrif->tariff_1_name;?></td>
                    <td>{{ $currencySign }}<?php echo $tarrif->tariff_1;?></td>
                    <td>{{ $currencySign }}<?php echo $total_amount;?></td>
                    
                </tr>
                <tr>
                    <td><?php echo $tarrif->tariff_2_name;?></td>
                    <td>{{ $currencySign }}<?php echo $tarrif->tariff_2;?></td>
                    <td><?php echo $tarrif->total_number_of_customers;?></td>
                    
                </tr>
                <tr>
                    <td><?php echo $tarrif->tariff_3_name;?></td>
                    <td>{{ $currencySign }}<?php echo $tarrif->tariff_3;?></td>
                    <td>{{ $currencySign }}<?php echo 0;?></td>
                    
                </tr>
                <tr>
                    <td><?php echo $tarrif->tariff_4_name;?></td>
                    <td>{{ $currencySign }}<?php echo $tarrif->tariff_4;?></td>
                    <td>{{ $currencySign }}<?php echo 0;?></td>
                    
                </tr>
                <tr>
                    <td><?php echo $tarrif->tariff_5_name;?></td>
                    <td>{{ $currencySign }}<?php echo $tarrif->tariff_5;?></td>
                    <td>{{ $currencySign }}<?php echo 0;?></td>
                    
                </tr>
            </table>
            </td></tr>
        <?php endforeach; ?>
</div>
</div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
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