<?php
use Illuminate\Database\Eloquent\Model;

class UserSignIn extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'utility_company_signins';
    /*
    protected $primaryKey = 'ID';

    public $timestamps = false;
    */

    public static function stamp($operator_id, $IP)
    {
        $user = User::find($operator_id);

        if (! $user) {
            return;
        }

        $user_sign_in = self::where('IP', $IP)->where('operator_id', $operator_id)->first();

        if (! $user_sign_in) {
            $user_sign_in = new self();
            $user_sign_in->IP = $IP;
            $user_sign_in->operator_id = $operator_id;
            $user_sign_in->save();
        } else {
            $user_sign_in->touch();
        }
    }
}
