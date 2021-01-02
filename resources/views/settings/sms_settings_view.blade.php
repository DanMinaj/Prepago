</div>
<div class="cl"></div>
<div class="admin2">
<h1>CHANGE SMS PASSWORD</h1>
    <form class="well form-horizontal">
        <p>Your sms password is: <?php echo $sms_password; ?></p>
        <p>You can change this password below.</p>
        <fieldset>
            <div class="control-group">
                <label class="control-label" for="input01">SMS Password:</label>
                <div class="controls">
                    <input type="password" class="input-xlarge" id="sms_password"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <a href="#myModal" class="btn btn-primary"  data-toggle="modal">Change password</a>

            </div>

        </fieldset>


    </form>


        <div id="myModal" class="modal hide fade" >
            <div class="modal-header">

                <h3 id="myModalLabel">Please re-enter your new SMS password</h3>
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
                <a href="#"  class="btn btn-danger"   onclick="issuepass()">Change</a>
            </div>
        </div>
        <script type="text/javascript">
            function issuepass() {
                var password = $('#password').val();
                var base_url = $('#base').val();
                var sms_password = $('#sms_password').val();
                if(password != sms_password){
                    alert('Your passwords does not match.');
                    return false;
                }
                var req_url = base_url + "/sms_settings/change_sms_password/" + password;
            //alert(req_url);
            $.ajax({
                type:'GET',
                url: req_url,
                datatype:'html',
                success: function(html, textStatus) {
                    var url=base_url+"/settings/sms_settings";
                    window.location=url;
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('An error occurred! ' + textStatus);
                }
            });


}

</script>



</div>



<div class="cl">&nbsp;</div>

