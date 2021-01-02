</div>

<div><br/></div>
<h1>Credit Amount In System</h1>
            

<div class="admin2">
<a href="{!! URL::to('system_reports') !!}">System Reports</a> > <a href="{!! URL::to('system_reports/topup_repots') !!}">Top-Up Reports</a> > Credit Amount In System
<h3>Total Balance: {!! $currencySign !!}<?php echo $balance ?></h3>
<h3><a href="<?php echo "{$csv_url}"?>">Download CSV</a></h3>
<div class="cl"></div>