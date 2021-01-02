<?php

class Email extends Eloquent
{
    protected $table = '';

    public $sender;
    public $from;
    public $to;
    public $title;
    public $body;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->sender = SystemSetting::get('email_default_name');
        $this->from = SystemSetting::get('email_default_from');
    }

    public function send()
    {
        $body = $this->body;
        $title = $this->title;
        $to = $this->to;
        $from = $this->from;
        $sender = $this->sender;

        return Mail::send([], [], function ($message) use ($body, $title, $to, $from, $sender) {
            $message->from($from, $sender)->subject($title);
            $message->to($to);
            $message->setBody($body, 'text/html');
        });
    }

    public static function quick_send($msg_i, $title_i, $to_i, $from_i, $sender_i)
    {
        return Mail::send([], [], function ($message) use ($msg_i, $title_i, $to_i, $from_i, $sender_i) {
            $message->from($from_i, $sender_i)->subject($title_i);
            $message->to($to_i);
            $message->setBody($msg_i, 'text/html');
        });
    }

    public static function admins($title, $msg)
    {
        $admins = preg_split('/\\r\\n|\\r|\\n/', SystemSetting::get('sys_admins'));
        self::quick_send($msg, $title, $admins, 'info@prepago.ie', 'Prepago System Monitor');
    }
}
