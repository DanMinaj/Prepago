<?php

function getSchemeSetupCountryVersions()
{
    $data = [];

    $data['shut_off_periods']                   = '{"Days":[{"Day":"Monday","Shut_Off_Start":"09:00","Shut_Off_End":"17:00","Active":1},{"Day":"Tuesday","Shut_Off_Start":"09:00","Shut_Off_End":"17:00","Active":1},{"Day":"Wednesday","Shut_Off_Start":"09:00","Shut_Off_End":"17:00","Active":1},{"Day":"Thursday","Shut_Off_Start":"09:00","Shut_Off_End":"17:00","Active":1},{"Day":"Friday","Shut_Off_Start":"09:00","Shut_Off_End":"17:00","Active":1},{"Day":"Saturday","Shut_Off_Start":"09:00","Shut_Off_End":"17:00","Active":1}]}';
    $data['isLive']                             = 1;
    $data['sms_disabled']                       = 0;
    $data['IOU_denied_message']                 = 'You currently have no IOU\'s left to use or your balance is to low. Please top up.';
    $data['shut_off_message']                   = 'Your service has been shut off. Please top up or use an IOU if possible to resume service.';
    $data['shut_off_warning_message']           = 'You have been scheduled to shut off. Please top up or use an IOU if possible, to avoid interruption to service.';
    $data['barcode_message']                    = 'Your barcode is:';
    $data['topup_message']                      = 'You have successfully topped up. You new balance is "b".';


    $data['Ireland']['vat_rate']                = 0.135;
    $data['Ireland']['currency_code']           = 978;
    $data['Ireland']['currency_sign']           = '€';
    $data['Ireland']['IOU_text']                = 'You can avail of a €5 IOU. A 15c service charge applies.';
    $data['Ireland']['IOU_extra_text']          = 'You can avail of a €5 IOU extra. A 20c service charge applies.';
    $data['Ireland']['FAQ']                     = '[{"question":"Where do I top-up?","answer":"This App has a map to your nearest top-up location, select Top-up, and then Top-up Location. This shows the nearest location anywhere in Ireland!"},
													{"question":"What is an IOU?","answer":"An IOU is an additional €5 credit which is taken back when you next top-up - remember, it must be requested before your account balance drops below €0."},
													{"question":"What is the minimum top-up?","answer":"The minimum top-up is €10.00 by barcode in retail shop and €25 by paypal inapp - remember to check this is enough credit to renew service."},
													{"question":"What is friendly credit?","answer":"To ensure you will have heat when you wake up in the morning and over the weekend your service does not shut off when shops maybe shut."},
													{"question":"When is friendly credit?","answer":"Friendly credit is at night from 5.00pm to 9.00am [17.00 to 09.00] Monday to Friday and also the weekend, all day on Saturday and Sunday. It is also available for selected bank holidays."},
													{"question":"What does \"Used yesterday\" mean?","answer":"This is the value of the heat used yesterday, over the full 24 hours. This is a good guide to budgeting for today\'s and tomorrow\'s usage."},
													{"question":"What does \"Credit Now, balance remaining\" mean?","answer":"This is the value you have in your account."},
													{"question":"When will service be shut off?","answer":"Service is withdrawn for no credit any weekday between 9.00am and 5.00pm [09.00 and 17.00]."},
													{"question":"Who do I contact for reconnection?","answer":"Service will be restored when you buy credit, please make sure you add enough credit to pay back any friendly credit or IOU taken."},
													{"question":"What does \"IOU Available\" mean?","answer":"This feature is available when you have €3 or less in credit. The €5 is deducted immediately when you next top-up. When it is used; the button goes red. It is blue at all other times."},
													{"question":"What is \"Barcode\"?","answer":"This needs to be handed to the operator when you go to top-up. We also include the number for your top-up number which can be used by your Payzone outlet."},
													{"question":"What is \"Top-up Location\"?","answer":"This is a map showing you the nearest Payzone top-up locations."},
													{"question":"What is \"Recent top-ups\\?\"","answer":"This is a list of your most recent successful top-ups.\n"},
													{"question":"What is away mode / remote control?","answer":"Away mode allows you to switch on your heat when you are on the way home, give it a boost - or you can switch it off for when you\'re away – you can even set it while you\'re away!"},
													{"question":"What is the unit cost of heat?","answer":"Heat is charged at %TARIFF_1% cents per kWh. Rounding will occur in charges."},
													{"question":"Why have I got District Heating?","answer":"All new buildings have to comply with Part L of the Building Regulations (2011) and having an efficient district heating system facilitates this."},
													{"question":"What is the Daily Delivery Charge?","answer":"This is a fixed daily charge to ensure the availability of heating and hot water, customer service and support  24/7/365. It is currently 93.07 cent per day. Rounding will occur in charges."},
													{"question":"What do I do if I have a query regarding my Snugzone account or payments?","answer":"Contact the system operator, Kaizen Energy, by email to billing@kaizenenergy.ie or by phone to (01) 685 3516."},
													{"question":"What do I do if I have a problem with the heating and/or hot-water in my apartment?","answer":"Visit the customer information section of the Kaizen Energy website (www.kaizenergy.ie) and choose your development to find the latest information and technical FAQs."},
													{"question":"Can I charge my EV [Electric Vehicle]?","answer":"You may use PrepayGO.com to recharge your Electric Vehicle in our shared EV bays. Charges may apply."},]';
    $data['Ireland']['balance_message']         = 'The balance of your account is "b" Euro.';
    $data['Ireland']['IOU_message']             = 'IOU successful.Service charge of 15c deducted from balance.';
    $data['Ireland']['IOU_extra_message']       = 'IOU Extra successful.Service charge of 15c deducted from balance.';
    $data['Ireland']['rates_message']           = 'Standing Charge "2" Euro. Per kWh charge "1" euro. Daily arrears repayment "a".';
    $data['Ireland']['credit_warning_message']  = 'Your credit is running low. Please top up to avoid interruption to service. Balance "b" Euro.';


    $data['UK']['vat_rate']                     = 0.05;
    $data['UK']['currency_code']                = 826;
    $data['UK']['currency_sign']                = '€';
    $data['UK']['IOU_text']                     = 'You can avail of a €5 IOU. A 15p service charge applies.';
    $data['UK']['IOU_extra_text']               = 'You can avail of a €5 IOU extra. A 20p service charge applies.';
    $data['UK']['FAQ']                          = '[{"question":"Where do I top-up?","answer":"This App has a map to your nearest top-up location, select Top-up, and then Top-up Location. This shows the nearest location anywhere in Ireland!"},
													{"question":"What is an IOU?","answer":"An IOU is an additional £5 credit which is taken back when you next top-up - remember, it must be requested before your account balance drops below £0."},
													{"question":"What is the minimum top-up?","answer":"The minimum top-up is £10.00 by barcode in retail shop and £25 by paypal inapp - remember to check this is enough credit to renew service."},
													{"question":"What is friendly credit?","answer":"To ensure you will have heat when you wake up in the morning and over the weekend your service does not shut off when shops maybe shut."},
													{"question":"When is friendly credit?","answer":"Friendly credit is at night from 5.00pm to 9.00am [17.00 to 09.00] Monday to Friday and also the weekend, all day on Saturday and Sunday. It is also available for selected bank holidays."},
													{"question":"What does \"Used yesterday\" mean?","answer":"This is the value of the heat used yesterday, over the full 24 hours. This is a good guide to budgeting for today\'s and tomorrow\'s usage."},
													{"question":"What does \"Credit Now, balance remaining\" mean?","answer":"This is the value you have in your account."},
													{"question":"When will service be shut off?","answer":"Service is withdrawn for no credit any weekday between 9.00am and 5.00pm [09.00 and 17.00]."},
													{"question":"Who do I contact for reconnection?","answer":"Service will be restored when you buy credit, please make sure you add enough credit to pay back any friendly credit or IOU taken."},
													{"question":"What does \"IOU Available\" mean?","answer":"This feature is available when you have £3 or less in credit. The £5 is deducted immediately when you next top-up. When it is used; the button goes red. It is blue at all other times."},
													{"question":"What is \"Barcode\"?","answer":"This needs to be handed to the operator when you go to top-up. We also include the number for your top-up number which can be used by your Payzone outlet."},
													{"question":"What is \"Top-up Location\"?","answer":"This is a map showing you the nearest Payzone top-up locations."},
													{"question":"What is \"Recent top-ups\\?\"","answer":"This is a list of your most recent successful top-ups.\n"},
													{"question":"What is away mode / remote control?","answer":"Away mode allows you to switch on your heat when you are on the way home, give it a boost - or you can switch it off for when you\'re away – you can even set it while you\'re away!"},
													{"question":"What is the unit cost of heat?","answer":"Heat is charged at %TARIFF_1% cents per kWh. Rounding will occur in charges."},
													{"question":"Why have I got District Heating?","answer":"All new buildings have to comply with Part L of the Building Regulations (2011) and having an efficient district heating system facilitates this."},
													{"question":"What is the Daily Delivery Charge?","answer":"This is a fixed daily charge to ensure the availability of heating and hot water, customer service and support  24/7/365. It is currently 93.07 cent per day. Rounding will occur in charges."},
													{"question":"What do I do if I have a query regarding my Snugzone account or payments?","answer":"Contact the system operator, Kaizen Energy, by email to billing@kaizenenergy.ie or by phone to (01) 685 3516."},
													{"question":"What do I do if I have a problem with the heating and/or hot-water in my apartment?","answer":"Visit the customer information section of the Kaizen Energy website (www.kaizenergy.ie) and choose your development to find the latest information and technical FAQs."},
													{"question":"Can I charge my EV [Electric Vehicle]?","answer":"You may use PrepayGO.com to recharge your Electric Vehicle in our shared EV bays. Charges may apply."},]';
													
    $data['UK']['balance_message']              = 'The balance of your account is "b" Pound.';
    $data['UK']['IOU_message']                  = 'IOU successful.Service charge of 15p deducted from balance.';
    $data['UK']['IOU_extra_message']            = 'IOU Extra successful.Service charge of 15p deducted from balance.';
    $data['UK']['rates_message']                = 'Standing Charge "2" Pound. Per kWh charge "1" Pound. Daily arrears repayment "a".';
    $data['UK']['credit_warning_message']       = 'Your credit is running low. Please top up to avoid interruption to service. Balance "b" Pound.';


    return $data;
}


function displayScuType($scuTypeAbbrev)
{
	$scuType = 'M-Bus + SCU';
    switch ($scuTypeAbbrev)
    {
        case 'a' : $scuType = 'M-Bus + SCU'; break;
        case 'n' : $scuType = 'Testing'; break;
        case 'd' : $scuType = 'SCU Only'; break;
    }

    return $scuType;
}