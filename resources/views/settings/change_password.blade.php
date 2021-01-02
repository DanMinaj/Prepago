</div>
<div class="cl"></div>
<div class="admin2">
<h1>CHANGE PASSWORD</h1>
    <form class="well form-horizontal">
        <fieldset>

            <div class="control-group">
                <label class="control-label" for="input01">New Password:</label>
                <div class="controls">
                    <input type="password" class="input-xlarge" id="password">

                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="input01">Retype Password:</label>
                <div class="controls">
                    <input type="password" class="input-xlarge" id="repassword">

                </div>
            </div>
            <div class="form-actions">
                <a href="#myModal" class="btn btn-primary"  data-toggle="modal">Submit</a>

            </div>
        </fieldset>
    </form>
    <div id="myModal" class="modal hide fade" >
        <div class="modal-header">

            <h3 id="myModalLabel">Change Username</h3>
        </div>
        <div class="modal-body">

            <form class="form-horizontal">
                <div class="form-group" role="form">
                    <label for="inputEmail1" class="control-label">Admin Old Password: </label>
                    <div>
                        <input type="password" class="form-control" id="oldpassword" placeholder="Password">
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
            var password = $('#oldpassword').val();
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
                        var password=$('#password').val(); 
                        var repassword=$('#repassword').val(); 
                        if(password!=repassword){
                             
                             
                            alert('Password And Retype Password Field Does Not Match');

                             
                        }else{
                            //alert(amount+" "+reason+" "+id);
                            var url=base_url+"/user_settings/change_admin_password/"+password;
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