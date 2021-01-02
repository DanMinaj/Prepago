</div>

<div><br/></div>
<h1>Meter View</h1>

<div class="admin">

    @include('includes.notifications')

    <ul class="nav nav-tabs" style="margin: 30px 0">
        <li class="active"><a href="#home" data-toggle="tab">Customer Details</a></li>
        <li><a href="#profile" data-toggle="tab">Meter Details</a></li>
        <li><a href="#messages" data-toggle="tab">Usage Details</a></li>
        <li><a href="#arrears" data-toggle="tab">Arrears</a></li>
        <li><a href="#topups" data-toggle="tab">Top Up</a></li>
        <li><a href="#new-topups" data-toggle="tab">Top Ups</a></li>
        <li><a href="#send-message" data-toggle="tab">Send Message</a></li>
        <li><a href="#utility-notes" data-toggle="tab">Notes</a></li>
        <li><a href="#iou-usage" data-toggle="tab">IOU Usage</a></li>
        <li><a href="#diagnostics" data-toggle="tab">Diagnostics</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="home"><p>No Available Information</p></div>

        <div class="tab-pane" id="profile">
            @if ($meter)
                <dl class="dl-horizontal">
                    <dt>Meter ID Number:</dt>
                    <dd>{{{ $meter->meter_number }}}</dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Meter Reading:</dt>
                    <dd>{{{ $meter->latest_reading . ' (' . $meter->latest_reading_time . ')' }}}</dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt style='white-space: normal;'>Start of Month Reading:</dt>
                    <dd>{{{ $meter->start_of_month_reading }}}</dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Shut Off Device Status:</dt>
                    <dd>{{{ $meter->shut_off_device_status }}}</dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Last Shut off Time:</dt>
                    <dd>{{{ $meter->last_shut_off_time }}}</dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Last Shut off Reading: </dt>
                    <dd>{{{ $meter->shut_off_reading }}}</dd>
                </dl>
                <dl class="dl-horizontal">
                    <dt>Sim Mobile Number:</dt>
                    <dd>{{{ $meter->meter_contact_number }}}</dd>
                </dl>
            @else
                <p>No Available Information</p>
            @endif
        </div>

        <div class="tab-pane" id="messages"><p>No Available Information</p></div>

        <div class="tab-pane" id="arrears"><p>No Available Information</p></div>

        <div class="tab-pane" id="topups"><p>No Available Information</p></div>

        <div class="tab-pane" id="send-message"><p>No Available Information</p></div>

        <div class="tab-pane" id="utility-notes"><p>No Available Information</p></div>

        <div class="tab-pane" id="iou-usage"><p>No Available Information</p></div>

        <div class="tab-pane" id="new-topups"><p>No Available Information</p></div>

        <div class="tab-pane" id="diagnostics">
            @include('partials.diagnostics', ['meter_id' => $meter_id])
        </div>

    </div>

</div>

<script type="text/javascript">
    $(function () {

    });
</script>