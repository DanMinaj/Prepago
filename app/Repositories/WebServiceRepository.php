<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\CustomerRC;
use App\Models\DistrictHeatingMeter;
use App\Models\DistrictHeatingUsage;
use App\Models\IOUExtraStorage;
use App\Models\IOUStorage;
use App\Models\PaymentLocations;
use App\Models\PaymentStorage;
use App\Models\PaymentStorageError;
use App\Models\PermanentMeterData;
use App\Models\RegisteredPhonesWithApps;
use App\Models\RemoteControlStatus;
use App\Models\RemoteControlTimes;
use App\Models\RTUCommandQue;
use App\Models\Scheme;
use App\Models\SMSMessage;
use App\Models\SystemSetting;
use App\Models\TemporaryPayments;
use App\Models\Weather;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;



class WebServiceRepository implements WebServiceRepositoryInterface
{
    private $customer_type = 'regular';
    private $ppSandbox = false;

    public function __construct()
    {
        $this->log = new Logger('View Paypal IPN Logs');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/paypal_payments_'.date('Y-m-d').'.log'), Logger::INFO));
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getCustomerLoginRequest($email, $username, $password, $phone_id, $model = '')
    {
        try {
            $response = [];
            $response['customer_id'] = '';
            $response['service_type'] = '';
            $response['error'] = '';
            $response['error_url'] = '';
            $response['permanent_meter_id'] = '';
            $response['has_remote_control'] = '';
            $response['is_prepay'] = '';

            if (SystemSetting::get('disable_old_app') == '1') {
                throw new Exception(SystemSetting::get('disable_old_app_msg'));
            }

            $this->check_password($username, $password, $email);

            if ($this->login_verification($username, $password, $email)) {
                $data = $this->get_customerid_scheme_number_service_type($username, $password, $email);
                $data['service_type'] = $this->get_service_type($data['scheme_number']);
                $data['permanent_meter_id'] = $this->get_permanent_meter_id($data['id']);
                $data['heat_port'] = $this->get_heat_port($data['permanent_meter_id']);
                $data['has_remote_control'] = false;
                if ($data['heat_port'] === -1) {
                    $data['has_remote_control'] = false;
                } elseif ($data['heat_port'] >= 1) {
                    $data['has_remote_control'] = true;
                }

                //if (!$this->check_phone_registration($phone_id)) {
                if (! $this->check_phone_registration($phone_id, $data['id'])) {
                    $date = date('Y-m-d');
                    $this->phone_entry($phone_id, $data['id'], $date, $data['scheme_number'], $model);
                }

                $response['customer_id'] = (string) $data['id'];
                $response['service_type'] = (string) $data['service_type'];
                $response['error'] = '';
                $response['error_url'] = '';
                $response['permanent_meter_id'] = $data['permanent_meter_id'];
                $response['has_remote_control'] = $data['has_remote_control'];
                $response['is_prepay'] = $this->customer_type == 'rc' ? false : true;

                unset($response['scheme_number']);
                unset($response['heat_port']);

                $customer = Customer::find($response['customer_id']);
                if ($customer) {
                    $customer->last_login_time = date('Y-m-d H:i:s');
                    $customer->save();
                }

                $this->logActivity($response['customer_id'], 'login');

                return $response;
            } else {
                $user = Customer::where('username', $username)->where('deleted_at', null)->first();

                if (! $user) {
                    $response['error'] = 'Username not found.';
                } else {

                    // Make inputted email and actual email lowercase for comparison
                    $email = strtolower(trim($email));
                    $user->email_address = strtolower(trim($user->email_address));

                    if ($user->email_address != $email && $user->password != $password) {
                        $response['error'] = 'Incorrect email and password for that user.';
                    } elseif ($user->password != $password) {
                        $response['error'] = 'Incorrect password.';
                    } elseif ($user->email_address != $email) {
                        $response['error'] = 'Incorrect email address.';
                    }
                }

                $response['error_url'] = 'http://www.snugzone.biz/findbalance.html';

                return $response;
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response['error_url'] = 'https://www.snugzone.biz/error.html?e='.$response['error'];

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getInformationRequest($customer_id, $email, $username, $password)
    {
        try {
            $response = [
                'username' => '',
                'balance' => '',
                'unit_abbreviation' => '',
                'shut_off' => '',
                'meter_ID' => '',
                'ev_meter_ID' => '',
                'barcode' => '',
                'IOU_available' => '',
                'IOU_extra_available' => '',
                'IOU_used' => '',
                'IOU_extra_used' => '',
                'latest_reading' => '',
                'ev_latest_reading' => '',
                'used_yesterday' => '',
                'scheduled_to_shut_off' => '',
                'shut_off_device_status' => '',
                'IOU_text' => '',
                'IOU_extra_text' => '',
                'currency_sign' => '',
                'message' => '',
                'flag_message' => 0,
                'error' => '',
            ];

            if (SystemSetting::get('disable_old_app') == '1') {
                throw new Exception(SystemSetting::get('disable_old_app_msg'));
            }

            if ($this->login_verification($username, $password, $email) || isset($_GET['testing'])) {
                $user_data = $this->get_user_data($customer_id);
                if ($user_data) {
                    $response['username'] = isset($user_data['username']) ? $user_data['username'] : '';
                    $response['scheme_number'] = isset($user_data['scheme_number']) ? $user_data['scheme_number'] : '';
                    $response['balance'] = isset($user_data['balance']) ? (string) $user_data['balance'] : '';
                    $response['unit_abbreviation'] = isset($user_data['unit_abbreviation']) ? (string) $user_data['unit_abbreviation'] : '';
                    $response['shut_off'] = isset($user_data['shut_off']) ? (string) $user_data['shut_off'] : '';
                    $response['meter_ID'] = isset($user_data['meter_ID']) ? (string) $user_data['meter_ID'] : '';
                    $response['ev_meter_ID'] = isset($user_data['ev_meter_ID']) ? (string) $user_data['ev_meter_ID'] : '';
                    $response['barcode'] = isset($user_data['barcode']) ? $user_data['barcode'] : '';
                    $response['IOU_available'] = isset($user_data['IOU_available']) ? (string) $user_data['IOU_available'] : '';
                    $response['IOU_extra_available'] = isset($user_data['IOU_extra_available']) ? (string) $user_data['IOU_extra_available'] : '';
                    $response['IOU_used'] = isset($user_data['IOU_used']) ? (string) $user_data['IOU_used'] : '';
                    $response['IOU_extra_used'] = isset($user_data['IOU_extra_used']) ? (string) $user_data['IOU_extra_used'] : '';
                    $response['latest_reading'] = isset($user_data['latest_reading']) ? (string) $user_data['latest_reading'] : '';
                    $response['ev_latest_reading'] = isset($user_data['ev_latest_reading']) ? (string) $user_data['ev_latest_reading'] : '';
                    $response['used_yesterday'] = isset($user_data['used_yesterday']) ? (string) $user_data['used_yesterday'] : '';
                    $response['scheduled_to_shut_off'] = isset($user_data['scheduled_to_shut_off']) ? (string) $user_data['scheduled_to_shut_off'] : '';
                    $response['shut_off_device_status'] = isset($user_data['shut_off_device_status']) ? (string) $user_data['shut_off_device_status'] : '';

                    $data = $this->get_IOU_text($response['scheme_number']);
                    $response['IOU_text'] = $data['IOU_text'];
                    $response['IOU_extra_text'] = $data['IOU_extra_text'];
                    $response['currency_sign'] = $data['currency_sign'];

                    if ($response['IOU_used'] == 1) {
                        $response['balance'] = $response['balance'] * 1 + $data['IOU_amount'] * 1;
                    }

                    unset($response['scheme_number']);
                } else {
                    $response['error'] = 'Customer Details Do Not Match';
                }

                return $response;
            } else {
                $response['error'] = 'Customer Details Do Not Match';

                return $response;
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getIOURequest($customer_id, $email, $username, $password, $IOU_type)
    {
        $response = [
            'username' => '',
            'balance' => '',
            'shut_off' => '',
            'meter_ID' => '',
            'barcode' => '',
            'IOU_available' => '',
            'IOU_extra_available' => '',
            'IOU_used' => '',
            'IOU_extra_used' => '',
            'latest_reading' => '',
            'used_yesterday' => '',
            'scheduled_to_shut_off' => '',
            'shut_off_device_status' => '',
            'IOU_text' => '',
            'IOU_extra_text' => '',
            'message' => '',
            'IOUsuccess' => '',
            'flag_message' => '',
            'error' => '',
        ];

        if ($this->login_verification($username, $password, $email)) {
            $user_data = $this->get_user_data($customer_id);
            $isSuccess = 0;

            if (! $user_data || ! isset($user_data['scheme_number']) || ! isset($user_data['balance'])) {
                $response['error'] = 'Customer not found';

                return $response;
            }

            //Check if the customer's balance and will allow them use an IOU vs the Schemes details
            if ($IOU_type == 1) {
                $isSuccess = $this->set_IOU($customer_id, $user_data['scheme_number'], $user_data['balance']);
            } elseif ($IOU_type == 2) {
                $isSuccess = $this->set_IOU_extra($customer_id, $user_data['scheme_number'], $user_data['balance']);
            }

            //Get the customer and meter details
            $user_data = $this->get_user_data($customer_id);
            $data = $this->get_IOU_text($user_data['scheme_number']);
            $user_data['IOU_text'] = $data['IOU_text'];
            $user_data['IOU_extra_text'] = $data['IOU_extra_text'];

            //If the customer is allowed to use an IOU begin the process of giving the customer an IOU
            if ($isSuccess == 1) {
                if ($IOU_type == 1) {
                    //Insert an IOU into the database and change customer iou settings
                    $user_data['message'] = $data['IOU_message'];
                    $this->insert_iou_entry($customer_id, $user_data['scheme_number']);
                } elseif ($IOU_type == 2) {
                    //Insert an IOU Extra into the database and change customer iou settings
                    $user_data['message'] = $data['IOU_extra_message'];
                    $this->insert_iou_extra_entry($customer_id, $user_data['scheme_number']);
                }

                //If the customer was shut off before the IOU was used we must turn them back on
                if ($user_data['shut_off'] == '1') {

                    //Update customer.shut_off, customer.shut_off_command_sent and district_heating.shut_off_device_status
                    $this->shuttoff1($customer_id, $user_data['meter_ID']);

                    //check which type of RTU is being used
                    if ($user_data['scu_type'] == 'a' || $user_data['scu_type'] == 'd') {
                        $this->add_rtu_entry($customer_id);
                    } elseif ($user_data['scu_type'] == 'b') {
                        //code to do
                    } elseif ($user_data['scu_type'] == 'c') {
                        //code to do
                    }
                }

                $user_data['IOUsuccess'] = 1;
            } else {
                $user_data['message'] = 'IOU failed! No IOU available or balance is to low. Please top up.';
                $user_data['IOUsuccess'] = 0;
            }

            $user_data['flag_message'] = 1;

            if ($IOU_type == 0) {
                $user_data['error'] = 'Wrong choice';
            } else {
                $user_data['error'] = '';
            }

            $response['username'] = isset($user_data['username']) ? $user_data['username'] : '';
            $response['scheme_number'] = isset($user_data['scheme_number']) ? $user_data['scheme_number'] : '';
            $response['balance'] = isset($user_data['balance']) ? (string) $user_data['balance'] : '';
            $response['shut_off'] = isset($user_data['shut_off']) ? (string) $user_data['shut_off'] : '';
            $response['meter_ID'] = isset($user_data['meter_ID']) ? (string) $user_data['meter_ID'] : '';
            $response['barcode'] = isset($user_data['barcode']) ? $user_data['barcode'] : '';
            $response['IOU_available'] = isset($user_data['IOU_available']) ? (string) $user_data['IOU_available'] : '';
            $response['IOU_extra_available'] = isset($user_data['IOU_extra_available']) ? (string) $user_data['IOU_extra_available'] : '';
            $response['IOU_used'] = isset($user_data['IOU_used']) ? (string) $user_data['IOU_used'] : '';
            $response['IOU_extra_used'] = isset($user_data['IOU_extra_used']) ? (string) $user_data['IOU_extra_used'] : '';
            $response['latest_reading'] = isset($user_data['latest_reading']) ? (string) $user_data['latest_reading'] : '';
            $response['used_yesterday'] = isset($user_data['used_yesterday']) ? (string) $user_data['used_yesterday'] : '';
            $response['scheduled_to_shut_off'] = isset($user_data['scheduled_to_shut_off']) ? (string) $user_data['scheduled_to_shut_off'] : '';
            $response['shut_off_device_status'] = isset($user_data['shut_off_device_status']) ? (string) $user_data['shut_off_device_status'] : '';
            $response['IOU_text'] = isset($user_data['IOU_text']) ? $user_data['IOU_text'] : '';
            $response['IOU_extra_text'] = isset($user_data['IOU_extra_text']) ? $user_data['IOU_extra_text'] : '';
            $response['message'] = isset($user_data['message']) ? $user_data['message'] : '';
            $response['IOUsuccess'] = isset($user_data['IOUsuccess']) ? $user_data['IOUsuccess'] : 0;
            $response['flag_message'] = isset($user_data['flag_message']) ? $user_data['flag_message'] : 0;
            $response['error'] = isset($user_data['error']) ? $user_data['error'] : '';

            if ($response['IOU_used'] == 1) {
                $response['balance'] = $response['balance'] + $data['IOU_amount'];
            }

            unset($response['scheme_number']);

            return $response;
        } else {
            $response['error'] = 'Customer Details Do Not Match';

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getRecentTopUpsRequest($customer_id, $email, $username, $password)
    {
        $response = [];
        $response['topup'] = '';
        $response['flag_message'] = 0;
        $response['error'] = '';

        if ($this->login_verification($username, $password, $email)) {
            $data = $this->get_recent_top_ups($customer_id);
            if ($data) {
                foreach ($data as $key => $val) {
                    foreach ($val as $key1 => $val1) {
                        $response['topup'][$key][$key1] = (string) $val1;
                    }
                }
            }
            $response['error'] = '';

            return $response;
        } else {
            $response['flag_message'] = 1;
            $response['error'] = 'Customer Details Do Not Match';

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getBarcodeRequest($customer_id, $email, $username, $password)
    {
        $response = [];
        $response['flag_message'] = 0;
        $response['error'] = '';
        $response['barcode'] = '';

        if ($this->login_verification($username, $password, $email)) {
            $data = $this->get_barcode_scheme_number($username, $password, $email);

            if ($data && isset($data['barcode'])) {
                $public_path = $_SERVER['DOCUMENT_ROOT'];

                $barcode_file_name = $public_path.'/Barcodes/'.$data['barcode'].'.png';
                $barcode_file = $this->read_file($barcode_file_name);

                $response['error'] = '';
                if ($barcode_file) {
                    $response['barcode'] = base64_encode($barcode_file);
                }
            } else {
                $response['flag_message'] = 1;
                $response['error'] = 'No barcode found';
            }

            return $response;
        } else {
            $response['flag_message'] = 1;
            $response['error'] = 'Customer Details Do Not Match';

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getFAQRequest($customer_id, $email, $username, $password)
    {
        $response = [];
        $response['flag_message'] = 0;
        $response['error'] = '';

        if ($this->login_verification($username, $password, $email)) {
            $data = $this->get_barcode_scheme_number($username, $password, $email);

            if ($data && isset($data['scheme_number'])) {
                $faq_str = $this->get_FAQ($data['scheme_number']);
                $response['faq'] = json_decode($faq_str);
            } else {
                $response['flag_message'] = 1;
                $response['error'] = 'No Frequently Asked Questions Found!';
            }

            return $response;
        } else {
            $response['flag_message'] = 1;
            $response['error'] = 'Customer Details Do Not Match';

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getTopupLocationsRequest($customer_id, $email, $username, $password, $lat, $lon)
    {
        $response = [];
        $response['flag_message'] = 0;
        $response['error'] = '';

        if ($this->login_verification($username, $password, $email)) {
            $data = $this->get_payment_locations($lat, $lon);
            if ($data) {
                $response['topup_locations'] = $data;
            } else {
                $response['flag_message'] = 1;
                $response['error'] = 'No Top-Up Locations Found!';
            }

            return $response;
        } else {
            $response['flag_message'] = 1;
            $response['error'] = 'Customer Details Do Not Match';

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getBarcodeWebappRequest($username)
    {
        $response = [];
        $response['barcodeNumber'] = '';
        $response['barcodeImage_base64_encode'] = '';
        $response['flag_message'] = 0;
        $response['error'] = '';

        if ($this->get_barcode_exists($username)) {
            $data = [];
            $data = $this->get_barcode($username);
            if ($data && isset($data['barcode'])) {
                $public_path = $_SERVER['DOCUMENT_ROOT'];
                $barcode_file_name = $public_path.'/Barcodes/'.$data['barcode'].'.png';
                $barcode_file = $this->read_file($barcode_file_name);

                $response['barcodeNumber'] = $data['barcode'];
                if ($barcode_file) {
                    $response['barcodeImage_base64_encode'] = base64_encode($barcode_file);
                }
                $response['error'] = '';
            } else {
                $response['flag_message'] = 1;
                $response['error'] = 'No Barcode Found';
            }

            return $response;
        } else {
            $response['flag_message'] = 1;
            $response['error'] = 'Invalid Username';

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getRemoteControlInformationRequest($customer_id, $email, $username, $password)
    {
        $response = [];
        $response['shut_off'] = '';
        $response['heating_on'] = '';
        $response['heating_on_time'] = '';
        $response['heating_on_date'] = '';
        $response['heating_off_time'] = '';
        $response['heating_off_date'] = '';
        $response['boost'] = '';
        $response['away_mode'] = '';
        $response['Daily Timer'] = '';
        $response['flag_message'] = 0;
        $response['error'] = '';

        if ($this->login_verification($username, $password, $email)) {
            $permanent_meter_id = $this->get_permanent_meter_id($customer_id);
            $data = $this->get_rc_info($permanent_meter_id);

            if (! $data) {
                $response['flag_message'] = 1;
                $response['error'] = 'No Information Found';
            } else {
                //if first run of request 9 and no data is associated with the user yet
                if (! $data['permanent_meter_id'] && ! $data['Daily Timer'] && $permanent_meter_id) {
                    $info = $this->saveRCInfoEmpty($permanent_meter_id);
                    if ($info['error']) {
                        $response['flag_message'] = 1;
                        $response['error'] = $info['error'];

                        return $response;
                    }
                }

                //update the information in the $data array
                $data = $this->get_rc_info($permanent_meter_id);
                $response['shut_off'] = (isset($data['shut_off']) && $data['shut_off'] == 1) ? true : false;
                $response['heating_on'] = (isset($data['heating_on']) && $data['heating_on'] == 1) ? true : false;
                if ($data['heating_turned_on_at_datetime'] && ! is_null($data['heating_turned_on_at_datetime'])) {
                    $heating_on_datetime = new DateTime($data['heating_turned_on_at_datetime']);
                    $heating_on_date = $heating_on_datetime->format('Y-m-d');
                    $heating_on_time = $heating_on_datetime->format('H:i');
                    $response['heating_on_date'] = $heating_on_date;
                    $response['heating_on_time'] = $heating_on_time;
                }
                if ($data['heating_to_be_turned_off_at_datetime'] && ! is_null($data['heating_to_be_turned_off_at_datetime'])) {
                    $heating_off_datetime = new DateTime($data['heating_to_be_turned_off_at_datetime']);
                    $heating_off_date = $heating_off_datetime->format('Y-m-d');
                    $heating_off_time = $heating_off_datetime->format('H:i');
                    $response['heating_off_date'] = $heating_off_date;
                    $response['heating_off_time'] = $heating_off_time;
                }

                /* GENERATE BOOST START */
                $response['boost']['boost_on'] = isset($data['heating_boost_on']) ? ($data['heating_boost_on'] == 1 ? true : false) : '';
                $response['boost']['boost_time_in_mins'] = isset($data['boost_time_in_mins']) ? ($data['boost_time_in_mins'] < 0 ? 0 : $data['boost_time_in_mins']) : 0;
                $boost_datetime = '';
                if ($data['heating_boost_end_datetime'] && $data['heating_boost_end_datetime'] != '0000-00-00 00:00:00' && ! is_null($data['heating_boost_end_datetime'])) {
                    $boost_datetime = new DateTime($data['heating_boost_end_datetime']);
                    $boost_date = $boost_datetime->format('Y-m-d');
                    $boost_time = $boost_datetime->format('H:i');
                    $response['boost']['date'] = $boost_date;
                    $response['boost']['time'] = $boost_time;
                } else {
                    $response['boost']['date'] = '';
                    $response['boost']['time'] = '';
                }
                /* GENERATE BOOST END */

                /* GENERATE AWAY_MODE START */
                $response['away_mode']['away_mode_on'] = isset($data['away_mode_on']) ? ($data['away_mode_on'] == 1 ? true : false) : '';
                $away_mode_datetime = '';
                if ($data['away_mode_end_datetime'] && $data['away_mode_end_datetime'] != '0000-00-00 00:00:00' && ! is_null($data['away_mode_end_datetime'])) {
                    $away_mode_datetime = new DateTime($data['away_mode_end_datetime']);
                    $away_date = $away_mode_datetime->format('Y-m-d');
                    $away_time = $away_mode_datetime->format('H:i');
                    $response['away_mode']['date'] = $away_date;
                    $response['away_mode']['time'] = $away_time;
                } else {
                    $response['away_mode']['date'] = '';
                    $response['away_mode']['time'] = '';
                }
                $response['away_mode']['permenantly'] = isset($data['away_mode_permanent']) ? ($data['away_mode_permanent'] == 1 ? true : false) : '';
                /* GENERATE AWAY_MODE END */

                /* GENERATE DAILY TIMER START */
                if (isset($data['Daily Timer'])) {
                    foreach ($data['Daily Timer'] as $key => $val) {
                        $response['Daily Timer'][$key]['Day'] = (isset($val['day']) && $val['day']) ? $val['day'] : '';
                        $response['Daily Timer'][$key]['Active'] = (isset($val['active']) && $val['active']) ? true : false;
                        //if no on/off times are set at least return the "Times" as empty array
                        $tmp_counter = 0;
                        for ($i = 1; $i <= 10; $i++) {
                            if (isset($val['t'.$i.'_start']) && isset($val['t'.$i.'_end']) && isset($val['t'.$i.'_active'])) {
                                $response['Daily Timer'][$key]['Times'][$i - 1]['On'] = isset($val['t'.$i.'_start']) ? $val['t'.$i.'_start'] : '';
                                $response['Daily Timer'][$key]['Times'][$i - 1]['Off'] = isset($val['t'.$i.'_end']) ? $val['t'.$i.'_end'] : '';
                                $response['Daily Timer'][$key]['Times'][$i - 1]['Active'] = isset($val['t'.$i.'_active']) && $val['t'.$i.'_active'] ? true : false;
                                $tmp_counter++;
                            } else {
                                continue;
                            }
                        }
                        if (! $tmp_counter) {
                            $response['Daily Timer'][$key]['Times'] = [];
                        }
                    }
                }
                /* GENERATE DAILY TIMER END */
            }

            return $response;
        } else {
            $response['flag_message'] = 1;
            $response['error'] = 'Customer Details Do Not Match';

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function setRemoteControlInformationRequest($customer_id, $email, $username, $password, $json)
    {
        $response = [];
        $response['flag_message'] = 0;
        $response['error'] = '';

        if ($this->login_verification($username, $password, $email)) {
            $permanent_meter_id = $this->get_permanent_meter_id($customer_id);
            $json_data = json_decode($json, true);

            $info = $this->saveRCInfo($permanent_meter_id, $json_data);
            if ($info['error']) {
                $response['flag_message'] = 1;
                $response['error'] = $info['error'];
            } else {
                $response = $this->getRemoteControlInformationRequest($customer_id, $email, $username, $password);
            }

            return $response;
        } else {
            $response['flag_message'] = 1;
            $response['error'] = 'Customer Details Do Not Match';

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function toggleRemoteControl($customer_id, $email, $username, $password)
    {
        $response = [];
        $response['flag_message'] = 0;
        $response['error'] = '';

        if ($this->login_verification($username, $password, $email) || $this->login_verification($username, sha1($password), $email)) {
            $json_data = null;
            $permanent_meter_id = $this->get_permanent_meter_id($customer_id);
            $remote_control_status_data = RemoteControlStatus::where('permanent_meter_id', '=', $permanent_meter_id)->get()->first();

            $rcs_action = ($remote_control_status_data && $remote_control_status_data->count()) ? 'update' : 'create';

            $time = time();
            $next_day = date('Y-m-d', strtotime('+1 day'));

            if ($rcs_action == 'create') {
                $json_data = [
                    'away_mode' => [
                        'away_mode_on' => '1',
                        'date' => $next_day,
                        'time' => '00:00:00',
                        'permenantly' => 1,
                    ],
                ];
            } else {
                if ($remote_control_status_data->away_mode_on) {
                    $json_data = [
                        'away_mode' => [
                            'away_mode_on' => '0',
                            'away_mode_cancelled' => 1,
                            'date' => $next_day,
                            'time' => '00:00:00',
                            'permenantly' => 0,
                        ],
                    ];
                } else {
                    $json_data = [
                        'away_mode' => [
                            'away_mode_on' => '1',
                            'date' => $next_day,
                            'time' => '00:00:00',
                            'permenantly' => 1,
                        ],
                    ];
                }
            }

            /* Populate the "remote_control_status" table START */
            $remote_control_status['permanent_meter_id'] = $permanent_meter_id;
            $remote_control_status['heating_on'] = '';
            $remote_control_status['heating_turned_on_at_datetime'] = $remote_control_status_data['heating_turned_on_at_datetime'] ? $remote_control_status_data['heating_turned_on_at_datetime'] : null;
            $remote_control_status['heating_to_be_turned_off_at_datetime'] = $remote_control_status_data['heating_to_be_turned_off_at_datetime'] ? $remote_control_status_data['heating_to_be_turned_off_at_datetime'] : null;

            if (isset($json_data['away_mode'])) {
                $remote_control_status['away_mode_on'] = $json_data['away_mode']['away_mode_on'];
                $remote_control_status['away_mode_end_datetime'] = $json_data['away_mode']['date'].' '.$json_data['away_mode']['time'];
                $remote_control_status['away_mode_permanent'] = $json_data['away_mode']['permenantly'];
                $remote_control_status['away_mode_relay_status'] = '';
                $remote_control_status['away_mode_cancelled'] = '';
                if ($rcs_action == 'update') {
                    //if ($remote_control_status_data['away_mode_on'] == 1 && !($json_data['away_mode']['away_mode_on']) && strtotime($remote_control_status['away_mode_end_datetime']) > time()) {
                    if ($remote_control_status_data['away_mode_on'] == 1 && ! $json_data['away_mode']['away_mode_on']) {
                        //$remote_control_status['away_mode_on'] = $remote_control_status_data['away_mode_on'];
                        $remote_control_status['away_mode_on'] = $json_data['away_mode']['away_mode_on'];
                        $remote_control_status['away_mode_end_datetime'] = $remote_control_status_data['away_mode_end_datetime'];
                        $remote_control_status['away_mode_cancelled'] = 1;
                    }
                }

                //check if a change was made to the DB and set the user_change_notification field to 1
                if (
                                $remote_control_status['away_mode_on'] != $remote_control_status_data['away_mode_on'] ||
                                $remote_control_status['away_mode_end_datetime'].':00' != $remote_control_status_data['away_mode_end_datetime'] ||
                                $remote_control_status['away_mode_permanent'] != $remote_control_status_data['away_mode_permanent'] ||
                                $remote_control_status['away_mode_cancelled'] != $remote_control_status_data['away_mode_cancelled']
                                ) {
                    $remote_control_status['user_change_notification'] = 1;
                }
            }

            if ($rcs_action == 'update') {
                if (! RemoteControlStatus::find($remote_control_status_data->permanent_meter_id)->update($remote_control_status)) {
                    throw new Exception('The table fields cannot be populated');
                } else {
                    echo 'Changed away mode status to '.$remote_control_status['away_mode_on'];
                }
            } else {
                if (! RemoteControlStatus::create($remote_control_status)) {
                    throw new Exception('The table fields cannot be populated');
                } else {
                    echo 'Created RC & turned on away mode';
                }
            }
        } else {
            $response['flag_message'] = 1;
            $response['error'] = 'Customer Details Do Not Match';

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function addPaypalPaymentRequest($customer_id, $email, $username, $password, $json, $debug = false)
    {
        try {
            $this->log->addInfo('STARTING PAYPAL PAYMENT REQUEST');
            $this->log->addInfo("Debug mode [$debug]");

            $response = [];
            $json_data = json_decode($json, true);
            $this->log->addInfo('payment json data', $json_data);

            /*
            if($_SERVER['REMOTE_ADDR'] == '89.100.85.124') {
                echo $username . '<br/>';
                echo $password . "\n";
                echo $email;
                throw new Exception('test');
            }
            */

            if ($this->login_verification($username, $password, $email)) {
                $response['payment_id_ref_number'] = $json_data['payment_id_ref_number'];
                $response['status'] = '';

                $data = $json_data;
                $data['customer_id'] = $customer_id;

                $this->log->addInfo('Passing data to addPaypalPayments', $data);
                $status = $this->addPaypalPayments($data, $debug);
                if ($status == 'redirect') {
                    $response = ['success' => 1, 'msg' => 'Successfully topped up'];
                //$response = $this->paypointPaymentsTurnMeterOnOff($customer_id);
                } else {
                    $response['status'] = $status;
                }

                return $response;
            } else {
                $response['flag_message'] = 1;
                $response['error'] = 'Customer Details Do Not Match';
                $this->log->addInfo('Customer Details Do Not Match: '.$customer_id.' | '.$email);

                return $response;
            }
        } catch (Exception $e) {
            $response['flag_message'] = 1;
            $response['error'] = 'addPaypalPaymentRequest() Code error occured: '.$e->getMessage();
            $this->log->addInfo('addPaypalPaymentRequest() Code error occured: '.$e->getMessage());

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function paypointPaymentsTurnMeterOnOff($customer_id)
    {
        $customer = Customer::find($customer_id);

        try {
            if (! $customer) {
                throw new Exception("Customer $customer_id does not exist!");
            }
            $customer->sendOpenCommand();
            $success['success'] = 1;
            $success['msg'] = 'Successfully sent meter command';

            return $success;
        } catch (Exception $e) {
            $error['success'] = 0;
            $error['msg'] = 'Database RTU entry not made: '.$e->getMessage();

            return $error;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function customerGraphData($customer_id, $email, $username, $password, $date_from, $date_to)
    {
        $response = [
            'dates' => '',
            'error' => '',
        ];

        if ($this->login_verification($username, $password, $email)) {
            $dates = [];

            /* VALIDATE DATES */
            $dateFormatRegex = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';

            $dateFromValidation = date_parse($date_from);
            if ($dateFromValidation['error_count'] > 0 || ! preg_match($dateFormatRegex, $date_from)) {
                $response['error'] = 'From date is not a valid date. The date should be in format YYYY-MM-DD';

                return $response;
            }
            $dateToValidation = date_parse($date_to);
            if ($dateToValidation['error_count'] > 0 || ! preg_match($dateFormatRegex, $date_to)) {
                $response['error'] = 'To date is not a valid date. The date should be in format YYYY-MM-DD';

                return $response;
            }
            /* VALIDATE DATES */

            //get the dates between the from_date and to_date
            $datesArray = $this->createDateRangeArray($date_from, $date_to);

            $customerInfo = Customer::find($customer_id);
            $customerSchemeNumber = $customerInfo ? $customerInfo->scheme_number : null;
            foreach ($datesArray as $key => $date) {
                $topup_amount = 0;
                $number_of_topups = 0;

                //get the information about that date from the district_heating_usage table (if exists)
                $dhu = DistrictHeatingUsage::where('customer_id', '=', $customer_id)
                            ->select('date', 'cost_of_day', 'start_day_reading', 'end_day_reading', 'total_usage', 'standing_charge', 'unit_charge', 'arrears_repayment')
                            ->where('date', '=', $date)
                            ->first();

                //get the temperature from the schemes table
                $temperature = '';
                if (! is_null($customerSchemeNumber)) {
                    $schemeCounty = $customerInfo->scheme->county;
                    $weatherRes = Weather::where('county', '=', $schemeCounty)
                                    ->whereRaw("date(date_time) = '$date'")
                                    ->first();
                    if ($weatherRes) {
                        $temperature = $weatherRes->currentTemperature;
                    }
                }

                //get the information about that date from the payments_storage table (if exists)
                $ps = PaymentStorage::where('customer_id', '=', $customer_id)
                        ->select('ref_number', 'customer_id', 'scheme_number', 'barcode', 'time_date', 'currency_code', 'amount', 'transaction_fee', 'acceptor_name_location_')
                        ->whereRaw("date(time_date) = '$date'")
                        ->distinct()
                        ->get();

                if ($ps->count()) {
                    $number_of_topups = $ps->count();
                    foreach ($ps as $psObj) {
                        $topup_amount += $psObj->amount;
                    }
                }

                if ($dhu || $ps->count() > 0) {
                    $dateInfo = [
                        'date' 				=> $date,
                        'topup_amount' 		=> $topup_amount,
                        'number_of_topups' 	=> $number_of_topups,
                        'credit_used' 		=> $dhu ? $dhu->cost_of_day : 0,
                        'daily_charge' 		=> $dhu ? $dhu->standing_charge : 0,
                        'unit_usage' 		=> $dhu ? $dhu->total_usage : 0,
                        'unit_charge' 		=> $dhu ? $dhu->unit_charge : 0,
                        'arrears_repayment' => $dhu ? $dhu->arrears_repayment : 0,
                        'temperature'		=> $temperature,
                    ];

                    $dates[] = $dateInfo;
                }
            }

            $response['dates'] = $dates;

            return $response;
        } else {
            $response['error'] = 'Customer Details Do Not Match';

            return $response;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getMeterRechargeStatusRequest($customer_id, $email, $username, $password, $rsCode)
    {
        $response = [
            'status' => '',
            'associated_with_customer' => '',
            'flag_message' => 0,
            'error' => '',
        ];

        if (! $this->login_verification($username, $password, $email)) {
            $response['flag_message'] = 1;
            $response['error'] = 'Customer Details Do Not Match';

            return $response;
        }

        if (! $meter = PermanentMeterData::withRSCode($rsCode)->first()) {
            $response['flag_message'] = 1;
            $response['error'] = 'Meter with RS code '.$rsCode.' does not exist';

            return $response;
        }

        $response['status'] = $meter->rechargeInProgress() ? 'recharging' : 'available';

        $dhm = $meter->districtHeatingMeters->first();
        if ($meter->rechargeInProgress() && $dhm) {
            $customer = Customer::where('ev_meter_ID', $dhm->meter_ID)->first();
            $response['associated_with_customer'] = $customer->id;
        }

        return $response;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function check_password($username, $password, $email)
    {
        $data['password'] = $password;

        //->where('email_address', '=', $email)
        $customer_info = Customer::where('username', '=', $username)
                        ->where('password', '=', '')
                        ->where('deleted_at', null)
                        ->get();

        if ($customer_info->count()) {
            $id = $customer_info[0]->id;
            Customer::where('id', '=', $id)->update($data);

            return true;
        }

        $customer_info_rc = CustomerRC::where('username', '=', $username)
                        //	->where('email_address', '=', $email)
                            ->where('password', '=', '')
                            ->get();

        if ($customer_info_rc->count()) {
            $id = $customer_info_rc[0]->id;
            CustomerRC::where('id', '=', $id)->update($data);

            return true;
        }

        return false;
    }

    // ----------------------------------------------------------------------------------------------------------
    public function login_verification($username, $password, $email)
    {

        //->where('email_address', '=', Crypt::encrypt($email))
        $customer_info = Customer::where('username', '=', $username)
                        ->where('password', '=', $password)
                        ->get();

        if ($customer_info->count()) {

            // Make inputted email and actual email lowercase for comparison
            $customer_info->first()->email_address = strtolower(trim($customer_info->first()->email_address));
            $email = strtolower(trim($email));

            if ($customer_info->first()->email_address != $email) {
                return false;
            }
        }

        if (! $customer_info->count()) {
            $customer_rc_info = CustomerRC::where('username', '=', $username)
                                ->where('password', '=', $password)
                                ->get();
            if (! $customer_rc_info->count()) {
                return false;
            } else {
                $this->customer_type = 'rc';

                return true;
            }
        }

        return true;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_customerid_scheme_number_service_type($username, $password, $email)
    {
        $data = [];

        if ($this->customer_type == 'rc') {
            $customer_info = CustomerRC::where('username', '=', $username)
                            ->where('email_address', '=', $email)
                            ->where('password', '=', $password)
                            ->get()
                            ->first();
        } else {
            //->where('email_address', '=', $email)
            $customer_info = Customer::where('username', '=', $username)
                            ->where('password', '=', $password)
                            ->where('deleted_at', null)
                            ->get()
                            ->first();
        }

        $data['scheme_number'] = $customer_info->scheme_number;
        $data['id'] = $customer_info->id;

        return $data;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_permanent_meter_id($customer_id)
    {
        $permanent_meter_id = '';
        if ($this->customer_type == 'rc') {
            $permanent_meter_id = CustomerRC::where('customers_rc.id', '=', $customer_id)
                                  ->pluck('permanent_meter_id');
        } else {
            $permanent_meter_id = DB::table('customers')
                                  ->join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
                                  ->where('customers.id', '=', $customer_id)
                                  ->pluck('permanent_meter_ID');
        }

        return $permanent_meter_id;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_service_type($scheme_number)
    {
        $service_type = Scheme::where('scheme_number', '=', $scheme_number)->pluck('service_type');

        return $service_type;
    }

    public function usageFromBillingEngineLogs($customer, $date)
    {
        $parts = explode('-', $date);
        $year = $parts[0];
        $month = $parts[1];
        $day = $parts[2];
        $date = $year.'_'.$month.'_'.$day;

        $filename = "/opt/prepago_engine/prepago_engine/logs/$year/$month/billing_engine/$date.txt";
        $entry = (object) [
            'billed' => 0,
            'usage' => 0,
            'date' => '',
        ];

        $entry->date = $year.'-'.$month.'-'.$day;

        if (! file_exists($filename)) {
            return $entry;
        }

        foreach (file($filename) as $line) {
            if (strpos(strtolower($line), 'error')) {
                $line = "<font color='red'>$line</font>";
            }

            $c1 = 'Customer '.$customer.' Old Balance';
            $c2 = 'Customer '.$customer.' billed';

            if (strpos($line, $c1) === false) {
                if (strpos($line, $c2) === false || strpos($line, 'daily tariff') === false) {
                    continue;
                } else {
                    $parts_1 = explode(' ', $line);
                    $billed = $parts_1[3];
                    $entry->billed += $billed;
                    $entry->billed += floatval($parts_1[7]);
                }

                //continue;
            } else {
                $parts = explode(' ', $line);
                $old_balance = floatval($parts[7]);
                $new_balance = floatval($parts[11]);
                $the_usage = floatval($parts[14]);
                $billed = $old_balance - $new_balance;
                $entry->billed += $billed;
                $entry->usage += $the_usage;
            }
        }

        $today = "$year-$month-$day";
        //$today = "2013-09-25";

        $smss = SMSMessage::where('date_time', 'like', "%$today%")
        ->where('customer_id', $customer)
        ->orderBy('date_time', 'desc')->get();

        $additional_sms_cost = 0;

        foreach ($smss as $sms) {
            if (! $sms->paid) {
                continue;
            }
            $cost = $sms->charge;
            $additional_sms_cost += $cost;
        }

        $entry->billed += $additional_sms_cost;

        //echo $entry->billed;

        return $entry;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_heat_port($permanent_meter_id)
    {
        $heat_port = PermanentMeterData::where('ID', '=', $permanent_meter_id)->pluck('heat_port');

        return $heat_port;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function check_phone_registration($phone_id, $customer_id)
    {
        if (RegisteredPhonesWithApps::where('customer_ID', '=', $customer_id)->count()) {
            $registered_phone = RegisteredPhonesWithApps::where('customer_ID', '=', $customer_id)->first();
            if ($registered_phone) {
                if (strcmp($registered_phone->phone_UID, $phone_id) !== 0) {
                    try {
                        $registered_phone->phone_UID = $phone_id;
                        $registered_phone->save();
                    } catch (Exception $e) {
                    }
                }
            }

            return true;
        } else {
            return false;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function phone_entry($phone_id, $customer_id, $date, $scheme_number, $model)
    {
        $data['phone_UID'] = $phone_id;
        if ($this->customer_type == 'rc') {
            $data['customer_ID'] = 0;
            $data['customers_rc_id'] = $customer_id;
        } else {
            $data['customer_ID'] = $customer_id;
            $data['customers_rc_id'] = 0;
        }
        $data['date_added'] = $date;
        $data['scheme_number'] = $scheme_number;
        $data['paid'] = 0;
        $data['make_model'] = $model;

        $charge = Scheme::where('scheme_number', '=', $scheme_number)->pluck('prepago_registered_apps_charge');
        $data['charge'] = $charge;

        RegisteredPhonesWithApps::create($data);
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_user_data($customer_id)
    {
        $data = [];

        if ($this->customer_type == 'rc') {
            $customer_info = DB::table('customers_rc')
                            ->join('permanent_meter_data', 'customers_rc.permanent_meter_id', '=', 'permanent_meter_data.ID')
                            ->join('district_heating_meters', 'district_heating_meters.permanent_meter_id', '=', 'permanent_meter_data.ID')
                            ->select('customers_rc.username', 'customers_rc.scheme_number', 'permanent_meter_data.shut_off', 'district_heating_meters.meter_ID')
                            ->where('customers_rc.id', '=', $customer_id)
                            ->where('district_heating_meters.meter_ID', '>', '0')
                            ->get();

            if ($customer_info) {
                $customer_info = $customer_info[0];

                $data['username'] = $customer_info->username;
                $data['scheme_number'] = $customer_info->scheme_number;
                $data['shut_off'] = $customer_info->shut_off;
                $data['meter_ID'] = $customer_info->meter_ID;
            }
        } else {
            $customer_info = DB::table('customers')
                             ->join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
                             ->join('schemes', 'customers.scheme_number', '=', 'schemes.scheme_number')
                             ->select('customers.id as customer_id', 'customers.*', 'district_heating_meters.*', 'schemes.*')
                             ->where('customers.id', '=', $customer_id)
                             ->get();
            if ($customer_info) {
                $customer_info = $customer_info[0];

                $data['username'] = $customer_info->username;
                $data['scheme_number'] = $customer_info->scheme_number;
                $data['balance'] = $customer_info->balance;
                $data['unit_abbreviation'] = $customer_info->unit_abbreviation;
                $data['shut_off'] = $customer_info->shut_off;
                $data['meter_ID'] = $customer_info->meter_ID;
                $data['ev_meter_ID'] = $customer_info->ev_meter_ID;
                $data['barcode'] = $customer_info->barcode;
                $data['IOU_available'] = $customer_info->IOU_available;
                $data['IOU_extra_available'] = $customer_info->IOU_extra_available;
                $data['IOU_used'] = $customer_info->IOU_used;
                $data['IOU_extra_used'] = $customer_info->IOU_extra_used;
                $data['latest_reading'] = $customer_info->latest_reading;
                $data['ev_latest_reading'] = Customer::find($customer_info->customer_id)->EVDistrictHeatingMeter
                                                        ? Customer::find($customer_info->customer_id)->EVDistrictHeatingMeter->latest_reading
                                                        : 0;
                $data['used_yesterday'] = $customer_info->used_yesterday;
                $data['scheduled_to_shut_off'] = $customer_info->scheduled_to_shut_off;
                $data['shut_off_device_status'] = $customer_info->shut_off_device_status;
                $data['scu_type'] = $customer_info->scu_type;

                // the following code is added to make sure the "latest reading" and "used yesterday" fields values are
                // the same as on the customer view details page
                $yesterday = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));
                $districtHeatingUsageForYesterday = DistrictHeatingUsage::where('customer_id', '=', $customer_id)
                                                                        ->where('date', '<=', $yesterday)
                                                                        ->where('start_day_reading', '>', 0)
                                                                        ->where('end_day_reading', '>', 0)
                                                                        ->orderBy('date', 'desc')
                                                                        ->first();

                $today = new DateTime(date('Y-m-d'));
                $yesterday_d = new DateTime($today->format('Y-m-d').' - 1 day');
                $yesterday = $yesterday_d->format('Y-m-d');

                // invalid
                //$data['used_yesterday'] = $this->usageFromBillingEngineLogs($customer_id, $yesterday)->billed;

                /*
                if ($districtHeatingUsageForYesterday) {
                    $data['latest_reading'] = $districtHeatingUsageForYesterday->end_day_reading;

                    $costOfDay = $districtHeatingUsageForYesterday->cost_of_day;
                    $tariff = Tariff::where('scheme_number', $customer_info->scheme_number)->first()->tariff_1;
                    $unitCharge = $districtHeatingUsageForYesterday->total_usage * $tariff;
                    if($districtHeatingUsageForYesterday->unit_charge <= 0 || $costOfDay <= 0) {
                        $costOfDay = $unitCharge + $districtHeatingUsageForYesterday->standing_charge;
                    }

                    //$data['used_yesterday'] = $costOfDay;
                }*/
            }
        }

        return $data;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_IOU_text($scheme_number)
    {
        $scheme_data = Scheme::where('scheme_number', '=', $scheme_number)->get()->first();

        $data['IOU_text'] = utf8_encode($scheme_data->IOU_text);
        $data['IOU_extra_text'] = utf8_encode($scheme_data->IOU_extra_text);
        $data['IOU_message'] = utf8_encode($scheme_data->IOU_message);
        $data['IOU_extra_message'] = utf8_encode($scheme_data->IOU_extra_message);
        $data['IOU_amount'] = $scheme_data->IOU_amount;
        $data['currency_sign'] = utf8_encode($scheme_data->currency_sign);
        $data['sms_password'] = $scheme_data->sms_password;

        return $data;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function set_IOU($customer_id, $scheme_no, $balance)
    {
        if ($this->customer_type == 'rc') {
            return 0;
        } else {
            $charge = $this->get_IOU_charge($scheme_no);
            $user_data = Customer::where('id', '=', $customer_id)->get()->first();

            $iou_available = $user_data->IOU_available;

            if ($iou_available == 1) {
                if ($balance > (0 - $charge['cmp'])) {
                    $balance = $balance - $charge['red'];
                    $data = ['IOU_used' => 1, 'IOU_available' => 0, 'balance' => $balance];
                    Customer::where('id', '=', $customer_id)->update($data);

                    return 1;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_IOU_charge($scheme_number)
    {
        $data = [];
        $result = [];

        $data = Scheme::where('scheme_number', '=', $scheme_number)->get()->first();

        $result['cmp'] = $data->IOU_amount;
        $result['red'] = $data->IOU_charge;

        return $result;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function set_IOU_extra($customer_id, $scheme_no, $balance)
    {
        if ($this->customer_type == 'rc') {
            return 0;
        } else {
            $charge = $this->get_IOU_Extra_charge($scheme_no);
            $user_data = Customer::where('id', '=', $customer_id)->get()->first();

            $iou_extra_available = $user_data->IOU_extra_available;
            if ($iou_extra_available == 1) {
                if ($balance > (0 - $charge['cmp'])) {
                    $balance = $balance - $charge['red'];
                    $data = ['IOU_extra_used' => 1, 'IOU_extra_available' => 0, 'balance' => $balance];
                    Customer::where('id', '=', $customer_id)->update($data);

                    return 1;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_IOU_Extra_charge($scheme_number)
    {
        $data = [];
        $result = [];

        $data = Scheme::where('scheme_number', '=', $scheme_number)->get()->first();

        $result['cmp'] = $data->IOU_extra_amount;
        $result['red'] = $data->IOU_extra_charge;

        return $result;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function insert_iou_entry($customer_id, $scheme_number)
    {
        $data = [];
        $result = [];

        $data = Scheme::where('scheme_number', '=', $scheme_number)->get()->first();
        $ioucharge = $data->IOU_charge;
        $date = date('Y-m-d H:i:s');
        $iou_data = ['customer_id' => $customer_id,
                     'scheme_number' => $scheme_number,
                     'time_date' => $date,
                     'charge' => $ioucharge,
                     'paid' => '0', ];
        IOUStorage::create($iou_data);
    }

    // ----------------------------------------------------------------------------------------------------------
    private function insert_iou_extra_entry($customer_id, $scheme_number)
    {
        $data = [];
        $result = [];

        $data = Scheme::where('scheme_number', '=', $scheme_number)->get()->first();
        $iouextracharge = $data->IOU_extra_charge;
        $date = date('Y-m-d H:i:s');
        $iou_data = ['customer_id' => $customer_id,
                     'scheme_number' => $scheme_number,
                     'date_time' => $date,
                     'charge' => $iouextracharge,
                     'paid' => '0', ];
        IOUExtraStorage::create($iou_data);
    }

    // ----------------------------------------------------------------------------------------------------------
    private function shuttoff1($customer_id, $meter_id)
    {
        $data1 = ['shut_off' => '0', 'shut_off_command_sent'=>'0'];
        Customer::where('id', '=', $customer_id)->update($data1);

        $data2 = ['shut_off_device_status' => '0'];
        DistrictHeatingMeter::where('meter_ID', '=', $meter_id)->update($data2);
    }

    // ----------------------------------------------------------------------------------------------------------
    private function add_rtu_entry($customer_id)
    {
        if ($this->customer_type == 'rc') {
            $customer_info = DB::table('customers_rc')
                             ->join('permanent_meter_data', 'customers_rc.permanent_meter_id', '=', 'permanent_meter_data.ID')
                             ->join('district_heating_meters', 'district_heating_meters.permanent_meter_id', '=', 'permanent_meter_data.ID')
                             ->where('customers_rc.id', '=', $customer_id)
                             ->where('district_heating_meters.meter_ID', '>', '0')
                             ->get();
        } else {
            $customer_info = DB::table('customers')
                             ->join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
                             ->where('customers.id', '=', $customer_id)
                             ->get();
        }

        if ($customer_info) {
            $customer_info = $customer_info[0];
            $data = [
                'customer_ID' => $customer_id,
                'meter_id' => $customer_info->meter_ID,
                'turn_service_on' => 1,
                'shut_off_device_contact_number' => $customer_info->shut_off_device_contact_number,
                'permanent_meter_id' => $customer_info->permanent_meter_ID,
                'scheme_number' => $customer_info->scheme_number,
                'port' => $customer_info->port,
            ];
            RTUCommandQue::create($data);
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_recent_top_ups($customer_id)
    {
        $data = [];
        $paymentsCollection = new \Illuminate\Support\Collection();

        if ($this->customer_type != 'rc') {
            $p1 = PaymentStorage::where('customer_id', $customer_id)->distinct()
                    ->orderBy('time_date', 'desc')->get(['ref_number', 'customer_id', 'scheme_number', 'barcode', 'time_date', 'currency_code', 'amount', 'transaction_fee', 'acceptor_name_location_']);
            foreach ($p1 as $p1_entry) {
                $paymentsCollection->push($p1_entry);
            }

            $p2 = TemporaryPayments::where('customer_id', $customer_id)->distinct()
                    ->orderBy('time_date', 'desc')->get(['ref_number', 'customer_id', 'scheme_number', 'barcode', 'time_date', 'currency_code', 'amount', 'transaction_fee', 'acceptor_name_location']);
            foreach ($p2 as $p2_entry) {
                $p2_entry->acceptor_name_location_ = $p2_entry->acceptor_name_location;
                unset($p2_entry->acceptor_name_location);
                $paymentsCollection->push($p2_entry);
            }

            restart:
            foreach ($paymentsCollection as $key=>$pc) {
                if ($key == 0) {
                    continue;
                }

                if ($paymentsCollection[$key]->time_date >= $paymentsCollection[$key - 1]->time_date) {
                    $temp = $paymentsCollection[$key];
                    $paymentsCollection[$key] = $paymentsCollection[$key - 1];
                    $paymentsCollection[$key - 1] = $temp;
                    goto restart;
                }
            }

            $data = $paymentsCollection->toArray();
        }

        return $data;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_barcode_scheme_number($username, $password, $email)
    {
        $data = [];

        if ($this->customer_type == 'rc') {
            return $data;
        } else {

           //->where('email_address', '=', $email)
            $customer_info = Customer::where('username', '=', $username)
                             ->where('password', '=', $password)
                             ->where('deleted_at', null)
                             ->get()
                             ->first();

            if ($customer_info) {
                $data['scheme_number'] = $customer_info->scheme_number;
                $data['barcode'] = $customer_info->barcode;
            }

            return $data;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_FAQ($scheme_number)
    {
        $data = [];

        if ($this->customer_type == 'rc') {
            return $data;
        } else {
            $data = Scheme::where('scheme_number', '=', $scheme_number)->get()->first();

            return $data->FAQ;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_payment_locations($lat, $lon)
    {
        $data = [];

        if ($this->customer_type == 'rc') {
            return $data;
        } else {
            $data = PaymentLocations::select('payment_locations.opening_hours', 'payment_locations.latitude', 'payment_locations.longitude', 'payment_locations.fascia_name', DB::raw('3956 * 2 * ASIN(SQRT( POWER(SIN(('.$lat.' - latitude) * pi()/180 / 2), 2) + COS('.$lat.' * pi()/180) * COS(latitude * pi()/180) * POWER(SIN(('.$lon.' - longitude) * pi()/180 / 2), 2) )) as distance'))
                    ->orderBy('distance', 'asc')
                    ->groupBy('id')
                    ->take(500)
                    ->get();

            return $data;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_barcode_exists($username)
    {
        if ($this->customer_type == 'rc') {
            return false;
        } else {
            $data = [];
            $data = Customer::where('username', '=', $username)
                    ->where('status', '=', 1)
                     ->where('deleted_at', null)
                    ->get();
            if ($data->count() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_barcode($username)
    {
        $customer_info = [];

        if ($this->customer_type == 'rc') {
            return $customer_info;
        } else {
            $data = [];
            $customer_info = Customer::where('username', '=', $username)
                             ->where('status', '=', 1)
                             ->where('deleted_at', null)
                             ->get()
                             ->first();
            if ($customer_info->count() > 0) {
                $data['barcode'] = $customer_info->barcode;
            } else {
                $data['barcode'] = '0';
            }

            return $data;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function get_rc_info($permanent_meter_id)
    {
        $data = [];
        if ($permanent_meter_id) {
            $data = DB::table('permanent_meter_data')
                    ->leftjoin('remote_control_status', 'permanent_meter_data.ID', '=', 'remote_control_status.permanent_meter_id')
                    ->select('permanent_meter_data.*', 'remote_control_status.*', DB::raw('ROUND( TIME_TO_SEC( TIMEDIFF( heating_boost_end_datetime, "'.date('Y-m-d H:i').'" ) ) / 60, 0 ) as boost_time_in_mins'))
                    ->where('permanent_meter_data.ID', '=', $permanent_meter_id)
                    ->take(1)
                    ->get();
            if ($data) {
                $data = (array) $data[0];

                $data_rc_times = PermanentMeterData::find($permanent_meter_id)->rctimes->toArray();
                $data['Daily Timer'] = $data_rc_times;
            }
        }

        return $data;
    }

    // ----------------------------------------------------------------------------------------------------------
    private function saveRCInfo($permanent_meter_id, $json_data)
    {

        /*$data['away_mode_end_datetime'] = "2015-01-14 16:11:00";
        dd(RemoteControlStatus::where('permanent_meter_id', '=', $permanent_meter_id)->get()->first());
        //dd(RemoteControlStatus::where('permanent_meter_id', '=', $permanent_meter_id)->update($data));
        dd('aaaaaa');*/
        if ($permanent_meter_id && $json_data) {
            $trans = DB::transaction(function () use ($permanent_meter_id, $json_data) {
                try {

                    //if query 1 or query 2
                    if (isset($json_data['away_mode']) || isset($json_data['boost'])) {

                        //check if there is already information in the "remote_control_status" table about that permanent_meter_id
                        $remote_control_status_data = RemoteControlStatus::where('permanent_meter_id', '=', $permanent_meter_id)->get()->first();

                        $rcs_action = ($remote_control_status_data && $remote_control_status_data->count()) ? 'update' : 'create';

                        /* Populate the "remote_control_status" table START */
                        $remote_control_status['permanent_meter_id'] = $permanent_meter_id;
                        $remote_control_status['heating_on'] = '';
                        $remote_control_status['heating_turned_on_at_datetime'] = $remote_control_status_data['heating_turned_on_at_datetime'] ? $remote_control_status_data['heating_turned_on_at_datetime'] : null;
                        $remote_control_status['heating_to_be_turned_off_at_datetime'] = $remote_control_status_data['heating_to_be_turned_off_at_datetime'] ? $remote_control_status_data['heating_to_be_turned_off_at_datetime'] : null;

                        if (isset($json_data['away_mode'])) {
                            $remote_control_status['away_mode_on'] = $json_data['away_mode']['away_mode_on'];
                            $remote_control_status['away_mode_end_datetime'] = $json_data['away_mode']['date'].' '.$json_data['away_mode']['time'];
                            $remote_control_status['away_mode_permanent'] = $json_data['away_mode']['permenantly'];
                            $remote_control_status['away_mode_relay_status'] = '';
                            $remote_control_status['away_mode_cancelled'] = '';
                            if ($rcs_action == 'update') {
                                //if ($remote_control_status_data['away_mode_on'] == 1 && !($json_data['away_mode']['away_mode_on']) && strtotime($remote_control_status['away_mode_end_datetime']) > time()) {
                                if ($remote_control_status_data['away_mode_on'] == 1 && ! $json_data['away_mode']['away_mode_on']) {
                                    //$remote_control_status['away_mode_on'] = $remote_control_status_data['away_mode_on'];
                                    $remote_control_status['away_mode_on'] = $json_data['away_mode']['away_mode_on'];
                                    $remote_control_status['away_mode_end_datetime'] = $remote_control_status_data['away_mode_end_datetime'];
                                    $remote_control_status['away_mode_cancelled'] = 1;
                                }
                            }

                            //check if a change was made to the DB and set the user_change_notification field to 1
                            if (
                                $remote_control_status['away_mode_on'] != $remote_control_status_data['away_mode_on'] ||
                                $remote_control_status['away_mode_end_datetime'].':00' != $remote_control_status_data['away_mode_end_datetime'] ||
                                $remote_control_status['away_mode_permanent'] != $remote_control_status_data['away_mode_permanent'] ||
                                $remote_control_status['away_mode_cancelled'] != $remote_control_status_data['away_mode_cancelled']
                                ) {
                                $remote_control_status['user_change_notification'] = 1;
                            }
                        }

                        if (isset($json_data['boost'])) {
                            $remote_control_status['heating_boost_on'] = $json_data['boost']['boost_on'];
                            //calculate heating_boost_end_datetime by adding the left minutes to the current timestamp
                            $remote_control_status['heating_boost_end_datetime'] = date('Y-m-d H:i:00', (time() + (60 * $json_data['boost']['boost_time_in_mins'])));
                            $remote_control_status['heating_boost_cancelled'] = '';

                            //if there is a record in the remote_control_status table
                            if ($rcs_action == 'update') {
                                //if ($remote_control_status_data['heating_boost_on'] == 1 && !($json_data['boost']['boost_on']) && strtotime($remote_control_status['heating_boost_end_datetime']) > time()) {
                                if ($remote_control_status_data['heating_boost_on'] == 1 && ! ($json_data['boost']['boost_on'])) {
                                    //$remote_control_status['heating_boost_on'] = $remote_control_status_data['heating_boost_on'];
                                    $remote_control_status['heating_boost_on'] = $json_data['boost']['boost_on'];
                                    $remote_control_status['heating_boost_end_datetime'] = $remote_control_status_data['heating_boost_end_datetime'];
                                    $remote_control_status['heating_boost_cancelled'] = 1;
                                }
                            }

                            //check if a change was made to the DB and set the user_change_notification field to 1
                            if (
                                $remote_control_status['heating_boost_on'] != $remote_control_status_data['heating_boost_on'] ||
                                $remote_control_status['heating_boost_end_datetime'] != $remote_control_status_data['heating_boost_end_datetime'] ||
                                $remote_control_status['heating_boost_cancelled'] != $remote_control_status_data['heating_boost_cancelled']
                                ) {
                                $remote_control_status['user_change_notification'] = 1;
                            }
                        }

                        if ($rcs_action == 'update') {
                            if (! RemoteControlStatus::find($remote_control_status_data->permanent_meter_id)->update($remote_control_status)) {
                                throw new Exception('The table fields cannot be populated');
                            }
                        } else {
                            if (! RemoteControlStatus::create($remote_control_status)) {
                                throw new Exception('The table fields cannot be populated');
                            }
                        }
                        /* Populate the "remote_control_status" table END */
                    }

                    if (isset($json_data['Daily Timer'])) {
                        $db_data_changed = false;

                        /* Populate the "remote_control_times" table START */
                        $json_data_days = [];
                        foreach ($json_data['Daily Timer'] as $key => $val) {

                            //check if there is already information in the "remote_control_times" table about that permanent_meter_id
                            $remote_control_times_data = RemoteControlTimes::where('permanent_meter_id', '=', $permanent_meter_id)
                                                        ->where('day', '=', $val['Day'])
                                                        ->get();
                            $remote_control_times_data_array = $remote_control_times_data->toArray()[0];
                            unset($remote_control_times_data_array['id']);

                            $remote_control_times_data_array['active'] = $remote_control_times_data_array['active'] == 0 || is_null($remote_control_times_data_array['active']) ? false : true;
                            for ($j = 1; $j <= 10; $j++) {
                                if (! is_null($remote_control_times_data_array['t'.$j.'_start']) &&
                                    ! is_null($remote_control_times_data_array['t'.$j.'_end'])) {
                                    $remote_control_times_data_array['t'.$j.'_active'] = ! $remote_control_times_data_array['t'.$j.'_active'] ? false : true;
                                }
                            }

                            $json_data_days[] = $val['Day'];
                            $remote_control_times['permanent_meter_id'] = $permanent_meter_id;
                            $remote_control_times['day'] = $val['Day'];
                            $remote_control_times['active'] = $val['Active'];
                            if (count($val['Times'])) {
                                foreach ($val['Times'] as $key1 => $val1) {
                                    $remote_control_times['t'.($key1 + 1).'_start'] = $val1['On'];
                                    $remote_control_times['t'.($key1 + 1).'_end'] = $val1['Off'];
                                    $remote_control_times['t'.($key1 + 1).'_active'] = $val1['Active'];
                                }
                                //set the other on and off times to null
                                if ($key + 1 < 10) {
                                    for ($i = $key1 + 2; $i <= 10; $i++) {
                                        $remote_control_times['t'.$i.'_start'] = null;
                                        $remote_control_times['t'.$i.'_end'] = null;
                                        $remote_control_times['t'.$i.'_active'] = 0;
                                    }
                                }
                            } else {
                                for ($i = 1; $i <= 10; $i++) {
                                    $remote_control_times['t'.$i.'_start'] = null;
                                    $remote_control_times['t'.$i.'_end'] = null;
                                    $remote_control_times['t'.$i.'_active'] = 0;
                                }
                            }

                            $changes_made_to_db = array_diff_assoc($remote_control_times, $remote_control_times_data_array);
                            if (count($changes_made_to_db)) {
                                $db_data_changed = true;
                            }

                            if ($remote_control_times_data && $remote_control_times_data->count()) {
                                if (! RemoteControlTimes::find($remote_control_times_data[0]->id)->update($remote_control_times)) {
                                    throw new Exception('The table fields cannot be populated');
                                }
                            } else {
                                if (! RemoteControlTimes::create($remote_control_times)) {
                                    throw new Exception('The table fields cannot be populated');
                                }
                            }
                        }

                        //check if maybe some of the week days was not sent in the JSON and set its on/off times to NULL
                        if (count($json_data['Daily Timer']) < 7) {
                            $week_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $missing_days = array_diff($week_days, $json_data_days);

                            if ($missing_days) {
                                foreach ($missing_days as $missing_days_key => $missing_days_val) {
                                    $missing_days_data['permanent_meter_id'] = $permanent_meter_id;
                                    $missing_days_data['day'] = $missing_days_val;
                                    $missing_days_data['active'] = 0;
                                    for ($md = 1; $md <= 10; $md++) {
                                        $missing_days_data['t'.$md.'_start'] = null;
                                        $missing_days_data['t'.$md.'_end'] = null;
                                        $missing_days_data['t'.$md.'_active'] = 0;
                                    }

                                    //check if there is already information in the "remote_control_times" table about that permanent_meter_id and that day
                                    $missing_days_info = RemoteControlTimes::where('permanent_meter_id', '=', $permanent_meter_id)
                                                         ->where('day', '=', $missing_days_val)
                                                         ->get();

                                    //check whether some changes had been made to DB (if a day was not included in the URL)
                                    $missing_days_info_array = $missing_days_info->toArray()[0];
                                    unset($missing_days_info_array['id']);
                                    $changes_made_to_db = array_diff_assoc($missing_days_data, $missing_days_info_array);
                                    if (count($changes_made_to_db)) {
                                        $db_data_changed = true;
                                    }

                                    if ($missing_days_info->count()) {
                                        if (! RemoteControlTimes::find($missing_days_info[0]->id)->update($missing_days_data)) {
                                            throw new Exception('The table fields cannot be populated');
                                        }
                                    } else {
                                        if (! RemoteControlTimes::create($missing_days_data)) {
                                            throw new Exception('The table fields cannot be populated');
                                        }
                                    }
                                }
                            } // end IF $missing_days
                        }

                        if ($db_data_changed) {
                            if (! RemoteControlStatus::find($permanent_meter_id)->update(['user_change_notification' => 1])) {
                                throw new Exception('The table fields cannot be populated');
                            }
                        }
                        /* Populate the "remote_control_times" table END */
                    }
                } // end TRY
                catch (Exception $e) {
                    return ['error' => $e->getMessage()];
                }
            });

            if ($trans['error']) {
                //return ['error' => $trans['error']];
                return ['error' => 'Error saving the data in the database'];
            } else {
                return true;
            }
        } //end if permanent_meter_id && json_data
        else {
            return ['error' => 'No information about this user\'s meter found'];
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function saveRCInfoEmpty($permanent_meter_id)
    {
        if ($permanent_meter_id) {
            $trans = DB::transaction(function () use ($permanent_meter_id) {
                try {
                    /* Populate the "remote_control_status" table START */
                    $remote_control_status['permanent_meter_id'] = $permanent_meter_id;
                    $remote_control_status['heating_on'] = '0';
                    $remote_control_status['heating_turned_on_at_datetime'] = null;
                    $remote_control_status['heating_to_be_turned_off_at_datetime'] = null;
                    $remote_control_status['away_mode_on'] = '0';
                    $remote_control_status['away_mode_end_datetime'] = null;
                    $remote_control_status['away_mode_permanent'] = '0';
                    $remote_control_status['away_mode_relay_status'] = '0';
                    $remote_control_status['away_mode_cancelled'] = '0';
                    $remote_control_status['heating_boost_on'] = '0';
                    $remote_control_status['heating_boost_end_datetime'] = null;
                    $remote_control_status['heating_boost_cancelled'] = '0';
                    $remote_control_status['user_change_notification'] = '0';

                    if (! RemoteControlStatus::create($remote_control_status)) {
                        throw new Exception('The table fields cannot be populated');
                    }
                    /* Populate the "remote_control_status" table END */

                    /* Populate the "remote_control_times" table START */
                    $week_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    foreach ($week_days as $week_day_key => $week_day_val) {
                        $week_days_data['permanent_meter_id'] = $permanent_meter_id;
                        $week_days_data['day'] = $week_day_val;
                        $week_days_data['active'] = 0;
                        for ($md = 1; $md <= 10; $md++) {
                            $week_days_data['t'.$md.'_start'] = null;
                            $week_days_data['t'.$md.'_end'] = null;
                            $week_days_data['t'.$md.'_active'] = 0;
                        }
                        if (! RemoteControlTimes::create($week_days_data)) {
                            throw new Exception('The table fields cannot be populated');
                        }
                    }
                    /* Populate the "remote_control_times" table END */
                } catch (Exception $e) {
                    return ['error' => $e->getMessage()];
                }
            });

            if ($trans['error']) {
                return ['error' => $trans['error']];
            //return ['error' => 'Error saving the data in the database'];
            } else {
                return true;
            }
        } //end if permanent_meter_id
        else {
            return ['error' => 'No information about this user\'s meter found'];
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function addPaypalPayments($data, $debug = false)
    {
        $status = '';

        try {
            if (SystemSetting::get('disable_old_app') == '1') {
                throw new Exception(SystemSetting::get('disable_old_app_msg'));
            }

            //if customer is RC -> return unsuccessful status
            if ($this->customer_type == 'rc') {
                $status = 'unsuccessful';

                return $status;
            }

            //check if payment id exists in the DB
            if (PaymentStorage::where('ref_number', '=', $data['payment_id_ref_number'])->count()) {
                $status = 'duplicate entry';

                return $status;
            }

            //check if payment ID is valid -> post back to paypal to verify
            $token = $this->getPaypalAppToken();
            $this->log->addInfo('PP TOKEN', [$token]);

            //TMP CODE
            $tokenWithNewCredentials = $this->getPaypalAppToken(0, 1);
            $this->log->addInfo('PP TOKEN WITH NEW CREDENTIALS', [$tokenWithNewCredentials]);
            //TMP CODE

            //if (!$token) { -> return that check after 2 months
            if (! $token && ! $tokenWithNewCredentials) { //tmp condition
                $status = 'Could not obtain token';

                return $status;
            }

            $paymentDetails = [];
            $paymentDetails['total'] = (float) $data['amount'];
            $paymentDetails['currency_code'] = $data['currency_code'];

            //$verifyPaymentResult = $this->verifyPayment($token, $data['payment_id_ref_number'], $paymentDetails); // revert in 2 months
            $verifyPaymentResult = $this->verifyPayment($token, $data['payment_id_ref_number'], $paymentDetails, $tokenWithNewCredentials, $debug); //tmp code
            $this->log->addInfo('verifyPaymentResult', [$verifyPaymentResult]);
            if ($verifyPaymentResult === 'token_expired') {
                //get new token from Paypal and re-run the verify payment process
                $tokenNew = $this->getPaypalAppToken(1);
                $this->log->addInfo('PP TOKEN NEW', [$tokenNew]);

                //TMP CODE
                $tokenNewWithNewCredentials = $this->getPaypalAppToken(1, 1);
                $this->log->addInfo('PP TOKEN NEW WITH NEW CREDENTIALS', [$tokenNewWithNewCredentials]);
                //TMP CODE

                //if (!$tokenNew) { -> return that check after 2 months
                if (! $tokenNew && ! $tokenNewWithNewCredentials) { //tmp condition
                    $status = 'Could not obtain token';

                    return $status;
                }
                //$verifyPaymentResult1 = $this->verifyPayment($tokenNew, $data['payment_id_ref_number'], $paymentDetails);// revert in 2 months
                $verifyPaymentResult1 = $this->verifyPayment($tokenNew, $data['payment_id_ref_number'], $paymentDetails, $tokenWithNewCredentials);
                $this->log->addInfo('verifyPaymentResult1', [$verifyPaymentResult1]);
                if (! $verifyPaymentResult1 || $verifyPaymentResult1 === 'token_expired') {
                    $status = 'The payment ID could not be verified';

                    return $status;
                }
            }
            if (! $verifyPaymentResult) {
                $status = 'The payment ID could not be verified';

                return $status;
            }

            $this->log->addInfo('STARTING THE TRANSACTION');
            $trans = DB::transaction(function () use ($data) {
                try {
                    $customerID = (int) $data['customer_id'];

                    //get customer scheme number
                    $customerInfo = $this->get_user_data($customerID);
                    $this->log->addInfo('customerInfo', $customerInfo);

                    //Add the payment to the payments_storage table
                    $paymentData = [];
                    $paymentData['ref_number'] = $data['payment_id_ref_number'];
                    $paymentData['customer_id'] = $customerID;
                    $paymentData['scheme_number'] = $customerInfo['scheme_number'];
                    $paymentData['barcode'] = isset($customerInfo['barcode']) ? $customerInfo['barcode'] : ''; //rc customers don't have barcode
                    $paymentData['time_date'] = date('Y-m-d H:i:s');
                    $paymentData['currency_code'] = $data['currency_code'];
                    $paymentData['amount'] = (float) $data['amount'];
                    $paymentData['transaction_fee'] = 0.0;
                    $paymentData['acceptor_name_location_'] = 'paypal';
                    $paymentData['payment_received'] = 1;
                    $paymentData['settlement_date'] = date('Y-m-d');
                    $paymentData['merchant_type'] = 0;
                    $paymentData['POS_entry_mode'] = 0;
                    $this->log->addInfo('paymentData', $paymentData);

                    if (! PaymentStorage::create($paymentData)) {
                        $this->log->addInfo('PAYMENT STORAGE - The payment cannot be added');
                        throw new Exception('The payment cannot be added');
                    }

                    //Update the customers table
                    $customerData = [];
                    $customerData['last_top_up'] = date('Y-m-d H:i:s');
                    $customerData['IOU_available'] = 0;
                    $customerData['IOU_used'] = 0;
                    $customerData['IOU_extra_available'] = 0;
                    $customerData['IOU_extra_used'] = 0;
                    $customerData['admin_IOU_in_use'] = 0;
                    $this->log->addInfo('customerData', $customerData);

                    if (! Customer::find($customerID)->update($customerData)) {
                        $this->log->addInfo('CUSTOMER - The customer data cannot be updated');
                        throw new Exception('The customer data cannot be updated');
                    }

                    $customer = Customer::find($customerID);
                    if ($customer) {
                        $ps = PaymentStorage::where('ref_number', $data['payment_id_ref_number'])->first();
                        $customer->topup($ps);
                    }
                } catch (Exception $e) {
                    PaymentStorageError::log($customerID, $e->getMessage());

                    return ['error' => $e->getMessage()];
                }
            });

            if (isset($trans['error'])) {
                $status = 'unsuccessful';
                if (gettype($trans['error']) === 'string') {
                    $this->log->addInfo('TRANS RESULTS ERROR', [$trans['error']]);
                } else {
                    $this->log->addInfo('TRANS RESULTS ERROR', $trans['error']);
                }
            } elseif ($trans == 'redirect') {
                $status = 'redirect';
            } else {
                $status = 'success';
            }

            $this->log->addInfo('TRANS STATUS', [$status]);

            return $status;
        } catch (Exception $e) {
            $status = $e->getMessage();
            $this->log->addInfo('TRANS STATUS', [$status]);

            return $status;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    private function read_file($file)
    {
        if (! file_exists($file)) {
            return false;
        }
        if (function_exists('file_get_contents')) {
            return file_get_contents($file);
        }
        if (! $fp = @fopen($file, FOPEN_READ)) {
            return false;
        }

        flock($fp, LOCK_SH);

        $data = '';
        if (filesize($file) > 0) {
            $data = &fread($fp, filesize($file));
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return $data;
    }

    // ----------------------------------------------------------------------------------------------------------
    //remove $userNewCredentials in 2 months - it's just for the temp code
    private function getPaypalAppToken($forceToken = 0, $useNewCredentials = 0)
    {

        //if (!$forceToken && Session::has('ppToken') && Session::get('ppToken')) { -> revert after removing $useNewCredentials
        if (! $forceToken && ! $useNewCredentials && Session::has('ppToken') && Session::get('ppToken')) {
            return Session::get('ppToken');
        }
        //TMP CODE
        elseif (! $forceToken && $useNewCredentials && Session::has('ppTokenNewCredentials') && Session::get('ppTokenNewCredentials')) {
            return Session::get('ppTokenNewCredentials');
        }
        //TMP CODE
        else {
            $liveClientID = 'AWBKcBCt4HDGo18nUKMQnns6_ZLSELeFeiYvfcgmFjYF-XZUPNNkvnAK75HC';
            $liveSecret = 'EHbLZBD8pScI88G7J4BvXljixlmZmro22v_BQftfg6kiAOCFtzGL2PSxyOFZ';

            //TMP CODE
            if ($useNewCredentials) {
                $liveClientID = 'AdaLyhgyaCNHU1ePx0J-nKXF5sa4yjsvM5TJmUtinNlejuE6sU6Xcnbjx4HwL3MUo1Ji6xkchMYQ6V0E';
                $liveSecret = 'EIp5CMugq88c9FPxdUtrBz6V2ggbYiC2EUrvglBpZqduSZUqshikYoDIpKLRowRe7gbgACxGrGcoZKwP';
            }
            //TMP CODE

            $url = $this->ppSandbox ? 'https://api.sandbox.paypal.com/v1/oauth2/token' : 'https://api.paypal.com/v1/oauth2/token';
            $clientID = $this->ppSandbox ? 'AR6hbBARiHwRsCpjG9dX18ZtZxRi61BzBYqxvag7AO65891d_FTZVrbxYrgj' : $liveClientID;
            $secret = $this->ppSandbox ? 'EBcGthCogcanCdxap-K7FEkHY8WlN7ltdZhaZlefTs3o4nA8EtVZJkRfwEIk' : $liveSecret;
            $token = '';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $clientID.':'.$secret);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');

            $result = curl_exec($ch);

            if (! empty($result)) {
                $json = json_decode($result);
                $token = isset($json->access_token) ? $json->access_token : '';
                //$tokenExpire = isset($json->expires_in) ? $json->expires_in : ""; //in seconds
            }

            curl_close($ch);

            //TMP CODE
            if ($useNewCredentials) {
                Session::put('ppTokenNewCredentials', $token);
            } else {
                Session::put('ppToken', $token);
            }
            //TMP CODE

            //Session::put('ppToken', $token); - revert after deleting the TMP CODE

            return $token;
        }
    }

    // ----------------------------------------------------------------------------------------------------------
    //remove $token withNewCredentials after 2 months - it's only for the tmp code
    private function verifyPayment($token, $paymentID, $paymentDetails, $tokenWithNewCredentials, $debug = false)
    {
        if ($debug == 1) {
            $this->ppSandbox = true;
        }

        $url = $this->ppSandbox ? 'https://api.sandbox.paypal.com/v1/payments/payment/'.urlencode($paymentID) : 'https://api.paypal.com/v1/payments/payment/'.urlencode($paymentID);
        $this->log->addInfo('verifyPayment START', [$url]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token,
            'Accept: application/json',
            'Content-Type: application/json',
        ]);

        $result = curl_exec($ch);

        $curl1Res = ''; //tmp code - remove in 2 months
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 401) {
            $this->log->addInfo('THE ORIGINAL FUNCTIONALITY TOKEN EXPIRED');
            $curl1Res = 'token_expired'; //tmp code - remove in 2 months
            //return 'token_expired'; -> revert in 2 months
        }

        curl_close($ch);

        $returnResult = true;

        if ($curl1Res == '') { //tmp code line - remove
         //tmp code line - remove
            $transactionState = '';
            $transactionAmountTotal = '';
            $transactionAmountCurrency = '';
            $transactionStatus = '';

            $jsonResult = json_decode($result, true);
            $this->log->addInfo('verifyPayment RESULT', $jsonResult);

            $transactionState = isset($jsonResult['state']) ? $jsonResult['state'] : '';
            $transactionDetails = isset($jsonResult['transactions'][0]) ? $jsonResult['transactions'][0] : '';
            if ($transactionDetails) {
                $transactionAmountTotal = isset($transactionDetails['amount']['total']) ? $transactionDetails['amount']['total'] : '';
                $transactionAmountCurrency = isset($transactionDetails['amount']['currency']) ? $transactionDetails['amount']['currency'] : '';
                if (isset($transactionDetails['related_resources']) && $transactionDetails['related_resources']) {
                    $transactionStatus = isset($transactionDetails['related_resources'][0]['sale']['state']) ? $transactionDetails['related_resources'][0]['sale']['state'] : '';
                }
            }

            $this->log->addInfo('verifyPayment transactionState', [$transactionState]);
            if ($transactionState === 'approved') {
                if ($transactionAmountTotal != $paymentDetails['total'] || $transactionAmountCurrency != $paymentDetails['currency_code']) {
                    $returnResult = false;
                    //return false;
                }
                if (! $this->ppSandbox && $transactionStatus !== 'completed') {
                    $returnResult = false;
                    //return false;
                }
            } else {
                $returnResult = false;
                //return false;
            }
        } //tmp code line - remove

        //TMP CODE
        else {
            $returnResult = false;
        }
        //TMP CODE

        $this->log->addInfo('$returnResult', [$returnResult]);

        //TMP CODE
        if (! $returnResult) {
            $this->log->addInfo('$returnResult is FALSE, try to verify the payment using the new pp details');
            if ($tokenWithNewCredentials) {
                $this->log->addInfo('init curl with new credentials token');

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer '.$tokenWithNewCredentials,
                    'Accept: application/json',
                    'Content-Type: application/json',
                ]);

                $result = curl_exec($ch);

                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 401) {
                    return 'token_expired';
                }

                //var_dump($result);

                curl_close($ch);

                $transactionState = '';
                $transactionAmountTotal = '';
                $transactionAmountCurrency = '';
                $transactionStatus = '';

                $jsonResult = json_decode($result, true);
                $this->log->addInfo('verifyPayment RESULT (NEW CREDENTIALS TOKEN)', $jsonResult);

                $transactionState = isset($jsonResult['state']) ? $jsonResult['state'] : '';
                $transactionDetails = isset($jsonResult['transactions'][0]) ? $jsonResult['transactions'][0] : '';
                if ($transactionDetails) {
                    $transactionAmountTotal = isset($transactionDetails['amount']['total']) ? $transactionDetails['amount']['total'] : '';
                    $transactionAmountCurrency = isset($transactionDetails['amount']['currency']) ? $transactionDetails['amount']['currency'] : '';
                    if (isset($transactionDetails['related_resources']) && $transactionDetails['related_resources']) {
                        $transactionStatus = isset($transactionDetails['related_resources'][0]['sale']['state']) ? $transactionDetails['related_resources'][0]['sale']['state'] : '';
                    }
                }

                $this->log->addInfo('verifyPayment transactionState (NEW CREDENTIALS TOKEN)', [$transactionState]);
                if ($transactionState === 'approved') {
                    if ($transactionAmountTotal != $paymentDetails['total'] || $transactionAmountCurrency != $paymentDetails['currency_code']) {
                        return false;
                    }
                    if (! $this->ppSandbox && $transactionStatus !== 'completed') {
                        return false;
                    }
                } else {
                    return false;
                }

                $returnResult = true;
            }
        }
        //TMP CODE

        return $returnResult;
        //return true;
    }

    private function createDateRangeArray($strDateFrom, $strDateTo)
    {

        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.

        $dateRange = [];

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($dateRange, date('Y-m-d', $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($dateRange, date('Y-m-d', $iDateFrom));
            }
        }

        return $dateRange;
    }

    private function logActivity($customers_id, $type)
    {
        try {
            $customer = Customer::find($customers_id);

            if (! $customer) {
                return;
            }

            $customer_device = RegisteredPhonesWithApps::where('customer_ID', $customers_id)->first();

            if (! $customer_device) {
                return;
            }

            $device_uid = $customer_device->phone_UID;
            $platform = 'web'; // default platform is web

            if (strpos($device_uid, '-') != false) {
                $platform = 'ios';
            }

            if (strlen($device_uid) == 16) {
                $platform = 'android';
            }

            $customer_activity = new CustomerActivity();
            $customer_activity->action = $type;
            $customer_activity->platform = $platform;
            $customer_activity->customer_id = $customers_id;
            $customer_activity->date_time = date('Y-m-d H:i:s');
            $customer_activity->save();
        } catch (Exception $e) {
        }
    }
}
