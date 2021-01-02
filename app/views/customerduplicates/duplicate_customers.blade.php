
	<table class="table table-bordered">
		<th>Barcode</th>
		<th>UID</th>
		<th>Username</th>
		<th>Email Address</th>
		@if ($fromSystemReports)
			<th>Address</th>
		@endif
		<th>Mobile Number</th>
		<th>First Name</th>
		<th>Surname</th>
		<th>meter_ID</th>
		<th>meter_number</th>
		<th>latest_reading</th>
		<th>latest_reading_time</th>
		<th>
		<a  style="display:none;" class="btn btn-success" type="button" href="">Resolve Duplicates</a>
		</th>


		<?php
		//if ($info == "")
		//    echo "There are no data to show";
		//else
			foreach ($info as $user):
		//print_r($user);exit;
            ?>
            @if (1 )
                <tr>
                    
                    <td><?php echo $user->barcode ?></td>
                    <td><?php echo $user->id ?></td>
                    <td><?php echo $user->username ?></td>
                    <td><?php echo $user->email_address ?></td>
                    @if ($fromSystemReports)
                        <td>
                            {{{ $user->house_number_name ? $user->house_number_name . ', ' : '' }}}
                            {{{ $user->street1 ? $user->street1 . ', ' : '' }}}
                            {{{ $user->street2 ? $user->street2 . ', ' : '' }}}
                            {{{ $user->town ? $user->town . ', ' : ''  }}}
                            {{{ $user->county ? $user->county . ($user->country ? ', ' : '') : '' }}}
                            {{{ $user->country ? $user->country : '' }}}
                        </td>
                    @endif
                    <td><?php echo $user->mobile_number ?></td>
                    <td><?php echo $user->first_name ?></td>
                    <td><?php echo $user->surname ?></td>
                    <td><?php echo $user->meter_ID ?></td>
                    <td><?php echo $user->meter_number ?></td>
                    <td><?php echo $user->latest_reading ?></td>
                    <td><?php echo $user->latest_reading_time ?></td>
                    
                    <td><a  class="btn btn-info" type="button" href="<?php echo URL::to('customer_tabview_controller/show/'.$user->id) ?>">View</a></td>

                </tr>
				<tr style="display:none;"><td colspan="100%">
				<table class="table table-bordered"><tr>
		<th>UID</th>
		<th>Username</th>
		<th>Email Address</th>
		@if ($fromSystemReports)
			<th>Address</th>
		@endif
		<th>Mobile Number</th>
		<th>First Name</th>
		<th>Surname</th>
		<th>Balance</th>
		<th>&nbsp;</th>
		</tr>
		<tr>
                    
                    <td><?php echo $user->id ?></td>
                    <td><?php echo $user->username ?></td>
                    <td><?php echo $user->email_address ?></td>
                    @if ($fromSystemReports)
                        <td>
                            {{{ $user->house_number_name ? $user->house_number_name . ', ' : '' }}}
                            {{{ $user->street1 ? $user->street1 . ', ' : '' }}}
                            {{{ $user->street2 ? $user->street2 . ', ' : '' }}}
                            {{{ $user->town ? $user->town . ', ' : ''  }}}
                            {{{ $user->county ? $user->county . ($user->country ? ', ' : '') : '' }}}
                            {{{ $user->country ? $user->country : '' }}}
                        </td>
                    @endif
                    <td><?php echo $user->mobile_number ?></td>
                    <td><?php echo $user->first_name ?></td>
                    <td><?php echo $user->surname ?></td>
                    <td class="blue">{{ $currencySign }}<?php echo $user->balance ?></td>
                    <td><a  class="btn btn-info" type="button" href="<?php echo URL::to('customer_tabview_controller/show/'.$user->id) ?>">View</a></td>

                </td></tr></table>
                </tr>
            @endif
        <?php endforeach; ?>
	</table>