<div class="admin">
<h1>SMS Settings</h1>
    <table class="table table-bordered">

        <tr>
            <th>Message</th>
            <th>Title</th>
            <th>Description</th>
            <th>&nbsp;</th>
        </tr>

        <tr>
            <td>1</td>
            <td>Balance Message</td>
            <td><?php echo $messages['balance_message']?></td>
            <td><a data-toggle="modal" href="#myModal1" class="btn btn-info">Edit</a></td>
        </tr>
        <div id="myModal1" class="modal hide fade" ><div class="modal-header"><h3 id="myModalLabel">Edit SMS Message</h3></div><div class="modal-body">
        <form id="form1" action="{!! URL::to('settings/sms_settings/save_sms_message') !!}" method="POST" class="form-horizontal"><div class="form-group" role="form"><label for="smsmessage" class="control-label">SMS Message: </label><div>
        <input type="hidden" name="formid" value="1">
        <textarea class="field span5" rows="5" name="smsmessage" id="smsmessage"><?php echo $messages['balance_message']?></textarea></div></div></form><div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#"  class="btn btn-danger"   onclick="issue(1)">Change</a></div></div>        

        <tr>
            <td>2</td>
            <td>IOU Message</td>
            <td><?php echo $messages['IOU_message']?></td> 
            <td><a data-toggle="modal" href="#myModal2" class="btn btn-info">Edit</a></td>
        </tr>
        <div id="myModal2" class="modal hide fade" ><div class="modal-header"><h3 id="myModalLabel">Edit SMS Message</h3></div><div class="modal-body">
        <form id="form2" action="{!! URL::to('settings/sms_settings/save_sms_message') !!}" method="POST" class="form-horizontal"><div class="form-group" role="form"><label for="smsmessage" class="control-label">SMS Message: </label><div>
        <input type="hidden" name="formid" value="2">
        <textarea class="field span5" rows="5" name="smsmessage" id="smsmessage"><?php echo $messages['IOU_message']?></textarea></div></div></form><div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#"  class="btn btn-danger"   onclick="issue(2)">Change</a></div></div>

        <tr>
            <td>3</td>
            <td>IOU Extra Message</td>
            <td><?php echo $messages['IOU_extra_message']?></td>
            <td><a data-toggle="modal" href="#myModal3" class="btn btn-info">Edit</a></td>
        </tr>
        <div id="myModal3" class="modal hide fade" ><div class="modal-header"><h3 id="myModalLabel">Edit SMS Message</h3></div><div class="modal-body">
        <form id="form3" action="{!! URL::to('settings/sms_settings/save_sms_message') !!}" method="POST" class="form-horizontal"><div class="form-group" role="form"><label for="smsmessage" class="control-label">SMS Message: </label><div>
        <input type="hidden" name="formid" value="3">
        <textarea class="field span5" rows="5" name="smsmessage" id="smsmessage"><?php echo $messages['IOU_extra_message']?></textarea></div></div></form><div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#"  class="btn btn-danger"   onclick="issue(3)">Change</a></div></div>

        <tr>
            <td>4</td>
            <td>IOU Denied Message</td>
            <td><?php echo $messages['IOU_denied_message']?></td>
            <td><a data-toggle="modal" href="#myModal4" class="btn btn-info">Edit</a></td>
        </tr>
        <div id="myModal4" class="modal hide fade" ><div class="modal-header"><h3 id="myModalLabel">Edit SMS Message</h3></div><div class="modal-body">
        <form id="form4" action="{!! URL::to('settings/sms_settings/save_sms_message') !!}" method="POST" class="form-horizontal"><div class="form-group" role="form"><label for="smsmessage" class="control-label">SMS Message: </label><div>
        <input type="hidden" name="formid" value="4">
        <textarea class="field span5" rows="5" name="smsmessage" id="smsmessage"><?php echo $messages['IOU_denied_message']?></textarea></div></div></form><div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#"  class="btn btn-danger"   onclick="issue(4)">Change</a></div></div>

        <tr>
            <td>5</td>
            <td>Rates/Tariff Message</td>
            <td><?php echo $messages['rates_message']?></td>
            <td><a data-toggle="modal" href="#myModal5" class="btn btn-info">Edit</a></td>
        </tr>
        <div id="myModal5" class="modal hide fade" ><div class="modal-header"><h3 id="myModalLabel">Edit SMS Message</h3></div><div class="modal-body">
        <form id="form5" action="{!! URL::to('settings/sms_settings/save_sms_message') !!}" method="POST" class="form-horizontal"><div class="form-group" role="form"><label for="smsmessage" class="control-label">SMS Message: </label><div>
        <input type="hidden" name="formid" value="5">
        <textarea class="field span5" rows="5" name="smsmessage" id="smsmessage"><?php echo $messages['rates_message']?></textarea></div></div></form><div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#"  class="btn btn-danger"   onclick="issue(5)">Change</a></div></div>

        <tr>
            <td>6</td>
            <td>Shut Off Message</td>
            <td><?php echo $messages['shut_off_message']?></td>
            <td><a data-toggle="modal" href="#myModal6" class="btn btn-info">Edit</a></td>
        </tr>
        <div id="myModal6" class="modal hide fade" ><div class="modal-header"><h3 id="myModalLabel">Edit SMS Message</h3></div><div class="modal-body">
        <form id="form6" action="{!! URL::to('settings/sms_settings/save_sms_message') !!}" method="POST" class="form-horizontal"><div class="form-group" role="form"><label for="smsmessage" class="control-label">SMS Message: </label><div>
        <input type="hidden" name="formid" value="6">
        <textarea class="field span5" rows="5" name="smsmessage" id="smsmessage"><?php echo $messages['shut_off_message']?></textarea></div></div></form><div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#"  class="btn btn-danger"   onclick="issue(6)">Change</a></div></div>

        <tr>
            <td>7</td>
            <td>Shut Off Warning Message</td>
            <td><?php echo $messages['shut_off_warning_message']?></td>
            <td><a data-toggle="modal" href="#myModal7" class="btn btn-info">Edit</a></td>
        </tr>
        <div id="myModal7" class="modal hide fade" ><div class="modal-header"><h3 id="myModalLabel">Edit SMS Message</h3></div><div class="modal-body">
        <form id="form7" action="{!! URL::to('settings/sms_settings/save_sms_message') !!}" method="POST" class="form-horizontal"><div class="form-group" role="form"><label for="smsmessage" class="control-label">SMS Message: </label><div>
        <input type="hidden" name="formid" value="7">
        <textarea class="field span5" rows="5" name="smsmessage" id="smsmessage"><?php echo $messages['shut_off_warning_message']?></textarea></div></div></form><div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#"  class="btn btn-danger"   onclick="issue(7)">Change</a></div></div>

        <tr>
            <td>8</td>
            <td>Credit Warning Message</td>
            <td><?php echo $messages['credit_warning_message']?></td>
            <td><a data-toggle="modal" href="#myModal8" class="btn btn-info">Edit</a></td>
        </tr>
        <div id="myModal8" class="modal hide fade" ><div class="modal-header"><h3 id="myModalLabel">Edit SMS Message</h3></div><div class="modal-body">
        <form id="form8" action="{!! URL::to('settings/sms_settings/save_sms_message') !!}" method="POST" class="form-horizontal"><div class="form-group" role="form"><label for="smsmessage" class="control-label">SMS Message: </label><div>
        <input type="hidden" name="formid" value="8">
        <textarea class="field span5" rows="5" name="smsmessage" id="smsmessage"><?php echo $messages['credit_warning_message']?></textarea></div></div></form><div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#"  class="btn btn-danger"   onclick="issue(8)">Change</a></div></div>

        <tr>
            <td>9</td>
            <td>Barcode Message</td>
            <td><?php echo $messages['barcode_message']?></td>
            <td><a data-toggle="modal" href="#myModal9" class="btn btn-info">Edit</a></td>
        </tr>
        <div id="myModal9" class="modal hide fade" ><div class="modal-header"><h3 id="myModalLabel">Edit SMS Message</h3></div><div class="modal-body">
        <form id="form9" action="{!! URL::to('settings/sms_settings/save_sms_message') !!}" method="POST" class="form-horizontal"><div class="form-group" role="form"><label for="smsmessage" class="control-label">SMS Message: </label><div>
        <input type="hidden" name="formid" value="9">
        <textarea class="field span5" rows="5" name="smsmessage" id="smsmessage"><?php echo $messages['barcode_message']?></textarea></div></div></form><div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#"  class="btn btn-danger"   onclick="issue(9)">Change</a></div></div>

        <tr>
            <td>10</td>
            <td>Top Up Message</td>
            <td><?php echo $messages['topup_message']?></td>
            <td><a data-toggle="modal" href="#myModal10" class="btn btn-info">Edit</a></td>
        </tr>
        <div id="myModal10" class="modal hide fade" ><div class="modal-header"><h3 id="myModalLabel">Edit SMS Message</h3></div><div class="modal-body">
        <form id="form10" action="{!! URL::to('settings/sms_settings/save_sms_message') !!}" method="POST" class="form-horizontal"><div class="form-group" role="form"><label for="smsmessage" class="control-label">SMS Message: </label><div>
        <input type="hidden" name="formid" value="10">
        <textarea class="field span5" rows="5" name="smsmessage" id="smsmessage"><?php echo $messages['topup_message']?></textarea></div></div></form><div id="alert"  class= "alert alert-error" style="visibility: hidden;">Error Occured</div></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#"  class="btn btn-danger"   onclick="issue(10)">Change</a></div></div>

    </table>

    <p>There are placeholder variable within the Prepago system that allow the user to insert values into SMS messages to customers.</p>
    <table class="table table-bordered">
        <tr><td colspan="2">Placeholders:</td></tr>

        <tr><td>“a”</td><td>The customers arrears balance</td></tr>
        <tr><td>“b”</td><td>The customers balance</td></tr>
        <tr><td>“1”</td><td>Tariff number 1</td></tr>
        <tr><td>“2”</td><td>Tariff number 2</td></tr>
        <tr><td>“3”</td><td>Tariff number 3</td></tr>
        <tr><td>“4”</td><td>Tariff number 4</td></tr>
        <tr><td>“5”</td><td>Tariff number 5</td></tr>
        <tr><td>“bc”</td><td>The customers barcode</td></tr>
        <tr><td>“IOUc”</td><td>The IOU charge</td></tr>
        <tr><td>“IOUEc”</td><td>The IOU Extra Charge</td></tr>

    </table>



<script type="text/javascript">
    function issue(id) {
        $('#form'+id).submit();
    }

</script>


</div>