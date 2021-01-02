<?php

use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Collection;

class BOSSController extends BaseController {

    protected $layout = 'layouts.admin_website';

    /**
     * Display the main boss page - list of schemes and list of assigned users
     */
    public function index($userID = null)
    {
        $user = User::findOrFail($userID ? : Auth::user()->id);
        //get assigned schemes
        $schemes = !$userID ? Auth::user()->activeSchemes() : $user->schemes;
        $userBossLevel = getBossLevel($user);
        $bossLevelUser = getBossLevelName($userBossLevel);
        $bossLevelDownUser = getBossLevelName($userBossLevel + 1);

        $users = $this->getAssignedUsers($userID);

        $this->layout->page = View::make('boss/index', [
            'userID'            => $userID ? : Auth::user()->id,
            'user'              => $user,
            'users'             => $users,
            'schemes'           => $schemes,
            'userBossLevel'     => $userBossLevel,
            'bossLevelUser'     => $bossLevelUser,
            'bossLevelDownUser' => $bossLevelDownUser
        ]);
    }

    public function displayBossRestrictionsSettings()
    {
        if ($this->bossLevel > 1)
        {
            return Redirect::to('welcome');
        }

        $settings = Auth::user()->settings;

        $this->layout->page = View::make('settings/boss_restrictions', ['settings' => $settings]);
    }

    public function saveBossRestrictionsSettings()
    {
        $settings = [
            'number_agents'             => (int)Input::get('number_agents'),
            'number_distributors'       => (int)Input::get('number_distributors'),
            'number_operators'          => (int)Input::get('number_operators'),
            'number_schemes_per_level'  => (int)Input::get('number_schemes_per_level')
        ];

        $userSettingsQuery = Auth::user()->settings();

        if ($userSettingsQuery->count())
        {
            if (!Auth::user()->settings()->update($settings))
            {
                return Redirect::to('settings/boss_restrictions')->with('errorMessage', 'The restrictions settings cannot be updated');
            }
        }
        else
        {
            if (!Auth::user()->settings()->save(new UserSetting($settings)))
            {
                return Redirect::to('settings/boss_restrictions')->with('errorMessage', 'The restrictions settings cannot be saved');
            }
        }

        return Redirect::to('settings/boss_restrictions')->with('successMessage', 'The restrictions settings were saved successfully');
    }

    protected function getAssignedUsers($userID)
    {
        $userID = $userID ? : Auth::user()->id;
        return User::where('parent_id', $userID)->get();
    }
	
	public function displayHierarchyPage()
    {
        $this->layout->page = View::make('boss/hierarchy');
    }

}