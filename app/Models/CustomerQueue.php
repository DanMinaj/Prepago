<?php
use Illuminate\Database\Eloquent\Model;

class CustomerQueue extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customers_queue';

    public function execute()
    {
        try {
            if ($this->processing) {
                return;
            }

            // Set as processing
            $this->processing = true;
            $this->save();

            $customer_exists = Customer::where('username', $this->username)->orderBy('id', 'DESC')->first();

            if ($customer_exists) {
                throw new Exception('Customer #'.$customer_exists->id.' already exists under '.$this->username.'.');
            }

            $newCustomer = Customer::openAccount([
                'username' => $this->username,
                'scheme_number' => $this->scheme_number,
                'email_address' => $this->email_address,
                'mobile_number' => $this->mobile_number,
                'nominated_telephone' => $this->nominated_telephone,
                'commencement_date' => $this->commencement_date,
                'selectedUnit' => $this->meter_number,
                'role' => 'normal',
                'balance' => $this->balance,
                'starting_balance' => $this->starting_balance,
                'first_name' => $this->first_name,
                'surname' => $this->surname,
                'arrears' => $this->arrears,
                'arrears_daily_repayment' => $this->arrears_daily_repayment,
            ]);

            if ($newCustomer) {
                $this->log('Customer ID: '.$newCustomer->id.'');
                $this->log('Username: '.$newCustomer->username.'');
                $this->log('Commencement date: '.$newCustomer->commencement_date.'');
                $this->log("\n\n");
                $this->customer_id = $newCustomer->id;
                $this->failed = 0;
                $this->failed_id = 0;
                $this->failed_msg = '';
                $this->save();
                $this->log('open_account', 'Successful openAccount() for '.$this->username.'. Customer #'.$newCustomer->id.' created');

                return [
                    'success' => 'Successfully created new customer #'.$newCustomer->id,
                ];
            } else {
                return [
                    'success' => 'Successfully executed Customer::openAccount(). Failed to retrieve customer object.',
                ];
            }
        } catch (Exception $e) {
            $this->log('open_account', 'Fatal error in openAccount() for '.$this->username.': '.$e->getMessage().' ('.$e->getLine().')');
            $this->failed = 1;
            $this->failed_id = 0;
            $this->failed_msg = $e->getMessage()."\n\n (".$e->getLine().')';
            $this->save();

            return [
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ];
        } finally {
            $this->processing = false;
            $this->completed = true;
            $this->completed_at = date('Y-m-d H:i:s');
            $this->save();
        }
    }

    public function cancel()
    {
        try {
        } catch (Exception $e) {
            $this->log('stop_open_account', 'Fatal error in cancel() for '.$this->username.': '.$e->getMessage().' ('.$e->getLine().')');
            $this->failed = 1;
            $this->failed_id = 0;
            $this->failed_msg = $e->getMessage()."\n\n (".$e->getLine().')';
            $this->save();

            return [
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ];
        } finally {
            $this->processing = false;
            $this->completed = true;
            $this->completed_at = date('Y-m-d H:i:s');
            $this->save();
        }
    }

    public function redo()
    {
        try {
            $this->processing = false;
            $this->completed = false;
            $this->completed_at = null;
            $this->failed_msg = '';
            $this->failed_id = 0;
            $this->failed = 0;
            $this->save();
        } catch (Exception $e) {
            $this->log('undo_open_account', 'Fatal error in undo() for '.$this->username.': '.$e->getMessage().' ('.$e->getLine().')');
            $this->failed = 1;
            $this->failed_id = 0;
            $this->failed_msg = $e->getMessage()."\n\n (".$e->getLine().')';
            $this->save();

            return [
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ];
        } finally {
        }
    }

    public function undo()
    {
        try {
            if ($this->reverse()) {
                throw new Exception("Failed undo. reverse() returned false. Please verify '".$this->username."' was actually closed.");
            }
        } catch (Exception $e) {
            $this->log('undo_open_account', 'Fatal error in undo() for '.$this->username.': '.$e->getMessage().' ('.$e->getLine().')');
            $this->failed = 1;
            $this->failed_id = 0;
            $this->failed_msg = $e->getMessage()."\n\n (".$e->getLine().')';
            $this->save();

            return [
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ];
        } finally {
            $this->processing = false;
            $this->completed = true;
            $this->completed_at = date('Y-m-d H:i:s');
            $this->save();
        }
    }

    public function log($type, $log)
    {
        return CustomerQueueLog::log($type, $log, $this->id);
    }

    public function reverse()
    {
        $customer_exists = Customer::whereRaw("username = '".$this->username."' AND commencement_date = '".$this->commencement_date."' AND starting_balance = '".$this->starting_balance."'")
        ->first();

        if ($customer_exists) {
            return Customer::closeAccount($customer_exists->id);
        }

        return false;
    }

    public function setFirstnameAttribute($value)
    {
        if (strlen($value) < 45) {
            $this->attributes['first_name'] = Crypt::encrypt($value);
        } else {
            $this->attributes['first_name'] = $value;
        }
    }

    public function setSurnameAttribute($value)
    {
        if (strlen($value) < 45) {
            $this->attributes['surname'] = Crypt::encrypt($value);
        } else {
            $this->attributes['surname'] = $value;
        }
    }

    public function setEmailAddressAttribute($value)
    {
        if (strlen($value) < 45) {
            $this->attributes['email_address'] = Crypt::encrypt($value);
        } else {
            $this->attributes['email_address'] = $value;
        }
    }

    public function setMobileNumberAttribute($value)
    {
        if (strlen($value) < 45) {
            $this->attributes['mobile_number'] = Crypt::encrypt($value);
        } else {
            $this->attributes['mobile_number'] = $value;
        }
    }

    public function getFirstNameAttribute($val)
    {
        try {
            if (strlen($val) > 45) {
                return Crypt::decrypt($val);
            } else {
                return $val;
            }
        } catch (Exception $e) {
            return $val;
        }
    }

    public function getSurnameAttribute($val)
    {
        try {
            if (strlen($val) > 45) {
                return Crypt::decrypt($val);
            } else {
                return $val;
            }
        } catch (Exception $e) {
            return $val;
        }
    }

    public function getEmailAddressAttribute($val)
    {
        try {
            if (strlen($val) > 45 && strpos($val, '@') === false) {
                return strtolower(trim(Crypt::decrypt($val)));
            } else {
                return strtolower(trim($val));
            }
        } catch (Exception $e) {
            return $val;
        }
    }

    public function getMobileNumberAttribute($val)
    {
        try {
            if (strlen($val) > 45) {
                return Crypt::decrypt($val);
            } else {
                return $val;
            }
        } catch (Exception $e) {
            return $val;
        }
    }
}
