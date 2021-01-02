<?php
function hasAccess($permissions, $all = true)
{
    //get group permissions
    $groupPermissions = getGroupPermissions() ? : [];
	
	
    if (!is_array($permissions))
    {
        $permissions = (array)$permissions;
    }

    if ($all)
    {
        $hasAccess = true;
        foreach ($permissions as $permission)
        {
            $hasAccess = $hasAccess && in_array($permission, $groupPermissions);
        }
    }
    else
    {
        $hasAccess = false;
        foreach ($permissions as $permission)
        {
            $hasAccess = $hasAccess || in_array($permission, $groupPermissions);
        }
    }

    return $hasAccess;
}

function getGroupPermissions()
{
	$groupPermissions = Group::findOrFail(Auth::user()->group_id)->permissions;
    return $groupPermissions;
}

function groups()
{
    return Group::orderBy('name')->get()->lists('name', 'id');
}