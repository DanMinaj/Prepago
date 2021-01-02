<?php

class BossUsersController extends BOSSController {

    protected $adminID;

    public function __construct()
    {
        $this->adminID = getAdminID();
    }


    public function displayAssignUsersPage($userID = null)
    {
        $userID = $userID ? : Auth::user()->id;
        $user = User::findOrFail($userID);
		$bossLevel = getBossLevel($user);

        //get list of people that are not assigned yet
        $unassignedUsers = User::where('id', '!=', $userID)->excludeAdmin()->unassigned()->get();
        $bossLevelUser = !$userID ? 'Admin' : getBossLevelName($bossLevel);
        $bossLevelDownUser = !$userID ? 'Agent' : getBossLevelName($bossLevel + 1);

        $this->layout->page = View::make('boss/assign_users', [
            'user'              => $user,
            'users'             => $unassignedUsers,
            'bossLevelUser'     => $bossLevelUser,
            'bossLevelDownUser' => $bossLevelDownUser
        ]);
    }

    public function assign($currentUserID = null)
    {
        $userIDs = Input::get('users');
        $settings = getSettings();

        // ------------- check users number restrictions ------------------
        $currentUserID = $currentUserID ? : Auth::user()->id;
        $currentUser = User::findOrFail($currentUserID);
        $currentUserBossLevel = getBossLevel($currentUser);
        $childrenLevelName = strtolower(getBossLevelName($currentUserBossLevel + 1));
        $children = getImmediateChildren($currentUser);
        $childrenCount = $children->count();
		
        //check the selected users count against the settings for the children level
        if (($childrenCount + count($userIDs)) > $settings['number_' . $childrenLevelName . 's'])
        {
            return Redirect::to('boss' . ($currentUserID !== Auth::user()->id ? '/' . $currentUserID : '') . '/assign')
                    ->with('errorMessage', 'The assigned users count exceeds the maximum allowed');
        }

        // ------------- check the schemes restrictions ------------------
        $childrenSchemesCount = 0;
        foreach ($children as $child)
        {
            $childrenSchemesCount += $child->schemes()->withoutArchived()->count();
        }

        $schemesCount = $childrenSchemesCount;
        if ($userIDs)
        {
            foreach ($userIDs as $userID)
            {
                $userSchemeCount = User::find($userID) ? User::find($userID)->schemes()->withoutArchived()->count() : 0;
                $schemesCount += $userSchemeCount;
                if ($schemesCount > $settings['number_schemes_per_level'])
                {
                    return Redirect::to('boss' . ($currentUserID !== Auth::user()->id ? '/' . $currentUserID : '') . '/assign')
                        ->with('errorMessage', 'The number of schemes exceeds the maximum allowed');
                }
            }
        }
        else
        {
            if ($schemesCount > $settings['number_schemes_per_level'])
            {
                return Redirect::to('boss' . ($currentUserID !== Auth::user()->id ? '/' . $currentUserID : '') . '/assign')
                    ->with('errorMessage', 'The number of schemes exceeds the maximum allowed');
            }
        }


        if ($userIDs)
        {
            foreach ($userIDs as $userID)
            {
                $user = User::where('id', $userID);
                if (!$user->update(['parent_id' => $currentUserID]))
                {
                    return Redirect::to('boss/' . $currentUserID)->with('errorMessage', 'The user ' . $user->first()->username . ' cannot be assigned');
                }
            }

            return Redirect::to('boss/' . $currentUserID)->with('successMessage', 'The users were assigned successfully');
        }

        return Redirect::to('boss/' . $currentUserID);
    }

    public function unassign($userID)
    {
        $user = User::findOrFail($userID);

        //get the parent of the user we're unassigning
        $parentID = $user->parent_id;

        //set parent id of the user we're unassigning to 0
        if (!$user->update(['parent_id' => 0]))
        {
            return Redirect::to('boss/' . $parentID)->with('errorMessage', 'The user cannot be unassigned');
        }
		
		//set the parent id of the children of the user we're unassigning to 0
        $childrenIDs = getChildren($user->id);
		if ($childrenIDs)
        {
			if (!User::whereIN('id', $childrenIDs)->update(['parent_id' => 0]))
			{
				return Redirect::to('boss/' . $parentID)->with('errorMessage', 'The user\'s children cannot be unassigned');
			}
		}	

        return Redirect::to('boss/' . $parentID)->with('successMessage', 'The user was unassigned successfully');
    }

