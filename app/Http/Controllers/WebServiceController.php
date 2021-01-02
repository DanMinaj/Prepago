<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as Response;

class WebServiceController extends BaseController
{
    private $ws;
    private $headers;

    // ----------------------------------------------------------------------------------------------------------
    public function __construct(WebServiceRepositoryInterface $ws)
    {
        $this->ws = $ws;
        $this->headers = ['Content-type'=> 'application/json; charset=utf-8'];
    }

    // ----------------------------------------------------------------------------------------------------------
    public function customerLoginRequest($email, $username, $password, $phone_id, $model = '')
    {
        $response = [];
        $email = urldecode($email);
        $username = urldecode($username);
        $password = urldecode($password);
        $phone_id = urldecode($phone_id);
        $model = urldecode($model);

        $response = $this->ws->getCustomerLoginRequest($email, $username, $password, $phone_id, $model);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function informationRequest($customer_id, $email, $username, $password)
    {
        $response = [];
        $customer_id = (int) $customer_id;
        $email = urldecode($email);
        $username = urldecode($username);
        $password = urldecode($password);

        $response = $this->ws->getInformationRequest($customer_id, $email, $username, $password);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function IOURequest($customer_id, $email, $username, $password, $IOU_type)
    {
        $response = [];
        $customer_id = (int) $customer_id;
        $email = urldecode($email);
        $username = urldecode($username);
        $password = urldecode($password);
        $IOU_type = (int) $IOU_type;

        $response = $this->ws->getIOURequest($customer_id, $email, $username, $password, $IOU_type);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function recentTopUpsRequest($customer_id, $email, $username, $password)
    {
        $response = [];
        $customer_id = (int) $customer_id;
        $email = urldecode($email);
        $username = urldecode($username);
        $password = urldecode($password);

        $response = $this->ws->getRecentTopUpsRequest($customer_id, $email, $username, $password);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getBarcodeRequest($customer_id, $email, $username, $password)
    {
        $response = [];
        $customer_id = (int) $customer_id;
        $email = urldecode($email);
        $username = urldecode($username);
        $password = urldecode($password);

        $response = $this->ws->getBarcodeRequest($customer_id, $email, $username, $password);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getFAQRequest($customer_id, $email, $username, $password)
    {
        $response = [];
        $customer_id = (int) $customer_id;
        $email = urldecode($email);
        $username = urldecode($username);
        $password = urldecode($password);

        $response = $this->ws->getFAQRequest($customer_id, $email, $username, $password);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getTopupLocationsRequest($customer_id, $email, $username, $password, $lat, $lon)
    {
        $response = [];
        $customer_id = (int) $customer_id;
        $email = urldecode($email);
        $username = urldecode($username);
        $password = urldecode($password);
        $lat = floatval($lat);
        $lon = floatval($lon);

        $response = $this->ws->getTopupLocationsRequest($customer_id, $email, $username, $password, $lat, $lon);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    public function getBarcodeRotateRequest($username)
    {
        $response = [];
        $response['flag_message'] = 0;
        $response['error'] = '';
        $response['barcode'] = '';

        $customer = Customer::where('username', $username)->first();
        $password = $customer->password;
        $email = $customer->email;
        $data['barcode'] = $customer->barcode;

        header('Content-type: image/png');

        $public_path = $_SERVER['DOCUMENT_ROOT'];

        $barcode_file_name = $public_path.'/Barcodes/'.$data['barcode'].'.png';
        $barcode_file = $this->read_file($barcode_file_name);

        $original = imagecreatefrompng($barcode_file_name);
        $rotated = imagerotate($original, 90, 0);

        imagepng($rotated);
        imagedestroy($rotated);
        exit(0);
    }

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
    public function getBarcodeWebappRequest($username)
    {
        $response = [];
        $username = urldecode($username);

        $response = $this->ws->getBarcodeWebappRequest($username);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function getRemoteControlInformationRequest($customer_id, $email, $username, $password)
    {
        $response = [];
        $customer_id = (int) $customer_id;
        $email = urldecode($email);
        $username = urldecode($username);
        $password = urldecode($password);

        $response = $this->ws->getRemoteControlInformationRequest($customer_id, $email, $username, $password);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function setRemoteControlInformationRequest($customer_id, $email, $username, $password, $json)
    {
        $response = [];
        $customer_id = (int) $customer_id;
        $email = urldecode($email);
        $username = urldecode($username);
        $password = urldecode($password);
        $json = urldecode($json);

        $response = $this->ws->setRemoteControlInformationRequest($customer_id, $email, $username, $password, $json);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function toggleRemoteControl($customer_id, $email, $username, $password)
    {
        $response = [];
        $customer_id = (int) $customer_id;
        $email = urldecode($email);
        $username = urldecode($username);
        $password = urldecode($password);

        $response = $this->ws->toggleRemoteControl($customer_id, $email, $username, $password);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function addPaypalPaymentRequest($customer_id, $email, $username, $password, $json, $debug = false)
    {
        $response = [];
        $customer_id = (int) $customer_id;
        $email = urldecode($email);
        $username = urldecode($username);
        $password = urldecode($password);
        $json = urldecode($json);

        $response = $this->ws->addPaypalPaymentRequest($customer_id, $email, $username, $password, $json, $debug);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function paypointPaymentsTurnMeterOnOff($customer_id)
    {
        $response = [];
        $customer_id = (int) $customer_id;

        $response = $this->ws->paypointPaymentsTurnMeterOnOff($customer_id);

        return \Illuminate\Support\Facades\Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------------------------------------------------
    public function customerGraphData($customer_id, $email, $username, $password, $date_from, $date_to)
    {
        $response = [];
        $customer_id = (int) $customer_id;

        $response = $this->ws->customerGraphData($customer_id, $email, $username, $password, $date_from, $date_to);

        return Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    public function handleAwayModeValveSwitch($pmd_id, $awayModeOn)
    {
        try {
            $pmd = PermanentMeterData::where('ID', $pmd_id)->first();
            if (! $pmd) {
                throw new Exception("PMD $pmd_id does not exist or is deleted!");
            }
            $customer = $pmd->customer;

            if (! $customer) {
                throw new Exception('Customer '.$customer->id.' does not exist or is deleted!');
            }
            if ($awayModeOn == 0) {
                $customer->sendOpenCommand(true);
            } elseif ($awayModeOn == 1) {
                $customer->sendCloseCommand(true);
            }

            $success['success'] = 0;
            $success['msg'] = "Successfully sent away mode valve command [$awayModeOn] to SCU: ".$pmd->scu_number;
            echo json_encode($success);
        } catch (Exception $e) {
            $success['success'] = 0;
            $success['msg'] = 'Unable to modify away mode valve status: '.$e->getMessage();
            echo json_encode($success);
        }
    }
}
