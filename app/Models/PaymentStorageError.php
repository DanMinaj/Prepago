<?php

class PaymentStorageError extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test_payments_storage_errors';

    public static function log($customerID, $message)
    {
        $log = new self();
        $log->customer_id = $customerID;
        $log->message = $message;
        $log->save();
    }
}
