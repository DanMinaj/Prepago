<div style="width: 50%; float: left">

    {!! Form::hidden('action', $action) !!}

    {!! Form::label('country', 'Country') !!}
    {!! Form::select('country', ['Ireland' => 'Ireland', 'UK' => 'UK']) !!}
    {!! $errors->first('country', '<div class="formerror">:message</div>') !!}

    {!! Form::label('scheme_nickname', 'Scheme Nickname') !!}
    {!! Form::text('scheme_nickname') !!}
    {!! $errors->first('scheme_nickname', '<div class="formerror">:message</div>') !!}

    {!! Form::label('scheme_number', 'Scheme Number') !!}
    {!! Form::text('scheme_number') !!}
    {!! $errors->first('scheme_number', '<div class="formerror">:message</div>') !!}

    {!! Form::label('scheme_description', 'Scheme Description') !!}
    {!! Form::textarea('scheme_description', null, ['size' => '30x2']) !!}
    {!! $errors->first('scheme_description', '<div class="formerror">:message</div>') !!}

    {!! Form::label('company_name', 'Company Name') !!}
    {!! Form::text('company_name') !!}
    {!! $errors->first('company_name', '<div class="formerror">:message</div>') !!}

    {!! Form::label('company_address', 'Company Address') !!}
    {!! Form::text('company_address') !!}
    {!! $errors->first('company_address', '<div class="formerror">:message</div>') !!}

    {!! Form::label('sms_password', 'SMS Password') !!}
    {!! Form::password('sms_password') !!}
    {!! $errors->first('sms_password', '<div class="formerror">:message</div>') !!}

    {!! Form::label('accounts_email', 'Account Email') !!}
    {!! Form::text('accounts_email') !!}
    {!! $errors->first('accounts_email', '<div class="formerror">:message</div>') !!}

    {!! Form::label('vat_rate', 'VAT Rate') !!}
    {!! Form::text('vat_rate') !!}
    {!! $errors->first('vat_rate', '<div class="formerror">:message</div>') !!}

    {!! Form::label('currency_code', 'Currency Code') !!}
    {!! Form::text('currency_code') !!}
    {!! $errors->first('currency_code', '<div class="formerror">:message</div>') !!}

    {!! Form::label('currency_sign', 'Currency Sign') !!}
    {!! Form::text('currency_sign') !!}
    {!! $errors->first('currency_sign', '<div class="formerror">:message</div>') !!}

    {!! Form::label('street2', 'Street') !!}
    {!! Form::text('street2') !!}
    {!! $errors->first('street2', '<div class="formerror">:message</div>') !!}

    {!! Form::label('town', 'Town') !!}
    {!! Form::text('town') !!}
    {!! $errors->first('town', '<div class="formerror">:message</div>') !!}

    {!! Form::label('county', 'County') !!}
    {!! Form::text('county') !!}
    {!! $errors->first('county', '<div class="formerror">:message</div>') !!}

    {!! Form::label('post_code', 'Post Code') !!}
    {!! Form::text('post_code') !!}
    {!! $errors->first('post_code', '<div class="formerror">:message</div>') !!}

    <!--
    {!! Form::label('start_date', 'Start Date') !!}
    {!! Form::text('start_date') !!}
    {!! $errors->first('start_date', '<div class="formerror">:message</div>') !!}
    -->

    {!! Form::label('service_type', 'Service Type') !!}
    {!! Form::text('service_type') !!}
    {!! $errors->first('service_type', '<div class="formerror">:message</div>') !!}

</div>

