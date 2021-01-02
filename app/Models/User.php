<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'utility_company_login_details';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

    protected $fillable = ['name', 'email', 'password'];

    protected $guarded = ['id'];

    public $timestamps = false;

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getSchemeNumberAttribute($value)
    {
        //get the first scheme assigned to the logged in user
        $scheme_number = Auth::user()->schemes()->first() ? Auth::user()->schemes()->first()->scheme_number : $value;

        if (Session::has('scheme_number')) {
            //check if a scheme with this number exists
            if (! Scheme::where('scheme_number', Session::get('scheme_number'))->count()) {
                Session::put('scheme_number', $scheme_number);
            }

            return Session::get('scheme_number');
        } else {
            Session::put('scheme_number', $scheme_number);

            return $scheme_number;
        }
    }

    public function getSchemeAttribute()
    {
        $scheme = Scheme::find(Auth::user()->scheme_number);

        if (! $scheme) {
            $scheme = new Scheme();
            $scheme->id = 0;
            $scheme->scheme_number = 0;
            $scheme->scheme_nickname = 'undefined';
            $scheme->status_ok = 0;
            $scheme->status_debug = 1;
            $scheme->archived = 0;
            $scheme->company_name = 'undefined';
            $scheme->company_address = 'undefined';
        }

        return $scheme;
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return '';
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /*public function scheme() {
        return $this->belongsTo('Scheme', 'scheme_number', 'scheme_number');
    }*/

    public function group()
    {
        return $this->belongsTo('Group', 'group_id');
    }

    public function schemes()
    {
        return $this->belongsToMany('Scheme', 'users_schemes', 'user_id', 'scheme_id');
    }

    public function activeSchemes()
    {
        return $this->schemes()->withoutArchived()->get();
    }

    public function settings()
    {
        return $this->hasOne('UserSetting');
    }

    public function scopeExcludeAdmin($query)
    {
        return $query->where('id', '!=', getAdminID());
    }

    public function scopeUnassigned($query)
    {
        return $query->where('parent_id', 0);
    }

    public function isAutoMeterReadingAvailable($schemeNumber = null)
    {
        if (! $this->hasMeterReadingsAutomationPermissions()) {
            return false;
        }

        return $this->automatedMeterReadings($schemeNumber)->count() == 0;
    }

    public function automatedMeterReadings($schemeNumber = null)
    {
        return PermanentMeterDataMeterReadWebsite::unprocessed($schemeNumber)->where('automated_by_user_ID', $this->id)->get();
    }

    public function automatedSuccessfulMeterReadings($schemeNumber = null)
    {
        return PermanentMeterDataMeterReadWebsite::unprocessed($schemeNumber)->completed()->where('automated_by_user_ID', $this->id)->get();
    }

    public function automatedUnsuccessfulMeterReadings($schemeNumber = null)
    {
        return PermanentMeterDataMeterReadWebsite::unprocessed($schemeNumber)->uncompleted()->where('automated_by_user_ID', $this->id)->get();
    }

    private function hasMeterReadingsAutomationPermissions()
    {
        return in_array($this->id, config('prepago.meter_readings_automation_users'));
    }

    public function hasAllAutomatedReadingsMarkedAsComplete()
    {
        return $this->automatedMeterReadings()->count() === $this->automatedSuccessfulMeterReadings()->count();
    }

    public function isUserTest()
    {
        return $this->id === 4;
    }

    public function mySchemes()
    {
        if ($this->isUserTest()) {
            return Scheme::active();
        }

        //
        $my_scheme_ids = UserScheme::where('user_id', $this->id)->get(['scheme_id']);
        $schemes = Scheme::where('id', '=', '-1')->get();

        foreach ($my_scheme_ids as $k => $s) {
            $scheme = Scheme::find($s->scheme_id);

            if (! $scheme->active) {
                continue;
            }

            $schemes->push($scheme);
        }

        return $schemes;
    }

    public function inScheme($scheme_id)
    {
        return UserScheme::where('user_id', $this->id)->where('scheme_id', $scheme_id)->first();
    }

    public function logPage()
    {
        $user = self::find($this->id);
        $page = $_SERVER['REQUEST_URI'];

        if (substr($page, 0, 1) == '/') {
            $page = explode('/', $page)[1];
        }

        if (strpos($page, 'running_services') !== false) {
            $page = '';
        }

        $this->is_online = 1;
        $this->is_online_time = date('Y-m-d H:i:s');

        if (strlen($page) > 1) {
            $this->is_online_page = $page;
        }

        $this->save();
    }
}
