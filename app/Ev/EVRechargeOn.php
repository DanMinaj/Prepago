<?php

class EVRechargeOn extends EVRechargeManager {

	protected $initiatedBySameCustomer = false;

    public function handle()
    {
        if (!$this->performChecksAndInit())
        {
            return $this->errorResponse();
        }
		
		if ($this->initiatedBySameCustomer) {
            return [
                'ev_recharge_status' => 'on',
                'flag_message' => 0,
               'customer' => $this->customer,
			   'meter'	=> $this->meter,
            ];
        }

        $this->emptyMeterReading();

        $this->associateDistrictHeatingMeterWithCustomer();

        $this->meter->block();

        $scheme = Scheme::where('scheme_number', '=', $this->customer->scheme_number)->first();
        $schemePrefix = $scheme ? $scheme->prefix : null;

        $this->meter->openValve($this->customer->scheme_number);
		
		if ($remoteControlStatus = $this->meter->remoteControlStatus) {
            $remoteControlStatus->heating_on = 0;
            $remoteControlStatus->save();
        }

        return [
            'ev_recharge_status' => 'on',
            'flag_message' => 0,
            'error' => '',
			'customer' => $this->customer,
			'meter'	=> $this->meter,
        ];
    }

    protected function performChecksAndInit()
    {
        if (!parent::performChecksAndInit())
        {
            return false;
        }
		
		if ($this->initiatedBySameCustomer = $this->isEVRechargeOnInitiatedBySameCustomer() && $this->meter->rechargeInProgress()) {
            return true;
        }

        if ($this->rechargeInProgress())
        {
            return false;
        }

        if (!$this->isAvailableBalanceEnough())
        {
            $this->errorMsg = 'Insufficient funds required for the recharge process.';
            return false;
        }
		
		if ( ! $this->meter->inUse()) {
            $this->errorMsg = 'This EV station is inactive.';
            return false;
        }

        return true;
    }

    protected function rechargeInProgress()
    {
        if ($this->meter->rechargeInProgress())
        {
            $this->errorMsg = 'The EV meter with RS Code ' . $this->rsCode . ' is currently in use';
            return true;
        }

        return false;
    }

    protected function isAvailableBalanceEnough()
    {
        return $this->customer->balance - $this->customer->maximum_recharge_fee < 0 ? false : true;
    }

    protected function emptyMeterReading()
    {
        $this->districtHeatingMeter->emptyLatestReading();
    }

    protected function associateDistrictHeatingMeterWithCustomer()
    {
        $this->customer->associateDistrictHeatingMeter($this->districtHeatingMeter->meter_ID);
    }
	
	protected function isEVRechargeOnInitiatedBySameCustomer()
    {
        return $this->customer->ev_meter_ID == $this->districtHeatingMeter->meter_ID;
    }

}