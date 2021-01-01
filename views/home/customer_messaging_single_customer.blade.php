
</div>

<div><br/></div>
<h1>Single Customer Messaging</h1>
@include('includes.search_form', array('searchURL'=> URL::to('customer_messaging/search_single_customer') ))

<div class="admin">
    <form class="well form-horizontal">
        <p>Add recipients to the sms list before sending a sms.</p>
        <fieldset>
            <div class="control-group">
                <label class="control-label" for="input01">Message:</label>
                <div class="controls">
                    <textarea class="input-xlarge" maxlength='105' id="message"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <a href="#myModal" class="btn btn-primary"  data-toggle="modal">Send</a>

            </div>


            <p style="font-size: 1.5em;"> SMS List</p>
            <?php

            if(Session::has('sms_list')){

                $sms_list = Session::get('sms_list');

                foreach ($sms_list as $k => $v){

                    ?>

                    <a href="#remModal<?php echo $v['id']; ?>" role="button" class="btn btn-danger" data-toggle="modal"><?php echo $v['email']; ?></a>
                    <div id="remModal<?php echo $v['id']?>" class="modal hide fade" >
                        <div class="modal-header">

                            <h3 id="remModalLabel">Remove user from sms list</h3>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to remove <?php echo $v['email']; ?> from this sms list?</p>
                        </div>
                        <div class="modal-footer">
                            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                            <a href="<?php echo URL::to('customer_messaging/rem_smslist/'.$v['id']) ?>" class="btn btn-danger">Yes</a>
                        </div>
                    </div>

                    <?php

                }

            }



            ?>

        </fieldset>


    </form>

    <table class="table table-bordered">
        <th>First Name</th>
        <th>Surname</th>
        <th>Email Address</th>
        <th>Username</th>
        <th>Add to sms qeue</th>
        <?php
        if ($customers == ""){
            echo "There are no data to show";
        }
        else{

            
            $listed[0] = '';
            $fake[0]['id'] = '';
            $sms_listt = Session::get('sms_list') ? Session::get('sms_list') : $fake; 
            $keytracker = 0;
            foreach ($sms_listt as $kv => $vv){
                $listed[$keytracker] = $vv['id'];
                $keytracker++;
            }

            foreach ($customers as $type):

                if(!in_array($type['id'], $listed)){
                    ?>
                    <tr>
                        <td><?php echo $type['first_name'] ?></td>
                        <td><?php echo $type['surname'] ?></td>
                        <td><?php echo $type['email_address'] ?></td>
                        <td><?php echo $type['username'] ?></td>

                        <td><a href="#myModal<?php echo $type['id']?>" role="button" class="btn btn-info" data-toggle="modal">Add</a></td>

                        <div id="myModal<?php echo $type['id']?>" class="modal hide fade" >
                            <div class="modal-header">

                                <h3 id="myModalLabel">Adding user to sms list</h3>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to add <?php echo $type['username']?> to this sms list?</p>
                            </div>
                            <div class="modal-footer">
                                <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                                <a href="<?php echo URL::to('customer_messaging/add_smslist/'.$type['id'].'/'.$type['username']) ?>" class="btn btn-danger">Yes</a>
                            </div>
                        </div>
                    </tr>
                    <?php 
                }
                endforeach;
            } ?>
        </table>

        <div id="myModal" class="modal hide fade" >
            <div class="modal-header">

                <h3 id="myModalLabel">Please enter SMS password</h3>
            </div>
            <div class="modal-body">

                <form class="form-horizontal">
                    <div class="form-group" role="form">
                        <label for="inputEmail1" class="control-label">SMS Password: </label>
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
                var req_url = base_url + "/customer_messaging/check_sms_login/" + password;
            //alert(req_url);
            $.ajax({
                type:'GET',
                url: req_url,
                datatype:'html',
                success: function(html, textStatus) {

                    //

                    if (html == 'valid') {
                        var message=$('#message').val();
                        if((message =='')){


                            alert('Please fill out all fields');


                        }else{
                            //alert(userid+" "+reason+" "+id);
                            var url=base_url+"/customer_messaging/send_single_sms/"+encodeURIComponent(message);
                            //alert(url);
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



</div>