    public function displayReassignPage($userID)
    {
        $user = User::findOrFail($userID);
        $usersToMoveTo = $this->getUsersToMoveTo($userID);

        $this->layout->page = View::make('boss/move_user', [
            'user'              => $user,
            'users'             => $usersToMoveTo
        ]);
    }

    public function reassign($userID)
    {
        $moveToUserID = (int)Input::get('users');

        //check restrictions
        $isAllowedToMove = $this->isAllowedToMove($userID, $moveToUserID);

        if ($isAllowedToMove === 'exceed_user_number')
        {
            return Redirect::to('boss/' . $userID . '/reassign')->with('errorMessage', 'The user you\'re trying to assign to already has the maximum number of allowed users. Try reassigning the current user to another one');
        }
        else if ($isAllowedToMove === 'exceed_scheme_number')
        {
            return Redirect::to('boss/' . $userID . '/reassign')->with('errorMessage', 'The number of schemes would exceed the allowed number. Try reassigning the current user to another one');
        }

        $user = User::findOrFail($userID);

        //get the parent of the user we're unassigning
        $parentID = $user->parent_id;

        //get the immediate children of the user we're unassigning
        $children = getImmediateChildren($user);
        if ($children->count())
        {
            $childrenIDs = $children->lists('id');
            //set the children's parent to $parentID
            if (!User::whereIn('id', $childrenIDs)->update(['parent_id' => $parentID]))
            {
                return Redirect::to('boss/' . $userID . '/reassign')->with('errorMessage', 'Cannot set the new parent');
            }
        }

        //set the parent of the user we're moving to the $moveToUserID
        if (!$user->update(['parent_id' => $moveToUserID]))
        {
            return Redirect::to('boss/' . $userID . '/reassign')->with('errorMessage', 'Cannot reassign the user');
        }

        return Redirect::to('boss' . ($parentID !== Auth::user()->id ? '/' . $parentID : ''))->with('successMessage', 'The user was successfully reassigned');

    }

    private function isAllowedToMove($userID, $moveToUserID)
    {
        $user = User::findOrFail($userID);
        $moveToUser = User::findOrFail($moveToUserID);
        $settings = getSettings();

        //get $moveToUserLevel
        $moveToUserLevel = getBossLevel($moveToUser);

        //get number of children under $moveToUser
        $moveToUserChildren = getImmediateChildren($moveToUser);
        $moveToUserChildrenCount = $moveToUserChildren ? $moveToUserChildren->count() : 0;

        //check the number of children under $moveToUser against the setting for this boss level
        $moveToUserChildrenLevel = $moveToUserLevel+1;
        $moveToUserChildrenLevelName = strtolower(getBossLevelName($moveToUserChildrenLevel));

        if ($moveToUserChildrenCount >= $settings['number_' . $moveToUserChildrenLevelName . 's'])
        {
            return 'exceed_user_number';
        }

        //check the number of schemes for $moveToUser, add the schemes for the user we're unassigning and check again the settings
        $moveToUserChildren = getImmediateChildren($moveToUser);
        $moveToUserChildrenSchemesCount = 0;
        foreach ($moveToUserChildren as $child)
        {
            $moveToUserChildrenSchemesCount += $child->schemes()->withoutArchived()->count();
        }
		
        $userSchemes = $user->schemes()->withoutArchived()->count();
        if ($moveToUserChildrenSchemesCount*1 + $userSchemes > $settings['number_schemes_per_level'])
        {
            return 'exceed_scheme_number';
        }

        return true;
    }

    private function getUsersToMoveTo($excludeUserID)
    {
        //return User::where('group_id', '>', $groupID)->where('parent_id', '!=', 0)->orWhere('id', $this->adminID)->get();
        //return User::where('parent_id', '!=', 0)->orWhere('id', $this->adminID)->get();

        //get the list of Admin, Agents and Distributors (you can't move a user under an Operator because that's the lowest level)
        $admin = User::find($this->adminID);
        $users = [
            [
                'id'         => $admin->id,
                'username'   => $admin->username,
                'level_name' => 'Admin'
            ]
        ];
        $assignedUsers = User::where('parent_id', '!=', 0)->where('id', '!=', $excludeUserID)->get();
        foreach ($assignedUsers as $assignedUser)
        {
            $level = getBossLevel($assignedUser);
            if ($level < 3)
            {
                $users[] = [
                    'id'         => $assignedUser->id,
                    'username'   => $assignedUser->username,
                    'level_name' => getBossLevelName($level)
                ];
            }
        }

        return $users;
    }
}