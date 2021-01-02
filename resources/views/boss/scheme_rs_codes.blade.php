</div>

<div><br/></div>
<h1>RS Codes for scheme {{ strtoupper($scheme_name) }}</h1>

</div>
<div class="cl"></div>
<div class="admin2">
    <div style="margin-bottom: 10px;"><a href="{!! URL::to('boss' . ($user->id != Auth::user()->id ? '/' . $user->id : '')) !!}">BOSS</a> &raquo; RS Codes for scheme {{ strtoupper($scheme_name) }}</div>

    <h3>RS Codes</h3>

    @if (!$rs_codes)
        <div>No RS Codes Available</div>
    @else
        @foreach ($rs_codes as $rsCode)
            <div style="font-size: 14px; font-weight: bold; margin-bottom: 10px;">
                <strong>{{ $rsCode }}</strong>
            </div>
        @endforeach
    @endif

</div>

</div>