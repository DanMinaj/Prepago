
</div>

<div><br/></div>
<h1>Issue Credit</h1>
@include('includes.search_form', array('searchURL'=> URL::to('issue_credit/search_customers') ))   


<p>This allows admins to issue credit to a customer where the customer does not need to pay the credit back to the system. Each of these credit transactions are recorded in the database.</p>

<form class="well form-horizontal">
        <fieldset>

            <div class="control-group">
                <label class="control-label" for="input01">Amount</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="amount">

                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="input01">Reason</label>
                <div class="controls">
                    <textarea type="text" class="input-xlarge" id="reason"></textarea>                   
                </div>
            </div>
            <div class="form-actions">
                <a href="#myModal" class="btn btn-primary"  data-toggle="modal">Issue credit</a>

            </div>

<p style="font-size: 1.5em;">Issue credit to single or multiple customers</p>

<?php

            if(Session::has('credit_list')){

                $credit_list = Session::get('credit_list');

                foreach ($credit_list as $k => $v){

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
                            <a href="<?php echo URL::to('issue_credit/rem_creditlist/'.$v['id'])?>" class="btn btn-danger">Yes</a>
                        </div>
                    </div>

                    <?php

                }

            }



            ?>

        </fieldset>
    </form>


    <div id="myModal" class="modal hide fade" >
        <div class="modal-header">

            <h3 id="myModalLabel">Issue Credit</h3>
        </div>
        <div class="modal-body">

            <form class="form-horizontal">
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
            <a href="#"  class="btn btn-danger"   onclick="issue()">Yes</a>
        </div>
    </div>
    <script type="text/javascript">
        function issue() {
            var password = $('#password').val();
            var base_url = $('#base').val();
            var req_url = base_url + "/issue_credit/check_login/" + password;
            //alert(req_url);
            $.ajax({
                type:'GET',
                url: req_url,
                datatype:'html',
                success: function(html, textStatus) {
                            
                    //

                    if (html == 'valid') {
                        var amount=$('#amount').val();
                        var reason=$('#reason').val();
                        var id=$('#id').val();
                                
                        if((amount=='') || (reason =='')|| (amount<0)){
                             
                             if(amount==''){
                                 alert('Please Insert Amount');
                             }else if(reason==''){
                                 alert('Please Insert Reason');
                             }else{
                                 alert('Please Insert Positive Amount');
                             }
                             
                        }else{
                            //alert(amount+" "+reason+" "+id);
                            var url=base_url+"/issue_credit/add_amount/"+amount+"/"+reason;
                            window.location=url;
                        }
                    } else {
                        //var visibility = $('#alert').css("visibility");
                        $('#alert').css("visibility", "visible");
                        return false;
                    }

                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('An error occurred! ' + textStatus);
                }
            });


        }

    </script>

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
            $credit_listt = Session::get('credit_list') ? Session::get('credit_list') : $fake; 
            $keytracker = 0;
            foreach ($credit_listt as $kv => $vv){
                $listed[$keytracker] = $vv['id'];
                $keytracker++;
            }

?>


    <?php foreach ($customers as $customer):

    	if(!in_array($customer->id, $listed)){
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
                    <a href="<?php echo URL::to('issue_credit/add_creditlist/'.$customer->id.'/'.$customer->username) ?>" class="btn btn-danger">Yes</a>
                </div>
            </div>


    <?php 
    	} 
    endforeach; ?>
</table>