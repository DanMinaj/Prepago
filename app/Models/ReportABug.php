<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class ReportABug extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'report_a_bug';

    protected $appends = ['created', 'replies'];

    public function getPreviewAttribute()
    {
        return implode(' ', array_slice(str_word_count($this->description, 2), 0, 5)).' .. ';
    }

    public function getRepliesAttribute()
    {
        $replies = [];

        $sms = SMSMessage::where('bug_report', $this->id)->get();

        $responses = [];

        if (strlen($this->responses) > 0) {
            $responses = unserialize($this->responses);
            if (is_array($responses)) {
                foreach ($responses as $k => $r) {
                    array_push($replies, (object) [
                        'time_date' => $r->time_date,
                        'response' => $r->response,
                        'type' => 'me',
                        'time_date_formatted' => Carbon\Carbon::parse($r->time_date)->diffForHumans(),
                    ]);
                }
            }
        }

        foreach ($sms as $k => $s) {
            array_push($replies, (object) [
                'time_date' => $s->date_time,
                'response' => $s->message,
                'type' => 'admin',
                'time_date_formatted' => Carbon\Carbon::parse($s->date_time)->diffForHumans(),
            ]);
        }

        usort($replies, function ($a, $b) {
            return  ( new DateTime($a->time_date) ) < ( new DateTime($b->time_date));
        });

        return $replies;
    }

    public function reply($reply)
    {
        $responses = [];

        if (strlen($this->responses) > 0) {
            $responses = unserialize($this->responses);
        }

        if (! is_array($responses)) {
            $responses = [];
        }

        array_push($responses, (object) [
            'time_date' => date('Y-m-d H:i:s'),
            'response' => $reply,
            'type' => 'me',
        ]);

        $responses = serialize($responses);
        $this->responses = $responses;
        $this->save();
    }

    public function getCreatedAttribute()
    {
        return Carbon\Carbon::parse($this->created_at)->diffForHumans();
    }

    public function getCustomerAttribute()
    {
        $customer = Customer::find($this->customer_id);

        try {
            if (! $customer) {
                $customer = Customer::where('username', self::decipherUsername($this->apt_number.$this->apt_building))->first();
            }
        } catch (Exception $e) {
            return false;
        }

        return $customer;
    }

    public function getIssueFiltered()
    {
        if (strpos($this->description, 'I received a resolution by looking at the reply which wa') !== false) {
            $desc = $this->description;

            $parts = explode("'", $desc);

            return $parts[1];
        } else {
            return $this->description;
        }
    }

    public function getResponseFiltered()
    {
        $delim = 'I received a resolution by looking at the reply which was:';
        if (strpos($this->description, $delim) !== false) {
            $desc = $this->description;

            $parts = explode($delim, $desc);

            return $parts[1];
        } else {
            return null;
        }
    }

    public function getSMSResponses()
    {
        $sms = SMSMessage::where('bug_report', $this->id)->get();

        return $sms;
    }

    public function getSchemeAttribute()
    {
        try {
            $customer = $this->customer;
            $scheme = null;

            if ($this->customer) {
                if ($customer->scheme) {
                    return $customer->scheme;
                }
            }

            $scheme = Scheme::where('scheme_nickname', $this->apt_building)->first();
            if ($scheme) {
                return $scheme;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    public static function decipherUsername($input)
    {
        $input = strtolower($input);
        $input = str_replace(' ', '', $input);

        return $input;
    }

    public function sendFollowUpEmail()
    {
        $reminder = ($this->follow_up_sent == 1 && $this->follow_up_sent_2 == 0);
        $customer = $this->customer;
        $query = $this;

        if ($this->follow_up_sent == 0) {
            $this->follow_up_sent = 1;
            $this->follow_up_sent_at = date('Y-m-d H:i:s');
            $this->save();
        }

        // if($this->follow_up_sent_2 == 0) {
        // $this->follow_up_sent_2 = 1;
        // $this->follow_up_sent_at = date('Y-m-d H:i:s');
        // $this->save();
        // }

        Mail::send('emails.support_follow_up', ['reminder' => $reminder, 'customer' => $customer, 'query' => $query], function ($message) use ($customer) {
            $message->to($customer->email_address);
            $message->from('info@prepago.ie');
            $message->subject('SnugZone Support: Was your issue solved?');
        });
    }

    public function sendCreationEmail($solved = false)
    {
        try {
            $new_ticket_email_recipients = SystemSetting::get('new_ticket_email_recipients');
            $emails = explode("\n",
            str_replace(["\r\n", "\n\r", "\r"], "\n", $new_ticket_email_recipients));
            //preg_split ('/$\R?^/m', $new_ticket_email_recipients);

            $subject = 'Prepago - New Bug Report: Customer#'.$this->customer_id;

            if ($solved || strpos($this->description, 'I received a resolution by looking at the reply w') !== false) {
                $subject = 'Prepago - New Bug Report Followup: Customer#'.$this->customer_id.' **Auto solved**';
                $this->resolved = 1;
                $this->sendFollowUpEmail();
            }
            $this->save();

            $from = SystemSetting::get('email_default_from');
            $who = SystemSetting::get('email_default_name');
            $emailInfo = [];
            $emailInfo['email_addresses'] = $emails;
            //$emailInfo['email_addresses'] = ['daniel@prepago.ie'];
            $data = [];

            $data['bug'] = $this;
            $email_template = 'emails.bugreport.index';

            Mail::send($email_template, $data, function ($message) use ($emailInfo, $subject, $from, $who) {
                $message->from($from, $who)->subject($subject);
                $message->to($emailInfo['email_addresses']);
            });
        } catch (Exception $b) {
            echo $b->getMessage();
        }
    }
}
