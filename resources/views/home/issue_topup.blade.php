
</div>

<div><br/></div>

<h1>{{ $title }}</h1>
            
<div style="float: right">
    <form method="post" action="<?php echo URL::to($searchFormURL) ?>" onsubmit="add_remove_creditlist()">
		
		<input type="hidden" name="type" id="issue_topup_type" value="{{ $type }}" />
		<input type="submit" value="" style="height: 37px;width: 32px;float: right;background: url(/resources/images/search.png);"/>
		<input id="search_box" name="search_box" type="text" style="height: 35px;padding: 0 0 0 5px;">

    </form>

</div>


<p>This allows admins to issue credit to a customer where the customer does not need to pay the credit back to the system. Each of these credit transactions are recorded in the database.</p>

<form class="well form-horizontal" id="issue_credit_form" onsubmit="$('#myModal').modal(); return false;">
	<fieldset>
		@if (Session::has('errorMsg'))
			<div id="alert" class= "alert alert-error">{{ Session::get('errorMsg') }}</div>
		@elseif (Session::has('successMsg'))
			<div class= "alert alert-success">{{ Session::get('successMsg') }}</div>
		@endif
		<div class="control-group">
			<label class="control-label" for="input01">Amount</label>
			<div class="controls">
				{{ Form::text('amount', null, ['id' => $type . '_amount', 'class' => 'input-xlarge']) }}
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label" for="input01">Reason</label>
			<div class="controls">
				<textarea type="text" class="input-xlarge" id="{{ $type }}_reason"></textarea>                   
			</div>
		</div>
		
		@if ($type == 'issue_topup_arrears')
			<div class="control-group">
				<label class="control-label" for="arrears_daily_repayment">Arrears Daily Repayment</label>
				<div class="controls">
					{{ Form::text('arrears_daily_repayment', 0.5, ['id' => 'arrears_daily_repayment', 'class' => 'input-xlarge']) }}              
				</div>
			</div>
		@endif
		
		<div class="form-actions">
			<a href="#myModal" class="btn btn-primary"  data-toggle="modal">Issue credit</a>
		</div>

		<p style="font-size: 1.5em;">{{ $title }} to single or multiple customers</p>
		
		<div id="credit_list">
			<?php
            if(Session::has($type . '_credit_list'))
			{
				$credit_list = Session::get($type . '_credit_list');

                foreach ($credit_list as $k => $v)
				{ 
				?>
                    <a href="#remModal<?php echo $v['id']; ?>" role="button" class="btn btn-danger" data-toggle="modal"><?php echo $v['email']; ?></a>
                    <div id="remModal<?php echo $v['id']?>" class="modal hide fade" >
                        <div class="modal-header">
                            <h3 id="remModalLabel">Remove user from the list</h3>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to remove <?php echo $v['email']; ?> from this list?</p>
                        </div>
                        <div class="modal-footer">
                            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                            <a href="<?php echo URL::to('issue_top_up/rem_creditlist/'.$v['id'] . '/' . $type)?>" class="btn btn-danger" onclick="add_remove_creditlist()">Yes</a>
                        </div>
                    </div>
                <?php
                }

            }
			?>
		</div>

	</fieldset>
</form>


<div id="myModal" class="modal hide fade" >
	<div class="modal-header">
		<h3 id="myModalLabel">{{ $title }}</h3>
	</div>
	<div class="modal-body">
		<form class="form-horizontal" id="admin_pass_form">
			<div class="form-group" role="form">
				<label for="inputEmail1" class="control-label">Admin Password: </label>
				<div>
					<input type="password" class="form-control" id="password" placeholder="Password">
					<input type="hidden" class="form-control" id="base" placeholder="Password" value="<?php echo URL::to('/'); ?>">
				</div>
			</div>
		</form>
		<div id="alert"  class= "alert alert-error" style="visibility: hidden;">Wrong Password</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		<a href="#" class="btn btn-danger" onclick="issue('{{ $addAmountURL }}', '{{ $type }}')">Yes</a>
	</div>
</div>
    

<table class="table table-bordered">
	<tr>
		<th>Name</th>
		<th>Username</th>
		<th>Barcode</th>
		<th>Email</th>
		<th>Mobile</th>
		<th><br></th>
	</tr>

	<?php
	$listed[0] = '';
	$fake[0]['id'] = '';
	$credit_listt = Session::get($type . '_credit_list') ? Session::get($type . '_credit_list') : $fake; 
	$keytracker = 0;
	foreach ($credit_listt as $kv => $vv){
		$listed[$keytracker] = $vv['id'];
		$keytracker++;
	}
	
	foreach ($customers as $customer):

		if(!in_array($customer->id, $listed))
		{
		?>
			<tr style="text-align: center;">
				<td><?php echo $customer->first_name . " " . $customer->surname; ?></td>
				<td><?php echo $customer->username; ?></td>
				<td><?php echo $customer->barcode; ?></td>
				<td><?php echo $customer->email_address; ?></td>
				<td><?php echo $customer->mobile_number; ?></td>
				<td><a href="#myaddModal<?php echo $customer->id; ?>" role="button" class="btn btn-info" data-toggle="modal">Add</a></td>
			</tr>

			<div id="myaddModal<?php echo $customer->id; ?>" class="modal hide fade" >
				<div class="modal-header">
					<h3 id="myModalLabel">Adding user to credit list</h3>
				</div>
				<div class="modal-body">
					<p>Are you sure you want to add <?php echo $customer->username; ?> to this list?</p>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
					<a href="<?php echo URL::to('issue_top_up/add_creditlist/'.$customer->id.'/'.$customer->username . '/' . $type) ?>" onclick="add_remove_creditlist()" class="btn btn-danger">Yes</a>
				</div>
			</div>
		<?php 
    	}
    		
	endforeach; ?>
    	
</table>


{{ HTML::script('resources/js/issue-credit.js') }}
<script type="text/javascript">
$(function() {
	$("#admin_pass_form").submit(function(e){
    	e.preventDefault();
    	issue('{{ $addAmountURL }}', '{{ $type }}');
	});
});
</script>