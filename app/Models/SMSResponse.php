<?php
use Illuminate\Database\Eloquent\Model;

class SMSResponse extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sms_replies';

    protected $primaryKey = 'ID';
}
