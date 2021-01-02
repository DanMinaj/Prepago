<?php

namespace App\Interfaces;

interface WebServiceRepositoryInterface
{
    public function getCustomerLoginRequest($email, $username, $password, $phoneID, $model = '');

    public function getInformationRequest($customer_id, $email, $username, $password);

    public function getIOURequest($customer_id, $email, $username, $password, $IOU_type);

    public function getRecentTopUpsRequest($customer_id, $email, $username, $password);

    public function getBarcodeRequest($customer_id, $email, $username, $password);

    public function getFAQRequest($customer_id, $email, $username, $password);

    public function getTopupLocationsRequest($customer_id, $email, $username, $password, $lat, $lon);

    public function getBarcodeWebappRequest($username);

    public function getRemoteControlInformationRequest($customer_id, $email, $username, $password);

    public function setRemoteControlInformationRequest($customer_id, $email, $username, $password, $json);
}
