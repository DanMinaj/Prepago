</div>

<div><br/></div>
<h1>Bill Reports</h1>

<div style="float: right">

    <form method="post" action="<?php echo URL::to('system_reports/bill_reports') ?>"class="form-inline" style="float:left">
        
		
		<label>From</label>
        <input id="from" name="from" @if((Input::get('from')))value="{!!Input::get('from')!!}"@endif type="text">
        <label>To</label>
        <input id="to" name="to" @if((Input::get('to')))value="{!!Input::get('to')!!}"@endif type="text">
        <input type="submit" value="search" class="btn-success"/>
		
		
    </form>

</div>

<script type="text/javascript">
    $(document).ready(function()
    {
        $("#to").datepicker({ dateFormat: 'dd-mm-yy' });
        $("#from").datepicker({ dateFormat: 'dd-mm-yy' });
    });

</script>

<div class="admin2">
    <a href="{!! URL::to('system_reports') !!}">System Reports</a> > Bill Reports
    <div class="cl"></div>
    <h3><a href="<?php echo $csv_url ?>">Download CSV</a></h3>
    <div id="all_customers">
        <div>
            Click on each checkbox to hide/show the corresponding column<br />
            <ul class="table-options">
                <li><input type="checkbox" checked="checked" id="customer_name"> Customer Name</li>
                <li><input type="checkbox" checked="checked" id="username"> Username</li>
                <li><input type="checkbox" checked="checked" id="email"> Email</li>
                <li><input type="checkbox" checked="checked" id="address"> Address</li>
                <li><input type="checkbox" checked="checked" id="units"> Units</li>
                <li><input type="checkbox" checked="checked" id="barcode"> Barcode</li>
                <li><input type="checkbox" checked="checked" id="payment_total"> Payment total</li>
                <li><input type="checkbox" id="payments"> Payments</li>
            </ul>
        </div>
        <table id="sortthistable" class="table bill-report" style="border: 1px solid #ddd;">
            <thead>
                <tr>
                    <th style="width: 15%">Customer Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Units</th>
                    <th>Barcode</th>
                    <th style="width: 10%">Payment total</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($customers as $customer)
                @include('partials.bill_report_section')
            @endforeach
            <tr>
                <td colspan="7" style="background-color: cornflowerblue; color: #FFF">BLUE METERS</td>
            </tr>
            @foreach ($blue_customers as $customer)
                @include('partials.bill_report_section')
            @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        //$("#sortthistable").tablesorter();

        $(".customer_payments_content").attr('colspan', $('table.bill-report thead tr th').length);

        $("input[type='checkbox']").click(function() {

            var checked = $(this).is(":checked");
            var index = $(this).parent().index();

            if ($(this).attr('id') == 'payments')
            {
                if (checked)
                {
                    $(".customer_payments").show();
                }
                else
                {
                    $(".customer_payments").hide();
                }
            }

            if(checked)
            {
                $('table.bill-report thead tr th:eq(' + index + ')').show();
            }
            else
            {
                $('table.bill-report thead tr th:eq(' + index + ')').hide();
            }
            $('table.bill-report tbody tr').not('.customer_payments').each(function() {
                if (!$(this).parents('.customer_payments').length)
                {
                    if(checked) {
                        $(this).find("td").eq(index).show();
                    } else {
                        $(this).find("td").eq(index).hide();
                    }
                }
            });

            if ($('table.bill-report thead tr th:visible').length == 0)
            {
                $(".customer_payments").hide();
            }
            else
            {
                if ($("#payments").is(':checked'))
                {
                    $(".customer_payments").show();
                }
                $(".customer_payments_content").attr('colspan', $('table.bill-report thead tr th:visible').length);
            }
        });
    });
</script>