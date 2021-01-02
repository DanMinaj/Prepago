</div>
<div class="cl"></div>
<div class="admin2">
    <h1>Scheme Messaging</h1>

    <div id="spinner"></div>

    @if (Auth::user()->schemes && Auth::user()->schemes->count() > 1)
        <ul class="nav nav-pills">
            <li class="{!! $all ? '' : 'active' !!}">
                <a href="{!! URL::to('customer_messaging/scheme/') !!}">Scheme {!! $currentScheme !!}</a>
            </li>
            <li class="{!! $all ? 'active' : '' !!}">
                <a href="{!! URL::to('customer_messaging/scheme/all') !!}">All Schemes</a>
            </li>
        </ul>
    @endif

    <form class="well form-horizontal">
        <fieldset>
            <p>Please type in a message to send to the whole scheme.</p>
            <div class="control-group">
                <label class="control-label" for="input01">Message:</label>
                <div class="controls">
                    <textarea maxlength='105' class="input-xlarge" id="message"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <a class="btn btn-primary" onclick="issue()">Send</a>

            </div>
        </fieldset>
    </form>
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
            <a href="#"  class="btn btn-danger" onclick="issue()">Yes</a>
        </div>
    </div>

    {!! HTML::script('resources/js/spin_options.js') !!}
    {!! HTML::script('resources/js/spin.min.js') !!}

    <script type="text/javascript">
        function issue() {
            var password = $('#password').val();
            var base_url = $('#base').val();
            var req_url = base_url + "/customer_messaging/check_sms_login/" + password;
           
		   var url=base_url+"/customer_messaging/send_scheme_sms{!! $all ? '/all' : ''!!}";
                         sendSchemeSMS(url);


        }

        function sendSchemeSMS(url)
        {
            $("#myModal").modal('hide');
            $('#spinner').show().css({"position": "fixed", "width": "100%", "height": "100%", "left": "0", "top": "0", "zIndex": "1000000", "background": "url(/resources/images/overlay.png) repeat 0 0"});
            $("#spinner").spin(opts);
			
            var message=$('#message').val();
            $.ajax({
                type: "POST",
                url: url,
                data: { sms: message },
                success: function (resp, textStatus) {
                    $('#spinner').hide();
					
					console.log(resp);
					
                    if (resp == 1)
                    {
                        $("#message").val("");
                        alert('The SMS were sent successfully');
						
						
                    }
                    else
                    {
                        alert('There was an error sending SMS to ' + resp);
                    }
                },
                error: function (xhr, textStatus, errorThrown) {
                    alert('An error occurred! ' + textStatus);
                }
            });
        }

    </script>



</div>