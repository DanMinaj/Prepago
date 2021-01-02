

</div>
<div class="cl"></div>
<h1>Close Account
    @include('includes.search_form', array('searchURL'=> URL::to('close_account/close_account_search') ))
</h1>
<div class="admin2">
	
	@if (Session::has('successMsg') && Session::get('successMsg'))
		<div class="alert alert-success">{{ Session::get('successMsg') }}</div>
	@endif
    @include('includes.notifications')
	
   <table class="table table-bordered tablesorter sortthistable">
		
		<thead>
			<th>Barcode</th>
			<th>Username</th>
			<th>Email Address</th>
			<th>Mobile Number</th>
			<th>First Name</th>
			<th>Surname</th>		
			<th>&nbsp;</th>
		</thead>
        <?php
        if ($customers == "")
            echo "There are no data to show";
        else
            foreach ($customers as $type):
                ?>
            <tr>
                <td><?php echo $type['barcode'] ?></td>
                <td><?php echo $type['username'] ?></td>
                <td><?php echo $type['email_address'] ?></td>
                <td><?php echo $type['mobile_number'] ?></td>
                <td><?php echo $type['first_name'] ?></td>
                <td><?php echo $type['surname'] ?></td>

                <td>
					<a href="{{ URL::to('close_account/' . $type['id'] . '/step1') }}" role="button" class="btn btn-danger">Delete</a>
                    <a href="<?php echo URL::to('customer_tabview_controller/show/'.$type['id']) ?>" target="_blank" class="btn btn-info">View</a>
                </td>

				<!--
                <div id="myModal<?php echo $type['id']?>" class="modal hide fade" >
                    {{-- Form::open(['url' => 'close_account', 'onsubmit' => 'return checkLandlord()']) }}
                        {{ Form::hidden('swap_from_id', $type['id']) }}
                        <div class="modal-header">
                            <h3 id="myModalLabel">Close Account</h3>
                        </div>
                        <div class="modal-body">
                            <p>
                                <a href="{{ URL::to('open_account/swap/' . $type['id']) }}">Create a new normal user or landlord</a>
                            </p>
                            @if ($type['role'] == 'normal' && $landlords[$type['id']]->count())
                                <select name="swap_to_id" id="landlords">
                                    <option value="">Select landlord</option>
                                    @foreach ($landlords[$type['id']] as $key => $val)
                                        <option value="{{ $val->id }}">{{ $val->username }}</option>
                                    @endforeach
                                </select>
                                <div>
                                    <label>{{ Form::checkbox('swap_credit', 1, true, ['id' => 'swap_credit', 'onclick' => 'javascript: swapCredit()']) }} Would you like to swap credit? ({{ $currencySign }}{{ $type['balance'] }})</label>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                            <input class="btn btn-danger" type="submit" value="Yes" id="submit">
                        </div>
                    {{ Form::close() --}}
                </div>
				-->
				
            </tr>
        <?php endforeach; ?>
    </table>

</div>
<script>
		$(document).ready(function() {
			$(function() {
				$(".sortthistable").tablesorter({
					
					headers: {
					  1: { sorter: "digit", empty : "top" }, // sort empty cells to the top
					  2: { sorter: "digit", string: "max" }, // non-numeric content is treated as a MAX value
					  3: { sorter: "digit", string: "min" }  // non-numeric content is treated as a MIN value
					},
					sortList: [[1, 1]]
					
				});
			});
		});
</script>