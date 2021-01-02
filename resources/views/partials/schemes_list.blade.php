@foreach ($schemes as $scheme)
    <label style="display:inline;margin-bottom:0px;font-size:12px" for="{!! "scheme_" . $scheme->id !!}">
        <input type="checkbox" value="{!! $scheme->id !!}" name="schemes[]" id="{!! "scheme_" . $scheme->id !!}" {!! in_array($scheme->id, $userSchemes) ? 'checked="checked"' : "" !!} />
        <strong>{!! $scheme->scheme_nickname ? : $scheme->company_name !!}</strong>
    </label>
    <br />
    <div style="padding-left: 20px;">{!! $scheme->scheme_description !!}</div>
@endforeach