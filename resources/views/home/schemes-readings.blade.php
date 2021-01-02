</div>

<div class="cl"></div>
<h1>Schemes Readings Export</h1>
<div class="cl"></div>

@include('includes.notifications')

<div>
    <table class="table table-bordered table-striped">
        <tr>
            <th>Scheme</th>
            <th></th>
        </tr>
        @foreach ($schemes as $scheme)
            <tr>
                <td>{!! $scheme->company_name !!} ({!! $scheme->scheme_nickname !!})</td>
                <td><a href="{!! url('schemes-readings/export/' . $scheme->scheme_number) !!}">Export Readings</a></td>
            </tr>
        @endforeach
    </table>

</div>

</div>
</div>