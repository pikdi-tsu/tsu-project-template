<?php

if(!function_exists('checkmenu'))
{
	function checkmenu($modul, $menu)
	{
		if(session('admin') === 'admin') return true;

        $groups = session('groupuser');

        if (!$groups) {
            return false;
        }

        $g = $groups->where('Modul', $modul)
            ->where('Menu', $menu)
            ->first();

        if($g) {
            return true;
        }

        return false;
	}
}

if(!function_exists('checkmodul'))
{
	function checkmodul($modul)
	{
		if(session('admin') === 'admin') return true;

        $groups = session('groupuser');

        if (!$groups) {
            return false;
        }

        $g = $groups->where('Modul', $modul)
            ->first();

        if($g) {
            return true;
        }

        return false;
	}
}
