<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;



class Customer extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customers';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $dates = ['deleted_at'];

    public static $rules = [
        'balance'					=> 'numeric',
        'first_name' 				=> 'required',
        'surname' 					=> 'required',
        'arrears'					=> 'numeric|min:1',
        'arrears_daily_repayment'	=> 'numeric|min:1',
        'email_address'				=> 'required|email',
        'mobile_number'				=> 'required|valid_mobile_phone',
        'nominated_telephone'		=> 'different:mobile_number|valid_mobile_phone',
        'commencement_date'			=> 'required|date|after_date',
    ];

    public static $customAttributeNames = [];

    public static $customErrorMessages = [
        'balance.numeric'							=> 'The starting balance field should contain a numeric value.',
        'first_name.required'						=> 'Please enter a first name.',
        'surname.required'							=> 'Please enter a last name.',
        'arrears.numeric'							=> 'The arrears field should contain a numeric value.',
        'arrears.min'								=> 'The arrears field value should be >= 0.',
        'arrears_daily_repayment.numeric'			=> 'The arrears daily repayment field should contain a numeric value.',
        'arrears_daily_repayment.min'				=> 'The arrears daily repayment field value should be >= 0.',
        'email_address.required'					=> 'Please enter an email address.',
        'email_address.email'						=> 'Please enter a valid email address.',
        'email_address.unique'						=> 'The email address you entered already exists in our DB.',
        'mobile_number.required'					=> 'Please enter a mobile number.',
        'mobile_number.valid_mobile_phone'			=> 'Please make sure the mobile number you entered starts with +353 or +44 and contains numbers only (example +353876441236).',
        'mobile_number.unique_mobile_phone'			=> 'The mobile number you entered already exists in our DB.',
        'nominated_telephone.valid_mobile_phone'	=> 'Please make sure the nominated mobile number you entered starts with +353 or +44 and contains numbers only (example +35387 644 1236).',
        'nominated_telephone.unique_mobile_phone'	=> 'The nominated mobile number you entered already exists in our DB.',
        'commencement_date.required'				=> 'Please enter a commencement date.',
        'commencement_date.date'					=> 'Please enter a valid commencement date.',
        'commencement_date.after_date'				=> 'The commencement date must be in the future.',
    ];

    public function getPaymentMethodsAttribute()
    {
        return CustomerPaymentMethod::where('customer_id', $this->id)->get();
    }

    public function getStripeCustomerAttribute()
    {
        return StripeCustomer::where('customer_id', $this->id)->orderBy('id', 'DESC')->first();
    }

    public function stopAutotopup($reason = '')
    {
        try {
            $subscription = $this->subscription;

            if (! $subscription) {
                return false;
            }

            $subscription->cancel($reason);

            return true;
        } catch (Exception $e) {
        }

        return false;
    }

    public function refundPayment($ref_number, $refund_reason, $partial_refund = false, $refund_amount = 0)
    {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            Stripe::start();

            $payment_exists = PaymentStorage::where('ref_number', $ref_number)
            ->where('customer_id', $this->id)
            ->first();

            if (! $payment_exists) {
                throw new Exception('Customer #'.$this->id.' does not have payment with ref '.$ref_number);
            }

            if ($refund_amount > $payment_exists->amount) {
                throw new Exception('The amount you are refunding cannot be greater than the payment itself!');
            }

            $payment_exists->archive($refund_reason, true, $refund_amount);

            if ($payment_exists->amount == $refund_amount) {
                $payment_exists->delete();
            } else {
                $payment_exists->amount = $refund_amount;
                $payment_exists->balance_after -= $refund_amount;
                $payment_exists->save();
            }

            $this->balance -= $refund_amount;
            $this->save();

            $re = \Stripe\Refund::create([
              'charge' => $payment_exists->ref_number,
              'amount' => $refund_amount * 100,
              'reason' => 'requested_by_customer',
            ]);

            return true;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception($e->getMessage());
        }

        return false;
    }

    public function startAutotopup($amount = 50, $trial = 0, $paymentSource = null)
    {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            Stripe::start();

            if ($paymentSource == null) {
                $paymentSource = StripePaymentSource::where('customer_id', $this->id)->orderBy('id', 'DESC')->first();
            }

            $subscription = null;
            $stripeCustomer = $this->stripeCustomer;
            $stripeSubscription = StripeCustomerSubscription::where('customer_id', $this->id)->first();
            if (! $stripeSubscription) {
                $stripeSubscription = new StripeCustomerSubscription();
                $stripeSubscription->stripe_customer_token = $stripeCustomer->token;
                $stripeSubscription->stripe_customer_id = $stripeCustomer->id;
                $stripeSubscription->customer_id = $this->id;
                $stripeSubscription->token = '';
                $stripeSubscription->payment_method_id = $paymentSource->id;
                $stripeSubscription->active = 0;
                $stripeSubscription->start_at = '';
                $stripeSubscription->end_at = '';
                $stripeSubscription->topup_amount = $amount;
                $stripeSubscription->force_end_at = null;
                $stripeSubscription->save();
            } else {
                $stripeSubscription->payment_method_id = $paymentSource->id;
                $stripeSubscription->topup_amount = $amount;
                $stripeSubscription->force_end_at = null;
                $stripeSubscription->save();
            }

            \Stripe\Customer::update($stripeCustomer->token, [
            'invoice_settings' => [
                'default_payment_method' => $paymentSource->source_type_token,
            ],
        ]);

            $now = date('Y-m-d H:i:s');

            $subscription = null;

            if ($trial > 0) {
                $subscription = \Stripe\Subscription::create([
              'customer' => $stripeCustomer->token,
              'items' => [
                [
                  'plan' => Stripe::getAutotopupPlan(),
                ],
              ],
              'trial_period_days' => $trial,
              //"expand" => ['latest_invoice.payment_intent']
            ]);
            } else {
                $subscription = \Stripe\Subscription::create([
              'customer' => $stripeCustomer->token,
              'items' => [
                [
                  'plan' => Stripe::getAutotopupPlan(),
                ],
              ],
              'expand' => ['latest_invoice.payment_intent'],
            ]);
            }

            $stripeSubscription = StripeCustomerSubscription::where('customer_id', $this->id)
        ->orderBy('id', 'DESC')->first();

            $timer = new Timer();
            while (! $stripeSubscription) {
                if ($timer->elapsed() >= 300) {
                    $timer->stop();
                    //throw new Exception('Connection timed out waiting for new subscription. Please try again in a moment.');
                    break;
                }
                $stripeSubscription = StripeCustomerSubscription::where('customer_id', $this->id)
            ->orderBy('id', 'DESC')->first();
            }

            $this->pending_subscription = 0;
            $this->save();

            return true;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            echo $e->getMessage().' ('.$e->getLine().')';
        } catch (Exception $e) {
            echo $e->getMessage().' ('.$e->getLine().')';
        }

        return false;
    }

    public function syncPaymentMethods()
    {
        try {
            Stripe::start();

            $customer_obj = $this->stripeCustomer;

            if (! $customer_obj) {
                return true;
            }

            $payment_methods = \Stripe\PaymentMethod::all([
                'customer' => $customer_obj->token,
                'type' => 'card',
            ]);

            $created = 0;
            $updated = 0;
            $deleted = 0;
            $synced = 0;

            $potential_methods = StripePaymentSource::where('customer_id', $this->id)->lists('source_type_token');
            $valid_methods = [];

            foreach ($payment_methods as $k => $p) {
                $customer_id = $this->id;
                $source_type_token = $p->id;
                $stripe_type_fingerprint = ($p->card && $p->card->fingerprint) ? $p->card->fingerprint : '';
                $stripe_customer_token = $p->customer;
                $stripe_customer_id = $customer_obj->id;
                $object_type = $p->object;
                $type = $p->type;
                $type_br = ($p->card && $p->card->brand) ? $p->card->brand : '';
                $last_4 = ($p->card && $p->card->last4) ? $p->card->last4 : '';
                $is_primary = 0;
                $email = ($p->billing_details && $p->billing_details->email) ? $p->billing_details->email : '';
                $last_used_at = null;
                $last_used_ip = null;

                $pm = StripePaymentSource::where('source_type_token', $source_type_token)
                ->first();

                if (! $pm) {
                    $pm = new StripePaymentSource();
                    $created++;
                } else {
                    $pm->is_primary = $is_primary;
                    $pm->last_used_at = $last_used_at;
                    $pm->last_used_ip = $last_used_ip;
                    $updated++;
                }

                $pm->customer_id = $customer_id;
                $pm->source_type_token = $source_type_token;
                $pm->stripe_type_fingerprint = $stripe_type_fingerprint;
                $pm->stripe_customer_token = $stripe_customer_token;
                $pm->stripe_customer_id = $stripe_customer_id;
                $pm->object_type = $object_type;
                $pm->type = $type;
                $pm->type_br = $type_br;
                $pm->last_4 = $last_4;
                $pm->email = $email;
                $pm->save();

                array_push($valid_methods, $pm->source_type_token);

                $synced++;
            }

            // echo '<pre>';
            //print_r($potential_methods);
            // echo '</pre>';
            // echo '<br/><hr/><br/>';
            // echo '<pre>';
            // print_r($valid_methods);
            // echo '</pre>';

            foreach ($potential_methods as $k => $v) {
                if (! in_array($v, $valid_methods)) {
                    $pm = StripePaymentSource::where('source_type_token', $v)->delete();
                    $deleted++;
                } else {
                }
            }

            return [
                'deleted' => $deleted,
                'created' => $created,
                'updated' => $updated,
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            //echo $e->getMessage() . " (" . $e->getLine() . ")";
        } catch (Exception $e) {
            //echo $e->getMessage() . " (" . $e->getLine() . ")";
        }
    }

    public function getPaymentNotificationsAttribute()
    {
        if ($this->simulator > 0) {
            $username = str_replace('_test', '', $this->username);
            $original = self::where('username', $username)->first();
            if ($original) {
                return CustomerStripePayments::getPendingNotifications($original->id);
            }
        }

        return CustomerStripePayments::getPendingNotifications($this->id);
    }

    public function setSurnameAttribute($value)
    {
        if (strlen($value) < 100) {
            $this->attributes['surname'] = Crypt::encrypt($value);
        } else {
            $this->attributes['surname'] = $value;
        }
    }

    public function setFirstnameAttribute($value)
    {
        if (strlen($value) < 100) {
            $this->attributes['first_name'] = Crypt::encrypt($value);
        } else {
            $this->attributes['first_name'] = $value;
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

    public function getSurnameAttribute($val)
    {
        try {
            if (strlen($val) > 100) {
                return Crypt::decrypt($val);
            } else {
                return $val;
            }
        } catch (Exception $e) {
            return $val;
        }
    }

    public function getFirstNameAttribute($val)
    {
        try {
            if (strlen($val) > 100) {
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

    public function getRcsAttribute()
    {
        if ($this->permanentMeter) {
            return RemoteControlStatus::where('permanent_meter_id', $this->permanentMeter->ID)->first();
        } else {
            return null;
        }
    }

    public function getTariffAttribute()
    {
        if ($this->scheme) {
            return Tariff::where('scheme_number', $this->scheme->id)->first();
        }
    }

    public function getTodaysDhuAttribute()
    {
        return DistrictHeatingUsage::where('customer_id', $this->id)
        ->where('date', date('Y-m-d'))->orderBy('id', 'ASC')->first();
    }

    public function getDistrictMeterAttribute()
    {
        return DistrictHeatingMeter::where('meter_ID', $this->meter_ID)->first();
    }

    public function getPermanentMeterAttribute()
    {
        if (! $this->districtMeter) {
            return null;
        }

        return $this->districtMeter->permanentMeter;
    }

    public function scheme()
    {
        return $this->belongsTo('App\Models\Scheme', 'scheme_number', 'scheme_number');
    }

    public function getSchemeAttribute()
    {
        return Scheme::where('scheme_number', $this->scheme_number)->first();
    }

    public function useIOU()
    {
        $scheme = $this->scheme;

        if (! $scheme) {
            return false;
        }

        $charge_cmp = $scheme->IOU_amount;
        $charge_red = $scheme->IOU_charge;

        if ($this->balance > (0 - $charge_cmp)) {
            //$this->balance = $this->balance - $charge_red;
            $this->IOU_used = 1;
            $this->IOU_available = 0;
            $this->save();
        } else {
            return false;
        }

        $ioucharge = $scheme->IOU_charge;
        $date = date('Y-m-d H:i:s');
        $iou_data = ['customer_id' => $this->id,
                     'scheme_number' => $scheme->scheme_number,
                     'time_date' => $date,
                     'charge' => $ioucharge,
                     'paid' => '0', ];
        IOUStorage::create($iou_data);

        $this->clearShutOff(true);

        return true;
    }

    public function clearShutOff($reset = false)
    {
        try {
            $dhm = $this->districtMeter;
            if ($dhm) {
                $dhm->scheduled_to_shut_off = 0;
                $dhm->shut_off_device_status = 0;
                $dhm->save();
            }

            $pmd = $this->permanentMeter;
            if ($pmd) {
                $pmd->shut_off = 0;
                $pmd->save();
            }

            $this->shut_off_command_sent = 0;
            $this->credit_warning_sent = 0;
            $this->shut_off = 0;
            $this->save();

            $customer_id = $this->id;

            if (! $reset) {
                $this->sendOpenCommand(0, 1, 0);
                $log = new PaymentStorageTestLog();
                $log->customer_id = $this->id;
                $log->message = 'Customer '.$this->id."'s meter was reopened";
                $log->save();

                $this->IOU_available = 0;
                $this->IOU_used = 0;
                $this->IOU_extra_available = 0;
                $this->IOU_extra_used = 0;
                $this->admin_IOU_in_use = 0;
                $this->save();
            } else {
                $this->sendOpenCommand(0, 0, 0);
            }
        } catch (Exception $e) {
            PaymentStorageError::log($this->id, $e->getMessage());
        }
    }

    public function sendOpenCommand($away_mode_initiated = 0, $topup_initiated = 0, $shut_off_engine_initiated = 0)
    {
        if ($this->permanentMeter && $this->districtMeter) {
            /*
            $scheme = $this->scheme;
            $dl = $this->scheme->dataLogger;
            $sim = $dl->SIM;


            $rcq = new RTUCommandQueWebsite();
            $rcq->time_date = date('Y-m-d H:i:s');
            $rcq->customer_ID = $this->id;
            $rcq->permanent_meter_id = $this->permanentMeter->ID;
            $rcq->scheme_number = $this->scheme_number;
            $rcq->data_logger_id = $datalogger->id;
            $rcq->turn_service_on = 1;
            $rcq->turn_service_off = 0;
            $rcq->restart_service = 0;
            $rcq->attempts_to_try = 10;
            $rcq->processed = 0;
            $rcq->complete = 0;
            $rcq->failed = 0;
            $rcq->response = '';
            $rcq->port = 2221;
            $rcq->ICCID = $sim->ICCID;
            $rcq->scu_type = 'm';
            $rcq->m_bus_relay_id = $pmd->scu_number;
            $rcq->away_mode_initiated = $away_mode_initiated;
            $rcq->topup_initiated = $topup_initiated;
            $rcq->shut_off_engine_initiated = $shut_off_engine_initiated;
            $rcq->automated_by_user_ID = 0;
            $rcq->save();
            */

            $rcq = new RTUCommandQue();
            $rcq->customer_ID = $this->id;
            $rcq->meter_id = $this->districtMeter->meter_ID;
            $rcq->permanent_meter_id = $this->permanentMeter->ID;
            $rcq->turn_service_on = 1;
            $rcq->shut_off_device_contact_number = '1.1.1.1';
            $rcq->port = 1;
            $rcq->scheme_number = $this->scheme_number;
            $rcq->away_mode_initiated = $away_mode_initiated;
            $rcq->shut_off_engine_initiated = $shut_off_engine_initiated;
            $rcq->topup_initiated = $topup_initiated;
            $rcq->save();
        }
    }

    public function sendCloseCommand($away_mode_initiated = 0, $topup_initiated = 0, $shut_off_engine_initiated = 0)
    {
        if ($this->permanentMeter && $this->districtMeter) {
            $rcq = new RTUCommandQue();
            $rcq->customer_ID = $this->id;
            $rcq->meter_id = $this->districtMeter->meter_ID;
            $rcq->permanent_meter_id = $this->permanentMeter->ID;
            $rcq->turn_service_off = 1;
            $rcq->shut_off_device_contact_number = '1.1.1.1';
            $rcq->port = 1;
            $rcq->scheme_number = $this->scheme_number;
            $rcq->away_mode_initiated = $away_mode_initiated;
            $rcq->shut_off_engine_initiated = $shut_off_engine_initiated;
            $rcq->topup_initiated = $topup_initiated;
            $rcq->save();
        }
    }

    public function addPayment($ref_number, $amount, $date = null)
    {
        try {
            $exists = PaymentStorage::where('ref_number', $ref_number)->first();

            if ($exists) {
                return false;
            }

            $time_date = date('Y-m-d H:i:s');
            $settlement_date = date('Y-m-d');
            if ($date != null) {
                $time_date = $date;
                $settlement_date = (new DateTime($date))->format('Y-m-d');
            }

            $paymentData = [];
            $paymentData['ref_number'] = $ref_number;
            $paymentData['customer_id'] = $this->id;
            $paymentData['scheme_number'] = $this->scheme_number;
            $paymentData['barcode'] = $this->barcode; //rc customers don't have barcode
            $paymentData['time_date'] = $date;
            $paymentData['currency_code'] = $this->scheme->currency_code;
            $paymentData['amount'] = $amount;
            $paymentData['transaction_fee'] = 0.0;
            $paymentData['acceptor_name_location_'] = 'paypal';
            $paymentData['payment_received'] = 1;
            $paymentData['restored_payment'] = 1;
            $paymentData['settlement_date'] = $settlement_date;
            $paymentData['merchant_type'] = 0;
            $paymentData['POS_entry_mode'] = 0;

            if (! PaymentStorage::create($paymentData)) {
                throw new Exception('The payment cannot be added');
            }
            $ps = PaymentStorage::where('ref_number', $ref_number)->first();
            $this->topup($ps);

            return $ps;
        } catch (Exception $e) {
            return false;
        }
    }

    public function topup($paymentStorage)
    {
        try {
            $balanceBefore = $this->balance;
            $balanceAfter = $this->balance + $paymentStorage->amount;

            $this->balance += $paymentStorage->amount;
            $this->last_top_up = $paymentStorage->time_date;
            $this->shut_off = 0;
            $this->save();

            $ref = $paymentStorage->ref_number;
            DB::table('payments_storage')
            ->where('ref_number', $ref)
            ->where('customer_id', $this->id)
            ->update([
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ]);

            // Manage turning the customers meter on/off
            //$this->sendOpenCommand(0, 1);
            $log = new PaymentStorageTestLog();
            $log->customer_id = $this->id;
            $log->message = 'Customer '.$this->id.' topped up by '.$paymentStorage->amount.'eur. New Bal: '.($this->balance);
            $log->save();

            $this->clearShutOff();
        } catch (Exception $e) {
            PaymentStorageError::log($this->id, $e->getMessage());
        }
    }

    public function countPayments($type = null, $from_date = null)
    {
        $total_payments = null;

        if ($type == null || $type == 'paypal') {
            if ($from_date == null) {
                // default paypal with no from date
                $total_payments = PaymentStorage::where('customer_id', $this->id)->where('ref_number', 'like', 'PAY-%')->count();
            } else {
                $total_payments = PaymentStorage::where('customer_id', $this->id)->where('time_date', '>=', $from_date)->where('ref_number', 'like', 'PAY-%')->count();
            }
        } else {
            if ($from_date == null) {
                $total_payments = PaymentStorage::where('customer_id', $this->id)->where('ref_number', 'like', 'PPR%')->count();
            } else {
                $total_payments = PaymentStorage::where('customer_id', $this->id)->where('time_date', '>=', $from_date)->where('ref_number', 'like', 'PPR%')->count();
            }
        }

        return $total_payments;
    }

    public function sumPayments($type = null, $from_date = null)
    {
        $total_payments = null;

        if ($type == null || $type == 'paypal') {
            if ($from_date == null) {
                // default paypal with no from date
                $total_payments = PaymentStorage::where('customer_id', $this->id)->where('ref_number', 'like', 'PAY-%')->sum('amount');
            } else {
                $total_payments = PaymentStorage::where('customer_id', $this->id)->where('time_date', '>=', $from_date)->where('ref_number', 'like', 'PAY-%')->sum('amount');
            }
        } else {
            if ($from_date == null) {
                $total_payments = PaymentStorage::where('customer_id', $this->id)->where('ref_number', 'like', 'PPR%')->sum('amount');
            } else {
                $total_payments = PaymentStorage::where('customer_id', $this->id)->where('time_date', '>=', $from_date)->where('ref_number', 'like', 'PPR%')->sum('amount');
            }
        }

        return $total_payments;
    }

    public function allTopups($on_date = null, $sum_total = false)
    {
        $topups = null;

        if ($on_date == null) {
            $paypal = $this->paymentsStorage()->get();
            $paypoint = $this->temporaryPayments()->get();
            $topups = array_merge($paypal->toArray(), $paypoint->toArray());
            usort($topups, function ($item1, $item2) {
                return $item2['time_date'] >= $item1['time_date'];
            });
        } else {
            $paypal = $this->paymentsStorage()->where('time_date', 'like', "%$on_date%")->get();
            $paypoint = $this->temporaryPayments()->where('time_date', 'like', "%$on_date%")->get();
            $topups = array_merge($paypal->toArray(), $paypoint->toArray());
            usort($topups, function ($item1, $item2) {
                return $item2['time_date'] >= $item1['time_date'];
            });
        }

        if ($sum_total) {
            $sum = 0;
            foreach ($topups as $topup) {
                $topup = (object) $topup;
                $sum += $topup->amount;
            }

            return $sum;
        }

        return $topups;
    }

    public function getTopupsAttribute()
    {
        return PaymentStorage::where('customer_id', $this->customer_id)->get();
    }

    public function allSMS($on_date = null, $sum_total = false)
    {
        $sms = null;

        if ($on_date == null) {
            $sms = SMSMessage::where('customer_id', $this->id)->get();
        } else {
            $sms = SMSMessage::where('customer_id', $this->id)->where('date_time', 'like', "%$on_date%")->get();
        }

        if ($sum_total) {
            $sum = 0;
            foreach ($sms as $s) {
                $sum += $s->charge;
            }

            return $sum;
        }

        return $sms;
    }

    public function paymentsStorage()
    {
        return $this->hasMany('App\Models\PaymentStorage', 'customer_id', 'id');
    }

    public function temporaryPayments()
    {
        return $this->hasMany('App\Models\TemporaryPayments', 'customer_id', 'id');
    }

    public function adminIssuedCredit()
    {
        return $this->hasMany('App\Models\AdminIssuedCredit', 'customer_id', 'id');
    }

    public function adminDeductedCredit()
    {
        return $this->hasMany('App\Models\AdminDeductedCredit', 'customer_id', 'id');
    }

    public function districtHeatingMeter()
    {
        return $this->hasOne('App\Models\DistrictHeatingMeter', 'meter_ID', 'meter_ID');
    }

    public function EVDistrictHeatingMeter()
    {
        return $this->hasOne('App\Models\DistrictHeatingMeter', 'meter_ID', 'ev_meter_ID');
    }

    public function enhancedUsage($range = null)
    {
        $from = new DateTime($range['from']);
        $to = new DateTime($range['to']);
        $final = (new DateTime($range['to']))->modify('+1 day');
        $usage = [];
        $totals = [
            'usage' => 0,
        ];

        // $pmd = $this->permanentMeter;
        // if(!$pmd)
        // return;

        // while($from->format('Y-m-d') != $final->format('Y-m-d')) {

        // $start_r = PermanentMeterDataReadingsAll::where('permanent_meter_id', $pmd->ID)->whereRaw("time_date LIKE '%" . $from->format('Y-m-d') . "%'")->orderBy('ID', 'ASC')->first();
        // $end_r = PermanentMeterDataReadingsAll::where('permanent_meter_id', $pmd->ID)->whereRaw("time_date LIKE '%" . $from->format('Y-m-d') . "%'")->orderBy('ID', 'DESC')->first();

        // if($start_r && $end_r) {
        // $totals['usage'] += ($end_r->reading1 - $start_r->reading1);

        // $usage[] = (object)[
        // 'totals' => $totals,
        // 'date' => $from->format('Y-m-d'),
        // 'start_day_reading' => $start_r->reading1,
        // 'end_day_reading' => $end_r->reading1,
        // 'total_usage' => ($end_r->reading1 - $start_r->reading1),
        // ];
        // } else {

        // $usage[] = (object)[

        // 'date' => $from->format('Y-m-d'),
        // 'start_day_reading' => 0,
        // 'end_day_reading' => 0,
        // 'total_usage' => 0,

        // ];

        // }
        // $from = $from->modify('+1 day');

        // }

        return $usage;
    }

    public function districtHeatingUsage($range = null)
    {
        if ($range != null) {
            return DistrictHeatingUsage::where('customer_id', $this->id)->whereRaw("date >= '".$range['from']."' AND date <= '".$range['to']."'")
        ->orderby('date', 'asc')->groupBy('date')->get();
        } else {
            return DistrictHeatingUsage::where('customer_id', $this->id)
        ->orderby('date', 'asc')->groupBy('date')->get();
        }
    }

    public function sendStatement($emails, $extra_emails = null, $range = null)
    {
        $from = date('Y-m-d', strtotime('-1 month'));
        $to = date('Y-m-d');
        if ($range != null) {
            $from = $range['from'];
            $to = $range['to'];
        }

        $usage = $this->districtHeatingUsage(['from' => $from, 'to' => $to]);

        $pmd = PermanentMeterData::where('username', $this->username)->first();
        $scheme = Scheme::find($this->scheme_number);
        $currency = $scheme->currency_sign;
        $address_1 = 'Apt '.$this->house_number_name.' '.$pmd->street1;
        $address_2 = ucfirst($pmd->street2);
        if (empty($address_2)) {
            $address_2 = '-';
        }
        $address_3 = ucfirst($scheme->street2);
        if (strlen($address_3) <= 3) {
            $address_3 = '-';
        }
        $address_4 = 'Co. '.$scheme->county;
        $topups = DB::table('payments_storage')->where('customer_id', $this->id)
            ->whereRaw("settlement_date >= '$from' AND settlement_date <= '$to'");
        $smss = DB::table('sms_messages')->where('customer_id', $this->id)
            ->whereRaw("date_time >= '$from' AND date_time <= '$to'");
        $earlier = new DateTime($from);
        $later = new DateTime($to);
        $no_of_days = $later->diff($earlier)->format('%a');

        $customer = self::find($this->id);

        $pdf = new Dompdf\Dompdf();
        $pdf->loadHtml(view('pdfs.statement', [
            'customer' => $customer,
            'address_1' => $address_1,
            'address_2' => $address_2,
            'address_3' => $address_3,
            'address_4' => $address_4,
            'from' => $from,
            'to' => $to,
            'no_of_days' => $no_of_days,
            'topups' => $topups,
            'smss' => $smss,
        ]));
        $pdf->set_option('isRemoteEnabled', true);
        $pdf->set_option('debugKeepTemp', true);
        $pdf->set_option('isHtml5ParserEnabled', true);
        $pdf->setPaper('A4', 'portrait');
        // (Optional) Setup the paper size and orientation
        //$dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $pdf->render();

        // $pdf = PDF::loadView('pdfs.statement', [
        // 'customer' => $customer,
        // 'address_1' => $address_1,
        // 'address_2' => $address_2,
        // 'address_3' => $address_3,
        // 'address_4' => $address_4,
        // 'from' => $from,
        // 'to' => $to,
        // 'no_of_days' => $no_of_days,
        // 'topups' => $topups,
        // 'smss' => $smss,
        // ]);

        // generate pdf using data
        // $dompdf->loadHtml(
        // view('pdfs.statement', [)
        // );
        // $dompdf->setPaper('A4', 'portrait');
        // $dompdf->render();

        $this->sms("Your requested SnugZone account statement has been sent to '".implode(', ', $emails)."'. \n\nRegards\nSnugZone");

        $from_f = (new DateTime($from))->format('d M Y');
        $to_f = (new DateTime($to))->format('d M Y');
        Mail::send('emails.statement', [], function ($message) use ($customer, $pdf, $emails, $extra_emails, $from_f, $to_f) {
            $message->to($emails);
            $message->from('info@prepago.ie');
            if (count($extra_emails) > 0) {
                $message->cc($extra_emails);
            }
            $message->subject("SnugZone Account Statement: $from_f - $to_f");
            $message->attachData($pdf->output(), 'statement_'.$from_f.'_'.$to_f.'_.pdf');
        });

        $this->balance -= 0.25;
        $this->save();

        if ($customer->id != 1) {
            $statement = new SnugzoneAppStatement();
            $statement->customer_id = $this->id;
            $statement->from = $from;
            $statement->to = $to;
            $statement->save();
        }
    }

    public function validDistrictHeatingUsage()
    {
        return $this->districtHeatingUsage()->where('start_day_reading', '>', 0);
    }

    public function latestDistrictHeatingReading()
    {
        return $this->hasOne('App\Models\DistrictHeatingUsage')->orderBy('date', 'DESC');
    }

    public function districtHeatingTotalUsage()
    {
        return $this->districtHeatingUsage()
                ->selectRaw('sum(total_usage) as total_usage, customer_id, date')
                ->where('end_day_reading', '!=', 0)
                ->groupBy('customer_id');
    }

    public function permanentMeter()
    {
        if ($this->districtHeatingMeter) {
            return $this->districtHeatingMeter->permanentMeterData;
        }

        return null;
    }

    public function charge($fee)
    {
        $this->update([
            'balance' => $this->balance - $fee,
        ]);
    }

    public function associateDistrictHeatingMeter($districtHeatingMeterID)
    {
        $this->update([
            'ev_meter_ID' => $districtHeatingMeterID,
        ]);
    }

    public function disassociateDistrictHeatingMeter()
    {
        $this->update([
            'ev_meter_ID' => 0,
        ]);
    }

    public function resetShutOff()
    {
        if ($this->districtMeter) {
            $meter = $this->districtMeter;
            $meter->scheduled_to_shut_off = 0;
            $meter->shut_off_device_status = 0;
            $meter->save();
        }

        if ($this->permanentMeter) {
            $pmd = $this->permanentMeter;
            $pmd->shut_off = 0;
            $pmd->save();
        }

        $this->shut_off_command_sent = 0;
        $this->shut_off = 0;
        $this->save();
    }

    public function totalUsage($from = null, $to = null)
    {
        $from = $from ?: Carbon::now()->startOfMonth();
        $to = $to ?: Carbon::now()->endOfDay();

        $dhu = DistrictHeatingUsage
                                    ::select('cost_of_day', 'start_day_reading', 'end_day_reading')
                                    ->where('customer_id', '=', $this->customer_id)
                                    ->where('date', '>=', $from)
                                    ->where('date', '<=', $to)
                                    ->where('start_day_reading', '>', 0)
                                    ->where('end_day_reading', '>', 0)
                                    ->orderby('date', 'asc')
                                    ->remember(1)
                                    ->get();

        if (! $dhu->count()) {
            return 0;
        }

        return  $dhu->last()->end_day_reading - $dhu->first()->start_day_reading;
    }

    public function totalUsageNoRange()
    {
        $dhu = DistrictHeatingUsage
                                    ::select('cost_of_day', 'start_day_reading', 'end_day_reading')
                                    ->where('customer_id', '=', $this->customer_id)
                                    ->where('start_day_reading', '>', 0)
                                    ->where('end_day_reading', '>', 0)
                                    ->orderby('date', 'asc')
                                    ->remember(1)
                                    ->get();

        if (! $dhu->count()) {
            return 0;
        }

        return  $dhu->last()->end_day_reading - $dhu->first()->start_day_reading;
    }

    public function avgDailyUsage($from = null, $to = null)
    {
        $from = $from ?: Carbon::now()->startOfMonth();
        $to = $to ?: Carbon::now()->endOfDay();

        $dhu = DistrictHeatingUsage
                                    ::select('cost_of_day', 'start_day_reading', 'end_day_reading')
                                    ->where('customer_id', '=', $this->id)
                                    ->where('date', '>=', $from)
                                    ->where('date', '<=', $to)
                                    ->where('start_day_reading', '>', 0)
                                    ->where('end_day_reading', '>', 0)
                                    ->orderby('date', 'asc')
                                    ->remember(1)
                                    ->get();

        if (! $dhu->count()) {
            return 0;
        }

        return ($dhu->last()->end_day_reading - $dhu->first()->start_day_reading) / $dhu->count();
    }

    public function avgDailyCost($from = null, $to = null)
    {
        $from = $from ?: Carbon::now()->startOfMonth();
        $to = $to ?: Carbon::now()->endOfDay();

        $dhu = DistrictHeatingUsage
                                    ::select('cost_of_day', 'start_day_reading', 'end_day_reading')
                                    ->where('customer_id', '=', $this->id)
                                    ->where('date', '>=', $from)
                                    ->where('date', '<=', $to)
                                    ->where('start_day_reading', '>', 0)
                                    ->where('end_day_reading', '>', 0)
                                    ->orderby('date', 'asc')
                                    ->remember(1)
                                    ->get();

        if (! $dhu->count()) {
            return 0;
        }

        $total_cost = 0;
        $dhu->each(function ($dhuItem) use (&$total_cost) {
            $total_cost = $total_cost + $dhuItem->cost_of_day;
        });

        return $total_cost / $dhu->count();
    }

    public function scopeActiveScheme($query)
    {
        return $query->join('schemes', 'customers.scheme_number', '=', 'schemes.id')
        ->whereRaw('(customers.deleted_at IS NULL AND schemes.archived = 0)');
    }

    public function scopeIOU($query)
    {
        return $query->join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
        ->join('schemes', 'customers.scheme_number', '=', 'schemes.id')
        ->whereRaw('(customers.IOU_used = 1 AND customers.deleted_at IS NULL AND schemes.archived = 0)');
    }

    public function scopeShutOff($query)
    {
        return $query->join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
        ->join('permanent_meter_data', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data.ID')
        ->whereRaw('( (district_heating_meters.shut_off_device_status = 1) AND (permanent_meter_data.is_bill_paid_customer = 0) )');
    }

    public function scopePendingShutOff($query)
    {
        return $query->join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
        ->join('permanent_meter_data', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data.ID')
        ->whereRaw('(permanent_meter_data.is_bill_paid_customer = 0 AND customers.simulator = 0 AND customers.deleted_at IS NULL AND (customers.balance <= 5.00 AND district_heating_meters.shut_off_device_status = 0))');
    }

    public function scopeBillPaid($query)
    {
        return $query->join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
        ->join('permanent_meter_data', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data.ID')
        ->whereRaw('(permanent_meter_data.is_bill_paid_customer = 1 AND customers.deleted_at IS NULL)');
    }

    public function scopeNormal($query)
    {
        return $query->join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
        ->join('permanent_meter_data', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data.ID')
        ->whereRaw('( (district_heating_meters.shut_off_device_status = 0 AND customers.balance > 5) OR (permanent_meter_data.is_bill_paid_customer = 1) )');
    }

    public function scopeActive2($query)
    {
        return $query->join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
        ->join('permanent_meter_data', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data.ID')
        ->join('schemes', 'customers.scheme_number', '=', 'schemes.id')
        ->whereRaw('(customers.deleted_at IS NULL AND schemes.archived = 0)');
    }

    public function getColourAttribute()
    {
        $meter = $this->districtMeter;
        $pmd = $this->permanentMeter;

        if (! $meter || ! $pmd) {
            return 'green';
        }

        if ($pmd->is_bill_paid_customer == 1) {
            return 'blue';
        }

        if (($meter->shut_off_device_status == 0 && $this->balance > 5.00) || $pmd->is_bill_paid_customer == 1) {
            return 'green';
        }

        if (($meter->shut_off_device_status == 0 && $this->balance <= 5.00)) {
            return 'yellow';
        }

        if (($meter->shut_off_device_status == 1 && $pmd->is_bill_paid_customer == 0)) {
            return 'red';
        }

        return 'green';
    }

    public static function getShutOffCustomers($scheme_id = 0)
    {
        if ($scheme_id == 0 && Auth::user()) {
            $scheme_id = Auth::user()->scheme_number;
        }

        return self::shutOff()
        ->whereRaw('((customers.status = 1 OR customers.simulator > 0) AND ev_owner = 0)')
        ->where('customers.scheme_number', '=', $scheme_id)
        ->orderBy('customers.balance', 'desc')
        ->get();
    }

    public static function getEmptyCustomers($scheme_id = 0)
    {
        if ($scheme_id == 0 && Auth::user()) {
            $scheme_id = Auth::user()->scheme_number;
        }

        $scheme = Scheme::find($scheme_id);
        if ($scheme_id == 24) {
            return PermanentMeterData::where('ID', '<=', -100)->get();
        }
        if (! $scheme) {
            return PermanentMeterData::where('ID', '<=', -100)->get();
        }

        return $scheme->emptyAddresses;
    }

    public static function getPendingCustomers($scheme_id = 0)
    {
        if ($scheme_id == 0 && Auth::user()) {
            $scheme_id = Auth::user()->scheme_number;
        }

        return self::pendingShutOff()
        ->whereRaw('((customers.status = 1 OR customers.simulator > 0) AND ev_owner = 0)')
        ->where('customers.scheme_number', '=', $scheme_id)
        ->orderBy('balance', 'asc')
        ->get();
    }

    public static function getNormalCustomers($scheme_id = 0)
    {
        if ($scheme_id == 0 && Auth::user()) {
            $scheme_id = Auth::user()->scheme_number;
        }

        return self::normal()
        ->whereRaw('((customers.status = 1 OR customers.simulator > 0) AND ev_owner = 0)')
        ->where('customers.scheme_number', '=', $scheme_id)
        ->orderBy('customers.id', 'asc')
        ->get();
    }

    public static function getActiveCustomers($inclu_me = false)
    {
        $customers = self::active2()
        ->whereRaw('((customers.status = 1 OR customers.simulator > 0) AND ev_owner = 0) ')
        ->orderBy('balance', 'asc')
        ->get();

        if ($inclu_me == true) {
            $customers->push(self::find(1));
        }

        return $customers;
    }

    public static function getBillPaidCustomers($scheme_id = 0)
    {
        if ($scheme_id == 0 && Auth::user()) {
            $scheme_id = Auth::user()->scheme_number;
        }

        $pmds = PermanentMeterData::where('is_bill_paid_customer', '1')->where('scheme_number', $scheme_id)->get();
        $customers = self::where('id', -2)->get();
        foreach ($pmds as $p) {
            $customer = $p->customer;
            if ($customer) {
                $customers->push($customer);
            }
        }

        return $customers;
    }

    public function scopeActive($query)
    {
        return $query->whereRaw('(status = 1 OR simulator > 0)')->where('username', '!=', '')->where('email_address', '!=', '')->where('mobile_number', '!=', '');
    }

    public function scopeResetShutOffCandidates($query)
    {
        return $query->active()->join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
        ->whereRaw('((district_heating_meters.scheduled_to_shut_off = 1 OR district_heating_meters.shut_off_device_status = 1) AND customers.balance > 5.00 AND customers.simulator = 0)');
    }

    public function scopeInScheme($query, $scheme_number)
    {
        return $query->where('scheme_number', $scheme_number);
    }

    public function get16Digit()
    {
        $pm = $this->permanentMeter();

        $mbus_tran = MBusAddressTranslation::where('8digit', $pm->scu_number)->first();

        if ($mbus_tran) {
            return $mbus_tran['16digit'];
        }

        return '';
    }

    public function duplicateMeters()
    {
        $duplicates = [];

        try {
            if (! $this->permanentMeter) {
                return;
            }

            if (! $this->districtMeter) {
                return;
            }

            $original = $this->districtMeter;

            $duplicates = DistrictHeatingMeter::where('permanent_meter_ID', $this->permanentMeter->ID)->where('meter_ID', '!=', $original->meter_ID)->get();

            return $duplicates;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getBarcodeLink()
    {
        return '/Barcodes/'.$this->barcode.'.png';
    }

    public function getIOUAttribute()
    {
        return IOUStorage::where('customer_id', $this->id)->orderBy('id', 'DESC')->first();
    }

    public static function inconsistentDHU($date = null)
    {
        if ($date == null) {
            $date = date('Y-m-d');
        }

        $date_prev = new DateTime($date);
        $date_prev = $date_prev->modify('- 1 day');
        $date_prev = $date_prev->format('Y-m-d');

        $customers = self::where('status', 1)->get();

        $inconsistent_usage = 0;
        $inconsistent_usage_customers = [];

        foreach ($customers as $c) {
            ob_start();

            $entry = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', $date)->orderBy('id', 'ASC')->first();
            $prev_entry = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', $date_prev)->orderBy('id', 'ASC')->first();

            if ($entry) {
                if (($entry->end_day_reading - $entry->start_day_reading) != $entry->total_usage) {
                    $c->entry = $entry;
                    $c->prev_entry = $prev_entry;
                    $inconsistent_usage++;
                    $inconsistent_usage_customers[] = $c;
                }
            }

            ob_end_flush();
        }

        return [
            'inconsistent_usage' => $inconsistent_usage,
            'inconsistent_usage_customers' => $inconsistent_usage_customers,
        ];
    }

    public static function duplicateDHU($date = null)
    {
        if ($date == null) {
            $date = date('Y-m-d');
        }

        $customers = self::where('status', 1)->get();

        $duplicate_dhu = 0;
        $duplicate_dhu_customers = [];

        foreach ($customers as $c) {
            ob_start();

            $entries = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', $date)->orderBy('id', 'ASC')->get();

            if (DistrictHeatingUsage::where('customer_id', $c->id)->where('date', $date)->count() > 1) {
                $duplicate_dhu++;

                $c->duplicate_entries = $entries;

                $duplicate_dhu_customers[] = $c;
            }

            ob_end_flush();
        }

        return [
            'duplicate_dhu' => $duplicate_dhu,
            'duplicate_dhu_customers' => $duplicate_dhu_customers,
        ];
    }

    public static function customerCountAtPeriod($date)
    {
        $c = DB::table('customers')->whereRaw("commencement_date <= '$date'")
        ->where('status', 1)->whereRaw('(deleted_at IS NULL)')->count();

        return $c;
    }

    public function generateToken($length)
    {
        $token = '';
        $codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codeAlphabet .= 'abcdefghijklmnopqrstuvwxyz';
        $codeAlphabet .= '0123456789';
        $max = strlen($codeAlphabet); // edited

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max - 1)];
        }

        $this->sso_ticket = $token;
        $this->save();

        return $token;
    }

    public function getUsage($from, $to, $ret_avg = false)
    {
        $dhu = DistrictHeatingUsage::where('customer_id', $this->id)
            ->whereRaw("(date >= '$from' AND date <= 'to')");

        if ($ret_avg) {
            return $dhu;
        }

        return $dhu->get();
    }

    public function getPlatformAttribute()
    {
        $tap = TrackingAppData::where('customer_id', $this->id)->whereRaw('( (LENGTH(platform) > 1) )')
        ->orderBy('last_login', 'DESC')->first();

        return $tap;
    }

    public function getSSO($platform = null, $uuid = null, $ip = null)
    {
        // Method 1. Retrieve SSO using UUID or IP Address

        if ($uuid != null && $ip != null && $platform != null) {
            $session = CustomerSession::whereRaw('(expired = 0 AND (expired_at IS NULL OR expired_at > NOW()))')
            ->where('customer_id', $this->id)
            ->whereRaw("( (uuid != 'unsupported' && uuid = '".$uuid."') OR ip = '".$ip."')")
            ->first();
            if ($session) {
                $session->touch();
                $session->ip = $this->getIP();
                $session->save();

                return $session->token;
            // session doesn't exist, create a new one
            } else {
                $session = new CustomerSession();
                $session->customer_id = $this->id;
                $session->token = $this->generateRandomString();
                $session->platform = $platform;
                $session->uuid = $uuid;
                $session->ip = $ip;
                $session->save();

                return $session->token;
            }
        }

        // Method 2. Old APP versions Retrieve SSO using serialized array list in customers.sso_ticket
        if (! empty($this->sso_ticket)) {

            // Check if the customers IP Address already has an SSO, if so, reuse that one
            $ssos = unserialize($this->sso_ticket);
            if (is_array($ssos)) {
                foreach ($ssos as $k => $v) {
                    if ($v['ip_address'] == $_SERVER['REMOTE_ADDR']) {
                        return $v['sso'];
                    }
                }
            }

            // Generate a new SSO
            $sso = $this->generateRandomString();
            $new_sso_array = ['ip_address' => $this->getIP(), 'time' => date('Y-m-d H:i:s'), 'sso' => $sso];
            if (empty($this->sso_ticket)) {
                $this->sso_ticket = serialize([$new_sso_array]);
            } else {
                $current_sso = unserialize($this->sso_ticket);
                if (is_array($current_sso)) {
                    array_push($current_sso, $new_sso_array);
                    $this->sso_ticket = serialize($current_sso);
                }
            }
            $this->save();
        }
    }

    public function validSSO($sso, $customer_id = 0)
    {

        // Check if $customer_id = $_SESSION['id']
        if (isset($_SESSION['id']) && ! empty($_SESSION['ID']) && $customer_id != 0) {
            if ($customer_id == $_SESSION['id']) {
                return true;
            }
        }

        // If the SSO we're checking is null, let's forget about it, it's not valid
        if (empty($sso)) {
            return false;
        }

        // Method 1. (latest) Check if sso in customers session table
        $session = CustomerSession::where('customer_id', $this->id)
        ->where('token', $sso)
        ->first();
        if ($session) {
            return true;
        }

        // Method 2. Check if $sso is inside of customers.sso_ticket serialized array
        if (! empty($sso)) {
            if (strpos($this->sso_ticket, $sso) !== false) {
                return true;
            }
        }

        return false;
    }

    public function logNewDevice()
    {
        try {
            $platform = Input::get('platform');
            $uuid = Input::get('uuid');

            $engagement = CustomerEngagement::where('customer_id', $this->id)
        ->whereRaw("(uuid = '$uuid')")->first();

            // update updated_at
            if ($engagement) {
            } else {
                $engagement = new CustomerEngagement();
                $engagement->date_added = date('Y-m-d');
            }

            $engagement->customer_id = $this->id;
            $engagement->platform = $platform;
            $engagement->uuid = $uuid;
            $engagement->ip = $this->getIP();

            if (Input::has('phone')) {
                $engagement->make = Input::get('phone');
            }

            if (Input::has('version')) {
                $engagement->version = Input::get('version');
            }

            $engagement->scheme_number = $this->scheme_number;
            $engagement->save();

            $engagement->touch();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getLastSessionAttribute()
    {
        $lastSession = CustomerSession::where('customer_id', $this->id)
        ->orderBy('updated_at', 'DESC')
        ->first();

        return $lastSession;
    }

    public function getCurrencySignAttribute()
    {
        if ($this->scheme) {
            return $this->scheme->currency_sign;
        }

        return '€';
    }

    public function getCurrencyCodeAttribute()
    {
        if ($this->scheme) {
            return $this->scheme->currency_code;
        }

        return 978;
    }

    public function getLastEngagementAttribute()
    {
        $lastEngagement = CustomerEngagement::where('customer_id', $this->id)
        ->orderBy('updated_at', 'DESC')
        ->first();

        return $lastEngagement;
    }

    public function getLastCommandAttribute()
    {
        $lastCommandWeb = RTUCommandQueWebsite::where('customer_ID', $this->id)->orderBy('ID', 'DESC')->first();
        $lastCommandAuto = RTUCommandQue::where('customer_ID', $this->id)->orderBy('ID', 'DESC')->first();

        if ($lastCommandAuto && ! $lastCommandWeb) {
            return $lastCommandAuto;
        }

        if (! $lastCommandAuto && $lastCommandWeb) {
            return $lastCommandWeb;
        }

        if ($lastCommandAuto && $lastCommandWeb) {
            if ($lastCommandAuto->time_date > $lastCommandWeb->time_date) {
                return $lastCommandAuto;
            }
            if ($lastCommandAuto->time_date < $lastCommandWeb->time_date) {
                return $lastCommandWeb;
            }
        }

        return null;
    }

    public function sms($message, $charge = 0, $ret = false)
    {
        try {
            $mobile_number = $this->mobile_number;

            $sms = SMS::createAndSend($mobile_number, $message, $charge, $this);

            if ($ret) {
                return $sms;
            }

            return 'success';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function confirmsms($message, $charge = 0, $ret = false)
    {
        try {
            $arr = ['messagetext' => $message, 'senderid' => 'Repliable', 'recipients' => [$this->mobile_number]];

            $url = 'https://rest.sendmode.com/v2/send';
            $data = ['message' => json_encode($arr)];

            // use key 'http' even if you send the request to https://...
            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n".
                        "Authorization: EHJH6YEXNKRUO1YXGA0C\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ],
            ];

            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            // Add SMS to queue
            $sms_messages = new SMSMessage();
            $sms_messages->customer_id = $this->id;
            $sms_messages->mobile_number = $this->mobile_number;
            $sms_messages->message = $message;
            $sms_messages->date_time = date('Y-m-d H:i:s');
            $sms_messages->scheme_number = $this->scheme_number;
            $sms_messages->charge = $charge;
            $sms_messages->balance_before = $this->balance;
            $sms_messages->balance_after = $this->balance - $charge;
            $sms_messages->paid = 1;
            $sms_messages->message_sent = 1;
            $sms_messages->save();

            // Charge the customer
            $this->balance -= $charge;
            $this->save();

            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function toggleAwayMode()
    {
        try {
            $pmd = $this->permanentMeter;

            if (! $pmd) {
                throw new Exception('No permanent_meter_data entry!');
            }
            $permanent_meter_id = $pmd->ID;

            $rcs = $this->rcs;

            if (! $rcs) {
                $rcs = new RemoteControlStatus();
                $rcs->permanent_meter_id = $permanent_meter_id;
                $rcs->heating_on = 0;
                $rcs->away_mode_on = 0;
                $rcs->away_mode_end_datetime = date('Y-m-d H:i:s');
                $rcs->away_mode_permanent = 0;
                $rcs->away_mode_relay_status = 0;
                $rcs->away_mode_cancelled = 0;
                $rcs->heating_boost_on = 0;
                $rcs->heating_boost_cancelled = 0;
                $rcs->user_change_notification = 0;
                $rcs->being_used_by_control_program = 0;
                $rcs->save();
            }

            $rcs = RemoteControlStatus::where('permanent_meter_id', $permanent_meter_id)->first();

            if ($rcs->away_mode_on == 1) {

                // Turn away mode OFF

                $rcs->away_mode_retry_datetime = null;
                $rcs->away_mode_permanent = 0;
                $rcs->away_mode_on = 0;
                $rcs->away_mode_end_datetime = '2015-01-01 00:00:00';
                $rcs->save();

                return (object) [
                    'status' => 'off',
                    'error' => null,
                ];
            } else {

                // Turn away mode ON

                $rcs->away_mode_retry_datetime = null;
                $rcs->away_mode_on = 1;
                $rcs->away_mode_cancelled = 0;
                $rcs->away_mode_permanent = 1;
                $rcs->away_mode_end_datetime = '2050-10-10 00:00:00';
                $rcs->away_mode_relay_status = 0;
                $rcs->save();

                $this->confirmsms("You have just turned on away mode. Please reply with ' No ' if this request was not made by you.\n\nKind regards\nSnugZone", 0.12);

                return (object) [
                    'status' => 'on',
                    'error' => null,
                ];
            }
        } catch (Exception $e) {
            return (object) [
                'status' => 'unknown',
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ];
        }

        return (object) [
            'status' => 'unknown',
            'error' => null,
        ];
    }

    public function getAwayModeAttribute()
    {
        try {
            $pmd = $this->permanentMeter;

            if (! $pmd) {
                throw new Exception('No permanent_meter_data entry!');
            }
            $permanent_meter_id = $pmd->ID;

            $rcs = $this->rcs;

            if (! $rcs) {
                $rcs = new RemoteControlStatus();
                $rcs->permanent_meter_id = $permanent_meter_id;
                $rcs->heating_on = 0;
                $rcs->away_mode_on = 0;
                $rcs->away_mode_end_datetime = date('Y-m-d H:i:s');
                $rcs->away_mode_permanent = 0;
                $rcs->away_mode_relay_status = 0;
                $rcs->away_mode_cancelled = 0;
                $rcs->heating_boost_on = 0;
                $rcs->heating_boost_cancelled = 0;
                $rcs->user_change_notification = 0;
                $rcs->being_used_by_control_program = 0;
                $rcs->save();
            }

            $rcs = RemoteControlStatus::where('permanent_meter_id', $permanent_meter_id)->first();

            if ($rcs->away_mode_on == 1) {
                return (object) [
                    'rcs' => $rcs,
                    'status' => 'off',
                    'error' => null,
                ];
            } else {
                return (object) [
                    'rcs' => $rcs,
                    'status' => 'on',
                    'error' => null,
                ];
            }
        } catch (Exception $e) {
            return (object) [
                'rcs' => null,
                'status' => 'unknown',
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ];
        }

        return (object) [
            'rcs' => null,
            'status' => 'unknown',
            'error' => null,
        ];
    }

    private function getIP()
    {
        $ip_address = 'unset';

        if (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            if (! empty($_SERVER['REMOTE_ADDR'])) {
                $ip_address = $_SERVER['REMOTE_ADDR'];
            }
        }

        return $ip_address;
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public static function get($key, $value)
    {
        $customers_get = [];

        try {
            switch ($key) {

                case 'name':
                case 'email_address':
                case 'mobile_number':

                    $customers = self::activeScheme()->orderBy('customers.id', 'DESC')->get();

                    $value = preg_replace('/\s/', '', $value);
                    //
                    foreach ($customers as $k=>$c) {
                        if ($key == 'name') {
                            $fullname = preg_replace('/\s/', '', $c->first_name.' '.$c->surname);
                            //echo "Checking if $fullname contains $value or vice versa\n";
                            if ((strpos($fullname, $value) !== false) || (strpos($value, $fullname) !== false)) {
                                $customers_get = [];
                                array_push($customers_get, $c);

                                return (count($customers_get) > 0) ? $customers_get : false;
                            }
                            if ((strpos($c->first_name, $value) !== false) || (strpos($value, $c->first_name) !== false)

                                || (strpos($c->surname, $value) !== false) || (strpos($value, $c->surname) !== false)) {
                                //echo $c->username . "\n";
                                array_push($customers_get, $c);
                                continue;
                            }
                        }
                        if (strcmp($c->$key, $value) == 0) {
                            array_push($customers_get, $c);
                            continue;
                        }
                        if ((strpos($c->$key, $value) !== false) || (strpos($value, $c->$key) !== false)) {
                            array_push($customers_get, $c);
                            continue;
                        }
                    }

                break;
            }
        } catch (Exception $e) {
            echo 'Error occured: '.$e->getMessage();
        }

        return (count($customers_get) > 0) ? $customers_get : false;
    }

    public function getLastEVChargeAttribute()
    {
        return EVUsage::where('customer_id', $this->id)->orderBy('id', 'DESC')->first();
    }

    public function getNotifications($type)
    {
        $notifications = [];

        if ($type == 'unread') {
            $notifications = InAppNotification::where('customer_id', $this->id)->where('delivered', 0)->orderBy('id', 'ASC')->first();
        }

        return $notifications;
    }

    public function getSubscriptionAttribute()
    {
        return StripeCustomerSubscription::where('customer_id', $this->id)->where('active', 1)->first();
    }

    public function getSubscriptionTransactionsAttribute()
    {
        return StripeCustomerPayment::where('customer_id', $this->id)
        ->whereRaw("(description LIKE '%Invoice%')")
        ->orderBy('id', 'DESC')->get();
    }

    public function getSubscriptionPaymentsAttribute()
    {
        return StripeCustomerPayment::where('customer_id', $this->id)
        ->whereRaw("(description LIKE '%auto topup%')")
        ->orderBy('id', 'DESC')->get();
    }

    public function getLastTopAttribute()
    {
        return PaymentStorage::where('customer_id', $this->id)->orderBy('time_date', 'DESC')->first();
    }

    public function getStatements($from = null, $to = null)
    {
        if ($from == null || $to == null) {
            return SnugzoneAppStatement::where('customer_id', $this->id)->get();
        }

        return SnugzoneAppStatement::where('customer_id', $this->id)
        ->whereRaw("(created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59')")->get();
    }

    public function sendSetupEmail()
    {
        $userInfo = [];
        $data = [
            'first_name' => $this->first_name,
            'email_address' => $this->email_address,
            'username' => $this->username,
            'starting_balance' => $this->starting_balance,
            'currency_sign' => $this->currency_sign,
        ];
        $userInfo['email_address'] = $this->email_address;

        return Mail::send('emails.customer_set_up', $data, function ($message) use ($userInfo) {
            $message->from('noreply@snugzone.biz')->subject('SnugZone Login Credentials');
            $message->to($userInfo['email_address']);
        });
    }

    /*
    Dear customer,

Here are your SnugZone login credentials:

Username/Account Number:
22neptune

Email:
mojek.natalia.k@gmail.com

Password:
- The password that you enter on your first login attempt will become your password. -

Current balance: 17.01

If you are having difficulties logging in with the app, use our website:
https://app.snugzone.biz/

SnugZone.

    */

    public function sendAccountDetails($cost = 0, $reset = false)
    {
        $msg = 'Hi '.$this->first_name.",\n\n";
        $msg .= "Here are your SnugZone login credentials:\n";
        $msg .= "Username/Account Number:\n".$this->username."\n\n";
        $msg .= "Email:\n".$this->email_address."\n\n";

        if ($reset) {
            DB::table('customers')->where('id', $this->id)->update(['password' => '']);
        } else {
            $msg .= "Password:\nYour password is the same. If you've forgotten your password, reset it in the login page.\n\n";
        }

        $msg .= "Current balance:\n".number_format($this->balance, 2)."\n\n";
        $msg .= "If you are having difficulties logging in with the app, use our web app:\nhttps://app.snugzone.biz/\n\n";
        $msg .= "\nSnugZone.";

        $this->sms($msg, $cost);
    }

    public function sendSetupSMS()
    {
        $msg = 'Hi '.$this->first_name."\n\n";
        $msg .= "You are now registered with SnugZone.\n";
        $msg .= "To download the app and login please visit www.snugzone.biz.\n\n";
        $msg .= "Login Credentials:\n\n";
        $msg .= "Email:\n".$this->email_address."\n\n";
        $msg .= "Username\Account Number:\n".$this->username."\n\n";
        $msg .= "Password:\nThe password that you enter on your first login attempt will become your password.\n\n";
        $msg .= "Starting balance:\n".$this->starting_balance."\n\n";
        $msg .= "If you are having difficulties logging in with the app, use our web app:\nhttps://app.snugzone.biz/\n\n";
        $msg .= "\nSnugZone.";

        $this->sms($msg, 0);
    }

    public static function openAccount($data)
    {
        if (! is_array($data)) {
            throw new Exception('Invalid data supplied for open account argument');
        }
        if (! isset($data['username'])) {
            throw new Exception('Username data not supplied in data array');
        }
        if (! isset($data['email_address'])) {
            throw new Exception('Email address data not supplied in data array');
        }
        if (! isset($data['mobile_number'])) {
            throw new Exception('Mobile number data not supplied in data array');
        }
        if (! isset($data['nominated_telephone'])) {
            throw new Exception('Nominated Telephone data not supplied in data array');
        }
        if (! isset($data['commencement_date'])) {
            throw new Exception('Commencement date data not supplied in data array');
        }
        if (! isset($data['selectedUnit'])) {
            throw new Exception('Selected unit (PMD-METER#) data not supplied in data array');
        }
        if (! isset($data['role'])) {
            throw new Exception('Role data not supplied in data array');
        }
        if (! isset($data['balance'])) {
            throw new Exception('Balance data not supplied in data array');
        }
        if (! isset($data['starting_balance'])) {
            throw new Exception('Starting balance data not supplied in data array');
        }
        if (! isset($data['first_name'])) {
            throw new Exception('First name data not supplied in data array');
        }
        if (! isset($data['surname'])) {
            throw new Exception('Surname data not supplied in data array');
        }
        if (! isset($data['arrears'])) {
            throw new Exception('Arrears data not supplied in data array');
        }
        if (! isset($data['arrears_daily_repayment'])) {
            throw new Exception('Arrears Daily Repayment data not supplied in data array');
        }
        // Find the PermanentMeterData Entry
        $permanentMeterData = PermanentMeterData::where('meter_number', $data['selectedUnit'])
        ->where('in_use', 0)->first();
        if (! $permanentMeterData) {
            throw new Exception('Could not locate PermanentMeterData Meter Number '.$data['selectedUnit']);
        }

        /*
        * Step 1. District Heating Meter
        */
        // Check if DistrictHeatingMeter Exists
        $districtHeatingMeter = DistrictHeatingMeter::where('meter_number', $data['selectedUnit'])->first();
        // Create a new DistrictHeatingMeter Entry if one doesn't already exist
        if (! $districtHeatingMeter) {
            $districtHeatingMeter = new DistrictHeatingMeter();
        }
        $districtHeatingMeter->meter_number = $data['selectedUnit'];
        // Only grab meter data info if the districtHeatingMeter didn't alreay exist
        if (! $districtHeatingMeter) {
            $districtHeatingMeter->latest_reading = $permanentMeterData->last_reading;
            $districtHeatingMeter->sudo_reading = $permanentMeterData->last_reading;
            $districtHeatingMeter->last_flow_temp = $permanentMeterData->last_temp;
            $districtHeatingMeter->last_return_temp = $permanentMeterData->last_temp;
            $districtHeatingMeter->last_temp_time = $permanentMeterData->last_temp_time;
            $districtHeatingMeter->last_valve_status = $permanentMeterData->last_valve;
            $districtHeatingMeter->last_valve_status_time = $permanentMeterData->last_valve_time;
            $districtHeatingMeter->latest_reading_time = $permanentMeterData->last_reading_time;
        }
        $districtHeatingMeter->start_of_month_reading = 0;
        $districtHeatingMeter->previous_monthly_readings = '';
        $districtHeatingMeter->shut_off_reading = 0;
        $districtHeatingMeter->last_shut_off_time = '0000-00-00 00:00:00';
        $districtHeatingMeter->last_restart_time = '0000-00-00 00:00:00';
        $districtHeatingMeter->shut_off_device_number = 0;
        $districtHeatingMeter->meter_contact_number = '1.1.1.1';
        $districtHeatingMeter->shut_off_device_contact_number = '1.1.1.1';
        $districtHeatingMeter->pin = 0;
        $districtHeatingMeter->port = 1;
        $districtHeatingMeter->scu_type = 'm';
        $districtHeatingMeter->permanent_meter_ID = $permanentMeterData->ID;
        $districtHeatingMeter->scheme_number = $permanentMeterData->scheme_number;
        $districtHeatingMeter->save();
        $permanentMeterData->in_use = 1;
        $permanentMeterData->save();
        $meter_ID = $districtHeatingMeter->meter_ID;
        SystemLog::log('open_account', 'Finalised DistrictHeatingMeter #'.$meter_ID);

        /*
        * Step 2. Create a new Customer Entry
        */
        $customer = self::where('status', '=', 0)->first();
        if (! $customer) {
            throw new Exception('Cannot find an empty Customer Row to fill customer data into!');
        }
        // Check if a customer is already using this meter_ID
        $customersUsingMeter = self::where('meter_ID', $meter_ID)->get();
        if (count($customersUsingMeter) > 0) {
            throw new Exception("A Customer Row is already using Meter ID #$meter_ID. Please vacate this ID from them!");
        }
        $customer->role = $data['role'];
        $customer->meter_ID = $meter_ID;
        $customer->balance = $data['balance'];
        $customer->house_number_name = $permanentMeterData->house_name_number;
        $customer->scheme_number = $permanentMeterData->scheme_number;
        $customer->starting_balance = $data['starting_balance'];
        $customer->username = $data['username'];
        $customer->first_name = $data['first_name'];
        $customer->surname = $data['surname'];
        $customer->email_address = $data['email_address'];
        $customer->mobile_number = $data['mobile_number'];
        $customer->nominated_telephone = $data['nominated_telephone'];
        $customer->commencement_date = $data['commencement_date'];
        $customer->arrears = $data['arrears'];
        $customer->arrears_daily_repayment = $data['arrears_daily_repayment'];
        $customer->street1 = $permanentMeterData->street1;
        $customer->street2 = $permanentMeterData->street2;
        $customer->town = $permanentMeterData->town;
        $customer->county = $permanentMeterData->county;
        $customer->country = $permanentMeterData->country;
        $customer->postcode = $permanentMeterData->postcode;
        $customer->activated = 1;
        $customer->status = 1;
        $customer->save();
        SystemLog::log('open_account', 'Finalised Customer #'.$customer->id);

        /*
        * Step 2.5. Create a new Customer Arrears Entry if applicable
        */
        if ($data['arrears'] > 0 && $data['arrears_daily_repayment'] > 0) {
            $customerArrears = new CustomerArrears();
            $customerArrears->customer_id = $customer->id;
            $customerArrears->scheme_number = $customer->scheme_number;
            $customerArrears->amount = $data['arrears'];
            $customerArrears->repayment_amount = $data['arrears_daily_repayment'];
            $customerArrears->date = date('Y-m-d');
            $customerArrears->save();
            SystemLog::log('open_account', 'Started Arrears of '.$data['arrears'].'/'.$data['arrears_daily_repayment'].' for Customer #'.$customer->id);
        }

        /*
        * Step 3. *final* Send setup notifs
        */
        $customer->sendSetupEmail();
        $customer->sendSetupSMS();
        SystemLog::log('open_account', 'Sent Setup email & SMS for Customer #'.$customer->id);

        return $customer;
    }

    public static function closeAccount($customer_id)
    {
        try {
            $customer = self::find($customer_id);
            if (! $customer) {
                throw new Exception('This customer #'.$customer_id." is already deleted or doesn't exist!");
            }
            $dhm = $customer->districtMeter;
            if (! $dhm) {
                throw new Exception('This customer #'.$customer_id." doesn't have a DistrictHeatingMeter entry (meter_ID = '".$customer->meter_ID."')!");
            }
            $pmd = $customer->permanentMeter;
            if (! $pmd) {
                throw new Exception('This customer #'.$customer_id." doesn't have a PermanentMeterData entry!");
            }
            /*
            * Step 1. Delete the actual customer
            */
            $customer->delete();

            /*
            * Step 2. Set the DistrictHeatingMeter Meter_Number to null & PermanentMeterID to null
            */
            $dhm->meter_number = null;
            $dhm->permanent_meter_ID = null;
            $dhm->save();

            /*
            * Step 3. Set PermanentMeterData InUse to 0
            */
            $pmd->in_use = 0;
            $pmd->save();

            /*
            * Step 4. *final* Create a customer deletion log entry
            */
            $customerDeletion = new CustomerDeletion();
            $customerDeletion->customer_id = $customer->id;
            if (Auth::check() && Auth::user() && Auth::user() != null) {
                $customerDeletion->operator_id = Auth::user()->id;
            } else {
                $customerDeletion->operator_id = 0;
            }
            $customerDeletion->reason = 'n/a';
            $customerDeletion->save();

            SystemLog::log('close_account', 'Closed Customer #'.$customer->id);

            return true;
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    public function getAddressFormattedAttribute()
    {
        $print = '';
        try {
            $address = $this->address;

            if (strlen($address->line1) > 2) {
                $print .= $address->line1;
            }
            if (strlen($address->line2) > 2) {
                $print .= "\n".$address->line2;
            }
            if (strlen($address->city) > 2) {
                $print .= "\n".$address->city;
            }
            if (strlen($address->county) > 2) {
                $print .= "\n".$address->county;
            }
            if (strlen($address->postcode) > 2) {
                $print .= "\n".$address->postcode."\n";
            }

            return $print;
        } catch (Exception $e) {
        }

        return $print;
    }

    public function getAddressAttribute()
    {
        $pmd = $this->permanentMeter;
        $scheme = $this->scheme;

        $address = (object) [
            'line1'		=> '',
            'line2' 	=> '',
            'city' 		=> '',
            'county' 	=> '',
            'country' 	=> 'Ireland',
            'postcode' 	=> '',
        ];

        if (! $pmd) {
            return $address;
        }

        try {
            if (strpos($pmd, 'Hall') !== false) {
                $pmd->street1 = 'Fairways Hall';
                $this->house_number_name = str_replace('h', '', $this->house_number_name);
            }
            $address->line1 = $this->house_number_name.' '.$pmd->street1;

            $address->line2 = ucfirst($pmd->street2);
            if (empty($address_2)) {
                $address->line2 = ucfirst($scheme->street2);
            }

            $address->city = ucfirst($pmd->town);
            if (strlen($address->city) <= 3) {
                $address->city = ucfirst($scheme->town);
            }
            if ($address->city == 'Dublin') {
                $address->city = '';
            }

            $address->county = 'Co. '.ucfirst($pmd->county);
            if (strlen($address->county) <= 3) {
                $address->county = 'Co. '.ucfirst($scheme->county);
            }

            $address->postcode = $pmd->postcode;
            if (strlen($address->postcode) <= 3 || strpos($address->postcode, '111') !== false
            || strpos($address->postcode, '000') !== false) {
                $address->postcode = '';
            }

            return $address;
        } catch (Exception $e) {
            //echo $e->getMessage();
        }

        return $address;
    }
}
