<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Mail;

class MailRepository
{
    public function sendCustomerSetUpEmail($data = null)
    {
        try {
            $userInfo = [];
            $userInfo['email_address'] = $data['email_address'];

            return Mail::send('emails.customer_set_up', $data, function ($message) use ($userInfo) {
                $message->from('noreply@snugzone.biz')->subject('SnugZone Login Credentials');
                //$message->from('admin@localhost.com')->subject('SnugZone Login Credentials');
                $message->to($userInfo['email_address']);
            });
        } catch (Exception $e) {
        }
    }
}
