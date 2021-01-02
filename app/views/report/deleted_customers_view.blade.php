</div>

<div><br/></div>
<h1>@if ($role === 'normal') Deleted Customers ({{ $from }} - {{ $to }}) @else Inactive Landlords @endif</h1>

<div style="float: right">

    <form method="get" action="" class="form-inline" style="float:left">
        <label>From</label>
        <input id="from" value='@if(isset($from)){{$from}}@endif' name="from" type="text">
        <label>To</label>
        <input id="to" value='@if(isset($to)){{$to}}@endif' name="to" type="text">
        <input type="submit" value="search" class="btn-success"/>

    </form>

</div>

<div class="admin2">
    <a href="{{ URL::to('system_reports') }}">System Reports</a> > @if ($role === 'normal') Deleted Customers Report @else Inactive Landlords Report @endif
    <h3><a href="{{ $csv_url }}?from={{ $from }}&to={{ $to }}">Download CSV</a></h3>
    <table id="sortthistable" class="table table-bordered">
        <thead>
            <tr>
                <th width="30%">Deleted at</th>
                <th width="5%">Username</th>
                <th width="5%">Email</th>
                <th width="15%">Adddress</th>
                <th width="10%">Mobile Number</th>
                <th width="10%">First Name</th>
                <th width="10%">Surname</th>
                <th width="5%">Balance</th>
                <th width="5%">End reading</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td>
					{{{ $customer->deleted_at }}}
					<br/>
					<b>({{ Carbon\Carbon::parse($customer->deleted_at)->diffForHumans() }})</b>
					</td>
                    <td>{{{ $customer->username }}}</td>
                    <td>{{{ $customer->email_address }}}</td>
                    <td>
                        {{{ isset($customer->house_number_name) ? $customer->house_number_name . ', ' : '' }}}
                        {{{ isset($customer->street1) ? $customer->street1 . ', ' : '' }}}
                        {{{ isset($customer->street2) ? $customer->street2 . ', ' : '' }}}
                        {{{ isset($customer->town) ? $customer->town . ', ' : ''  }}}
                        {{{ isset($customer->county) ? $customer->county . ($customer->country ? ', ' : '') : '' }}}
                        {{{ isset($customer->country) ? $customer->country : '' }}}
                    </td>
                    <td>{{{ $customer->mobile_number }}}</td>
                    <td>{{{ $customer->first_name }}}</td>
                    <td>{{{ $customer->surname }}}</td>
                    <td>{{ $currencySign }}{{{ $customer->balance }}}</td>
                    <td>
						@if($customer->districtMeter)
						{{ $customer->districtMeter->sudo_reading }} kWh
						@endif
					</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
<script type="text/javascript">

$(document).ready(function()
    {
        $("#to").datepicker({ dateFormat: 'yy-mm-dd' });
        $("#from").datepicker({ dateFormat: 'yy-mm-dd' });
    });
    $(document).ready(function() {

        $("#sortthistable").tablesorter(); 

        $('#allitem').click(function() {
            if ($(this).is(':checked')) {
                
                //alert('clicked');
                var url = "<?php echo URL::to('system_reports/topup_reports/customer_topup_history_by_ajax'); ?>";
                $.ajax({
                    type: 'GET',
                    url: url,
                    data: 'html',
                    success: function(html, textStatus) {

                        $('#all_customers').html(html);
                        //location.reload();
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        alert('An error occurred! ' + textStatus);
                    }
                });
            }
        });
    });



</script>