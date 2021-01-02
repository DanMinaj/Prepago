</div>

<div><br/></div>
<h1>Customer Top-up History &horbar; {!! Auth::user()->scheme->scheme_nickname !!}</h1>

<div style="float: right">

    <form method="post" action="<?php echo URL::to('system_reports/topup_reports/customer_topup_history_search_by_date') ?>"class="form-inline" style="float:left">
        <label>From</label>
        <input id="from" value='@if(isset($from)){!!$from!!}@endif' name="from" type="text">
        <label>To</label>
        <input id="to" value='@if(isset($to)){!!$to!!}@endif' name="to" type="text">
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

    <a href="{!! URL::to('system_reports') !!}">System Reports</a> > <a href="{!! URL::to('system_reports/topup_reports') !!}">Top-Up Reports</a> > Customer Top-up History
    <!--
	<h3>Total Top-Up Amount: {!! $currencySign !!}<?php echo number_format($total_amount, 2) ?></h3>
    <h3>Total Top-Ups: <?php echo $total; ?></h3>
    <h5>Paypal: <?php echo $pp_payments; ?> &horbar; &euro;{!! number_format($pp_payments_amount, 2) !!}</h5>
    <h5>Stripe: <?php echo $s_payments; ?> &horbar; &euro;{!! number_format($s_payments_amount, 2) !!}</h5>
    <h5>Payzone: <?php echo $pz_payments; ?> &horbar; &euro;{!! number_format($pz_payments_amount, 2) !!}</h5>
    <h5>PayPoint: <?php echo $ppo_payments; ?> &horbar; &euro;{!! number_format($ppo_payments_amount, 2) !!}</h5>
	-->
   
    <div class="cl"></div>
    <h3><a href="<?php echo $csv_url ?>">Download CSV</a></h3>
    <div id="all_customers">
	
	@if($overload)
		
	<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
		<b>Note:</b> To prevent the page from crashing we have shown you <b>100/{!! count($payments) !!}</b> in this list. To view them all, download the CSV.
	</div>

	@endif
        <table id="sortthistable" class="table table-bordered">
            <thead>
                <tr>
				
                    <th>Name</th>
                    <th>Time</th>
                    <th>Amount</th>
                    <th>Method</th>
                    
                </tr>
            </thead>
            <tbody>
			
			@if(count($payments) >= 100)
				<?php $limit = 100; ?>
			@endif
				
			@if(count($payments) < 100)
				<?php $limit = count($payments); ?>
			@endif
			
			
			@for($i = 0; $i<=$limit-1; $i++)
				
				
				<tr>
				
					<td>
						<a href="{!! URL::to('customer_tabview_controller/show', ['id' => $payments[$i]['customer_id']]) !!}"> Customer {!! $payments[$i]['customer_id'] !!} </a>
					</td>
					
					<td>
						{!! $payments[$i]['time_date'] !!}
					</td>
					
					<td>
					
						{!! $currencySign !!}{{ number_format($payments[$i]['amount'], 2) }}
					
					</td>
					
					<td>
					
						{!! (empty($payments[$i]['acceptor_name_location'])) ? $payments[$i]['acceptor_name_location'] : $payments[$i]['acceptor_name_location_']  !!}
					
					</td>
					
					
				</tr>
               
				
			@endfor
			
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