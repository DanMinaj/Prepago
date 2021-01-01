<?php

function getLandlords($schemeNumber)
{
    $customers = Customer::where('status', '=', 1)->where('scheme_number', '=', $schemeNumber)->get();
    $deletedLandlordsList = new \Illuminate\Database\Eloquent\Collection();
    foreach ($customers as $customer)
    {
        //get deleted landlords list (needed if we're deleting a normal customer)
        $deletedLandlordsList[$customer->id] = Customer::onlyTrashed()->where('role', '=', 'landlord')->where('meter_ID', '=', $customer->meter_ID)->get(['id', 'username']);
    }

    return $deletedLandlordsList;
}