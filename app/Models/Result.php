<?php

namespace App\Models;

class Result
{
    /**
     * Return a failure message.
     * @param msg string, Specifies the message of the failure.
     * @return json
     */
    public static function fail($msg = '')
    {
        $return = [
            'success' => 0,
            'msg' => $msg,
            ];

        return json_encode($return);
    }

    /**
     * Return a successful message.
     * @param ai array, Additional information that needs to be returned to the client.
     * @return json
     */
    public static function success($ai = [])
    {
        $return = [
            'success' => 1,
            'ai' => $ai,
            ];

        return json_encode($return);
    }
}
