<?php

function getSchemes($user)
{
    $userSchemes = getUserSchemes($user);

    $childrenSchemes = getChildrenSchemes($user);

    return array_unique(array_merge($userSchemes, $childrenSchemes));
}

function getChildrenSchemes($user, $schemes = [])
{
    //get user's children
    $children = User::where('parent_id', $user->id)->get();
    if ($children->count() > 0) {
        foreach ($children as $child) {
            $schemes = array_merge($schemes, getUserSchemes($child));
            $schemes = getChildrenSchemes($child, $schemes);
        }
    }

    return $schemes;
}

function getUserSchemes($user)
{
    $userSchemes = $user->schemes ? $user->schemes()->withoutArchived()->select('schemes.id')->lists('schemes.id') : [];

    return $userSchemes;
}

/**
 * The number of parents above the current user determines the boss level
 * Admin - no parents
 * Agent - 1 parent
 * Distributor - 2 parents
 * Operator - 3 parents.
 */
function getBossLevel($user, $parentsCount = 0)
{
    $parent = User::where('id', $user->parent_id)->first();
    if (! $parent && $user->group_id != 4) {
        return null;
    } elseif (! $parent) {
        return $parentsCount;
    } else {
        return getBossLevel($parent, $parentsCount + 1);
    }
}

function getBossLevelName($level)
{
    $levelName = '';
    switch ($level) {
        case '0': $levelName = 'Admin'; break;
        case '1': $levelName = 'Agent'; break;
        case '2': $levelName = 'Distributor'; break;
        case '3': $levelName = 'Operator'; break;
    }

    return $levelName;
}

function getImmediateChildren($parent, $excludeUserID = null)
{
    if (! $parent) {
        return new \Illuminate\Database\Eloquent\Collection();
    }

    $query = User::where('parent_id', $parent->id);
    if ($excludeUserID) {
        $query = $query->where('id', '!=', $excludeUserID);
    }

    return $query->get();
}

function getAdminID()
{
    return User::where('group_id', 4)->first()->id;
}

function getSettings()
{
    $loggedUserBossLevel = getBossLevel(Auth::user());

    //if the logged in user is a distributor or an operator -> get the  parent agent settings
    $userID = Auth::user()->id; //by default set the setting to admin or agent
    if ($loggedUserBossLevel == 2) { //distributor
        $userID = Auth::user()->parent_id;
    } elseif ($loggedUserBossLevel == 3) { //operator
        $parent = User::where('id', Auth::user()->parent_id)->first(); //distributor
        //get distributor's parent -> aka Agent
        $userID = $parent->parent_id;
    }

    $settings = UserSetting::where('user_id', $userID)->first();
    $settings = $settings ? $settings->toArray() : [];

    //if the currently logged in Agent hadn't set any settings -> get the admin settings
    if (! $settings && $loggedUserBossLevel == 1) {
        $settings = UserSetting::where('user_id', getAdminID())->first();
        $settings = $settings ? $settings->toArray() : [];
    }

    //if admin didn't provide boss settings -> get the default values from the config file
    if (! $settings) {
        $settings = \Config::get('boss.settings');
    }

    return $settings;
}

function displayBossHierarchy()
{
    $admin = User::findOrFail(getAdminID());
    echo '<p>'.strtoupper($admin->username).'</p>';
    displayBossHierarchyChildren($admin, 1);
}

function displayBossHierarchyChildren($parent, $level)
{
    $children = getImmediateChildren($parent);
    if ($children->count()) {
        foreach ($children as $child) {
            echo '<p>';
            echo str_repeat('-', $level * 8);
            echo $child->username.' <span>('.getBossLevelName($level).')</span><br />';
            foreach ($child->schemes as $childScheme) {
                echo str_repeat('&nbsp;&nbsp;', $level * 6).'<span class="schemes">'.($childScheme->username ?: $childScheme->company_name.'</span><br />');
            }
            echo '</p>';
            $newLevel = $level + 1;
            displayBossHierarchyChildren($child, $newLevel);
        }
    }
}

function getChildren($userID)
{
    $childrenIDs = [];
    $children = User::where('parent_id', $userID)->get();
    if ($children->count()) {
        foreach ($children as $child) {
            $childrenIDs[] = array_merge([$child->id], getChildren($child->id));
            //echo 'children count: ';var_dump($childrenIDs);echo '<br />';
        }
    }

    return array_flatten($childrenIDs);
}
