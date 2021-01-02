                            
</div>

<div><br/></div>

<h1>Issue Top-Up Arrears</h1>
            

<div class="admin">
    <form class="well form-horizontal">
        <fieldset>

            <div class="control-group">
                <label class="control-label" for="input01">Amount</label>
                <input type="hidden" class="input-xlarge" id="id" value="<?php echo $customer_id;?>">
                <div class="controls">
                    <input type="text" class="input-xlarge" id="amount">

                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="input01">Daily Repayment Amount</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="repay_amount">

                </div>
            </div>
            <div class="form-actions">
                <a href="#myModal" class="btn btn-primary"  data-toggle="modal">Submit</a>

            </div>
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
                                var reason=$('#repay_amount').val();
                                var id=$('#id').val();
                                //alert(amount+" "+reason+" "+id);
                                var url=base_url+"/issue_topup_arrears/add_amount/"+id+"/"+amount+"/"+reason;
                                //alert(url);
                                window.location=url;
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