<div style="width: 50%; float: left">
    {!! Form::label('daily_customer_charge', 'Daily Customer Charge') !!}
    {!! Form::text('daily_customer_charge') !!}
    {!! $errors->first('daily_customer_charge', '<div class="formerror">:message</div>') !!}

    {!! Form::label('commission_charge', 'Commission Charge') !!}
    {!! Form::text('commission_charge') !!}
    {!! $errors->first('commission_charge', '<div class="formerror">:message</div>') !!}

    {!! Form::label('prepago_registered_apps_charge', 'Prepago Registered Apps Charge') !!}
    {!! Form::text('prepago_registered_apps_charge') !!}
    {!! $errors->first('prepago_registered_apps_charge', '<div class="formerror">:message</div>') !!}

    {!! Form::label('IOU_chargeable', 'IOU Chargeable') !!}
    {!! Form::text('IOU_chargeable') !!}
    {!! $errors->first('IOU_chargeable', '<div class="formerror">:message</div>') !!}

    {!! Form::label('IOU_amount', 'IOU Amount') !!}
    {!! Form::text('IOU_amount') !!}
    {!! $errors->first('IOU_amount', '<div class="formerror">:message</div>') !!}

    {!! Form::label('IOU_charge', 'IOU Charge') !!}
    {!! Form::text('IOU_charge') !!}
    {!! $errors->first('IOU_charge', '<div class="formerror">:message</div>') !!}

    {!! Form::label('IOU_text', 'IOU Text') !!}
    {!! Form::textarea('IOU_text', null, ['size' => '30x2']) !!}
    {!! $errors->first('IOU_text', '<div class="formerror">:message</div>') !!}

    {!! Form::label('IOU_extra_amount', 'IOU Extra Amount') !!}
    {!! Form::text('IOU_extra_amount') !!}
    {!! $errors->first('IOU_extra_amount', '<div class="formerror">:message</div>') !!}

    {!! Form::label('IOU_extra_charge', 'IOU Extra Charge') !!}
    {!! Form::text('IOU_extra_charge') !!}
    {!! $errors->first('IOU_extra_charge', '<div class="formerror">:message</div>') !!}

    {!! Form::label('IOU_extra_text', 'IOU Extra Text') !!}
    {!! Form::textarea('IOU_extra_text', null, ['size' => '30x2']) !!}
    {!! $errors->first('IOU_extra_text', '<div class="formerror">:message</div>') !!}

    {!! Form::label('prepage_SMS_charge', 'Prepage SMS Charge') !!}
    {!! Form::text('prepage_SMS_charge') !!}
    {!! $errors->first('prepage_SMS_charge', '<div class="formerror">:message</div>') !!}

    {!! Form::label('prepago_new_admin_charge', 'Prepago New Admin Charge') !!}
    {!! Form::text('prepago_new_admin_charge') !!}
    {!! $errors->first('prepago_new_admin_charge', '<div class="formerror">:message</div>') !!}

    {!! Form::label('prepago_in_app_message_charge', 'Prepago In App Message Charge') !!}
    {!! Form::text('prepago_in_app_message_charge') !!}
    {!! $errors->first('prepago_in_app_message_charge', '<div class="formerror">:message</div>') !!}

    {!! Form::label('prefix', 'Prefix') !!}
    {!! Form::text('prefix') !!}
    {!! $errors->first('prefix', '<div class="formerror">:message</div>') !!}

    {!! Form::label('unit_abbreviation', 'Unit Abbreviation') !!}
    {!! Form::text('unit_abbreviation') !!}
    {!! $errors->first('unit_abbreviation', '<div class="formerror">:message</div>') !!}

    {!! Form::label('scu_type', 'SCU Type') !!}
    @if ($action == 'create')
        {!! Form::select('scu_type', ['' => 'SELECT', 'a' => 'M-Bus + SCU', 'n' => 'Testing', 'd' => 'SCU Only']) !!}
        {!! $errors->first('scu_type', '<div class="formerror">:message</div>') !!}
    @else
        <strong>{!! displayScuType($scheme['scu_type']) !!}</strong>
        <br /><br />
    @endif

    <div id="sim_cards" style="display: none">
        {!! Form::label('ICCID', 'ICCID') !!}
        {!! Form::text('ICCID') !!}
        {!! $errors->first('ICCID', '<div class="formerror">:message</div>') !!}

        {!! Form::label('MSISDN', 'MSISDN') !!}
        {!! Form::text('MSISDN') !!}
        {!! $errors->first('MSISDN', '<div class="formerror">:message</div>') !!}

        {!! Form::label('IP_Address', 'IP Address') !!}
        {!! Form::text('IP_Address') !!}
        {!! $errors->first('IP_Address', '<div class="formerror">:message</div>') !!}

        {!! Form::label('Name', 'Name') !!}
        {!! Form::text('Name') !!}
        {!! $errors->first('Name', '<div class="formerror">:message</div>') !!}

        {!! Form::label('software_version', 'Software Version') !!}
        {!! Form::text('software_version') !!}
        {!! $errors->first('software_version', '<div class="formerror">:message</div>') !!}

        {!! Form::label('in_use', 'In Use') !!}
        {!! Form::text('in_use') !!}
        {!! $errors->first('in_use', '<div class="formerror">:message</div>') !!}
    </div>
</div>

<style type="text/css">
    .formerror {
        color: red;
        margin-bottom: 10px;
    }
</style>

<script type="text/javascript">
    $(function()
    {
        //$("#start_date").datepicker({ dateFormat: 'yy-mm-dd' });
        if ($("#scu_type").val() == 'a')
        {
            $("#sim_cards").show();
        }

        $("#scu_type").change(function()
        {
            if ($("#scu_type").val() == 'a')
            {
                $("#sim_cards").show();
            }
            else
            {
                $("#sim_cards").hide();
            }
        });
    });
</script>