</div>
<div class="admin2">
    <h1>Tariff Settings</h1>
    <div class="cl">&nbsp;</div>

    @if (Auth::user()->schemes && Auth::user()->schemes->count() > 1)
        <ul class="nav nav-pills">
            <li class="{!! $all ? '' : 'active' !!}">
                <a href="{!! URL::to('settings/tariff') !!}">Scheme {!! $currentScheme->scheme_nickname ? : $currentScheme->company_name !!}</a>
            </li>
            <li class="{!! $all ? 'active' : '' !!}">
                <a href="{!! URL::to('settings/tariff/all') !!}">All Schemes</a>
            </li>
        </ul>
    @endif

    <table class="table table-bordered" >
        <tr>
            <th></th>
            <th>Current Tariffs</th>
            @if ($all)
                <th>Scheme</th>
            @endif
        </tr>

        @if(!$tarrifs){
            <tr><td colspan='@if ($all) 3 @else 2 @endif'>There are no data to show<td><tr>
        @else
            @foreach ($tarrifs as $tarrif)
                @for ($i = 1; $i < 6; $i++)
                    <?php $tariffName = "tariff_" . $i . "_name"; ?>
                    <?php $tariffIndex = "tariff_" . $i; ?>
                    @if ($tarrif->{$tariffName})
                        <tr>
                            <td>{{ $tarrif->{$tariffName} }}</td>
                            <td>{{ $tarrif->{$tariffIndex} }}</td>
                            @if ($all)
                                <td>{{ $tarrif->scheme->scheme_nickname ? : $tarrif->scheme->company_name }}</td>
                            @endif
                        </tr>
                    @endif
                @endfor
            @endforeach
        @endif
    </table>

    <form action="{!! URL::to('settings/tarrif/add') !!}" method="POST" id="tarrifadd">

        <input type="hidden" name="tariff-added" id="tariff-added" value="{!! \Session::get('tarrif-added') !!}" />
        <input type="hidden" name="tariff-all" id="tariff-all" value="{!! $all !!}" />

        <p style="font-size: 1.5em;">Add a future tariff change</p>

        <table>
            <tr>
                <td>Tariff to change:</td>
                <td>
                    <select name="tarriftype">
                        @foreach ($tarrifs as $tarrif)
                            @for ($i = 1; $i < 6; $i++)
                                <?php $tariffName = "tariff_" . $i . "_name"; ?>
                                @if ($tarrif->{$tariffName})
                                    <option value="scheme_{{ $tarrif->scheme_number }}_tariff_{{ $i }}">{{ $tarrif->{$tariffName} . ($all ? " (" . ($tarrif->scheme->scheme_nickname ? : $tarrif->scheme->company_name) . ")" : '') }}</option>
                                @endif
                            @endfor
                        @endforeach
                    </select>
                </td>
            </tr>
            <tr><td>Change date:</td><td><input type="text" name="fromDate" id="fromDate"></td></tr>
            <tr><td>New tarrif:</td><td><input type="text" name="newValue" id="newValue"></td></tr>
            <tr><td>&nbsp;</td><td>
                    <div class="form-actions">
                        <a href="#myModal" class="btn btn-primary" data-toggle="modal">Add</a>

                    </div>
                </td></tr>
        </table>
        <div id="myModal" class="modal hide fade" >
            <div class="modal-header">
                <h3 id="myModalLabel">Future tariffs.</h3>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" role="form">
                        <p>Add future tariff change?</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                <a href="#" class="btn btn-danger" onclick="issue()">Yes</a>
            </div>
        </div>


    </form>


    <ul class="nav nav-tabs">
        <li class="active"><a href="#future" data-toggle="tab">Future Tariffs</a></li>
        <li><a href="#past" data-toggle="tab">Past Tariffs</a></li>
    </ul>

    <div class="tab-content">
        <div id="future" class="tab-pane active">
            @if($new_tarrifs)
                <table class="table table-bordered" >
                    <th>Tariff to change</th>
                    <th>Change date</th>
                    <th>New Value</th>
                    @if ($all)
                        <th>Scheme</th>
                    @endif
                    <th>&nbsp;</th>
                    @foreach ($new_tarrifs as $tarrif)
                        <?php $tariffToChange = $tarrif->tariff_to_change . '_name'; ?>
                        @if($tarrif->tarrif->{$tariffToChange})

                            <tr>
                                <td>{{ $tarrif->tarrif->{$tariffToChange} }}</td>
                                <td>{{ $tarrif->change_date }}</td>
                                <td>{{ $tarrif->new_value }}</td>
                                @if ($all)
                                    <td>{{ $tarrif->scheme->scheme_nickname  ? : $tarrif->scheme->company_name }}</td>
                                @endif
                                <td><a href="#cancelModal{{ $tarrif->id }}" role="button" class="btn btn-danger" data-toggle="modal">Cancel</a></td>
                            </tr>

                            <div id="cancelModal{{ $tarrif->id }}" class="modal hide fade" >
                                <div class="modal-header">

                                    <h3 id="cancelModalLabel">Cancel tarrif change</h3>
                                </div>
                                <div class="modal-body">
                                    <p>You are about to cancel a future tarrif change, continue?</p>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                                    <a href="{!! URL::to('settings/tarrif/cancel/' . $tarrif->id . ($all ? '/all' : '')) !!}" class="btn btn-danger">Yes</a>
                                </div>
                            </div>

                        @endif
                    @endforeach
                </table>
            @else
                <p>No future tariff changes.</p>
            @endif

        </div>

        <div id="past" class="tab-pane">
            @if($past_tarrifs)
                <table class="table table-bordered" >
                    <th>Past Tariff Change</th>
                    <th>Change Date</th>
                    <th>Value</th>
                    @if ($all)
                        <th>Scheme</th>
                    @endif

                    @foreach ($past_tarrifs as $tarrif)
                        <?php $tariffToChange = $tarrif->tariff_to_change . '_name'; ?>
                        @if($tarrif->tarrif->{$tariffToChange})
                            <tr>
                                <td>{{ $tarrif->tarrif->{$tariffToChange} }}</td>
                                <td>{{ $tarrif->change_date }}</td>
                                <td>{{ $tarrif->new_value }}</td>
                                @if ($all)
                                    <td>{{ $tarrif->scheme->scheme_nickname  ? : $tarrif->scheme->company_name }}</td>
                                @endif
                            </tr>
                        @endif
                    @endforeach
                </table>
            @endif
        </div>

    </div>

</div>

<div class="modal hide fade" id="tariff-added-modal">
    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h3>Tariff Changes</h3>
    </div>
    <div class="modal-body">
        <p>Do you want to tell your customers that you have changed the tariffs?</p>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">No</button>
        <a href="/customer_messaging/scheme{!! $all ? '/all' : '' !!}" class="btn btn-primary">Yes</a>
    </div>
</div>

<script type="text/javascript">

    $(window).load(function()
    {
        if ($("#tariff-added").val())
        {
            $("#tariff-added-modal").modal('show');
        }
    });

    $(document).ready(function()
    {
        $("#fromDate").datepicker({ dateFormat: 'yy-mm-dd', minDate: 1 });
    });

    function issue() {
        var newValue = $('#newValue').val();
        var fromDate = $('#fromDate').val();

        if(newValue == '' || fromDate == '')
        {
            alert('Please fill out all fields');
            return false;
        }
        else{
            $('#tarrifadd').submit();
        }

    }

</script>
</div>