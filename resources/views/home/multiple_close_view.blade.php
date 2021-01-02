                            
</div>

<div><br/></div>

<h1>Multiple Close</h1>

@include('includes.search_form', array('searchURL'=> URL::to('settings/multiple_close/multiple_close_account_search') ))

<div class="cl"></div>
<div class="admin2">

	@if (Session::has('successMsg') && Session::get('successMsg'))
		<div class="alert alert-success">{!! Session::get('successMsg') !!}</div>
	@endif
	
    <form method="post" action="{!! URL::to('settings/multiple_close/close_account_action') !!}">
        <table class="table table-bordered">
            <th>Barcode</th>
            <th>Username</th>
            <th>Email Address</th>
            <th>Mobile Number</th>
            <th>First Name</th>
            <th>Surname</th>

            <th>Delete Account</th>
            <th>View Account</th>
            <?php $i = 0 ?>
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

                        <td> <label class="checkbox">
                                <input type="hidden" name="row[]" value="<?php echo $type['id'] ?>"/>
                                <input name="checkbox[<?php echo $i++ ?>]" id="check" type="checkbox"> Delete
                            </label></td>
                        <td><a href="<?php echo URL::to('customer_tabview_controller/show/'.$type['id']) ?>" target="_blank" class="btn btn-info">View</a></td>

                    </tr>
                <?php endforeach; ?>
        </table>

        <a id="target" role="button" class="btn btn-danger" >Close Accounts</a>

        <div id="myModal" class="modal hide fade" >
            <div class="modal-header">

                <h3 id="myModalLabel">Are you sure you wish to close [number of accounts to close] account(s)?</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you wish to close [number of accounts to close] account(s)?</p>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                <button type="submit" class="btn btn-danger">Close Accounts</button>
            </div>
        </div>
        
        
         <div id="myModal2" class="modal hide fade" >
            <div class="modal-header">

                <h3 id="myModalLabel">Selection empty</h3>
            </div>
            <div class="modal-body">
                <p>Please Select customer</p>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $("#target").click(function() {
            //if ($('#check').is(':checked')) {
			if ($('input[type="checkbox"][name^="checkbox"]:checked').length > 0) {
               $('#myModal').modal('show');
            } else {
                $('#myModal2').modal('show');
            }
        });
    </script>


</div>