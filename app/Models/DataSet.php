<?php

class DataSet
{
    public $data;
    public $label;
    public $borderColor;
    public $borderWidth = 2;
    public $fill = 'false';
    public $fillColor = "''";

    public function __construct($label)
    {
        $this->data = [];
        $this->label = '"'.$label.'"';
        $this->borderColor = '"#ccc"';
    }

    public function get()
    {
        return '
			{
				label: '.$this->label.',
				data: ['.implode(', ', $this->data).'],
				borderColor: '.$this->borderColor.',
				borderWidth: '.$this->borderWidth.',
				fill: '.$this->fill.',
				fillColor: '.$this->fillColor.',
			},
		';
    }

    public function setFill($fill)
    {
        $this->fill = 'true';
        $this->fillColor = "'$fill'";
    }

    public function setColor($color)
    {
        $this->borderColor = "'$color'";
    }

    public static function getLastYear()
    {
        $last_year = date('Y-m-d', strtotime('last year')).' 00:00:00';
        $this_year = date('Y-m-d').' 00:00:00';

        $ret = [];
        $ret['this_year'] = $this_year;
        $ret['last_year'] = $last_year;
        $ret['months'] = [];
        $ret['labels'] = [];

        while ($last_year <= $this_year) {
            $ret['months'][] = (new DateTime($last_year))->format('Y-m');
            $ret['labels'][] = '"'.(new DateTime($last_year))->format('M Y').'"';

            $last_year = (new DateTime($last_year))->modify('+1 month')->format('Y-m-d H:i:s');
        }

        return $ret;
    }

    public static function getLastWeek()
    {
        $last_week = date('Y-m-d', strtotime('last week Monday')).' 00:00:00';
        $this_week = date('Y-m-d', strtotime('this week Sunday')).' 23:59:59';
        $this_start_week = date('Y-m-d', strtotime('this week Monday')).' 00:00:00';

        $ret = [];
        $ret['this_week'] = $this_week;
        $ret['last_week'] = $last_week;
        $ret['this_weeks_days'] = [];
        $ret['last_weeks_days'] = [];
        $ret['labels'] = [];

        while ($last_week < $this_week) {
            if ($last_week < $this_start_week) {
                $ret['last_weeks_days'][] = (new DateTime($last_week))->format('Y-m-d').'';
                $ret['labels'][] = '"'.(new DateTime($last_week))->format('D').'"';
            } else {
                $ret['this_weeks_days'][] = (new DateTime($last_week))->format('Y-m-d').'';
            }

            $last_week = (new DateTime($last_week))->modify('+1 day')->format('Y-m-d H:i:s');
        }

        return $ret;
    }

    public static function getLastDay()
    {
        $yesterday = date('Y-m-d', strtotime('yesterday')).' 00:00:00';
        $today = date('Y-m-d', strtotime('today')).' 23:59:59';

        $ret = [];
        $ret['today'] = $today;
        $ret['yesterday'] = $yesterday;
        $ret['todays_hours'] = [];
        $ret['yesterdays_hours'] = [];
        $ret['labels'] = [];

        while ($yesterday < $today) {
            if ((new DateTime($yesterday))->format('Y-m-d') == date('Y-m-d')) {
                $ret['todays_hours'][] = (new DateTime($yesterday))->format('Y-m-d H');
            } else {
                $ret['yesterdays_hours'][] = (new DateTime($yesterday))->format('Y-m-d H');
                $ret['labels'][] = '"'.(new DateTime($yesterday))->format('hA').'"';
            }

            $yesterday = (new DateTime($yesterday))->modify('+1 hour')->format('Y-m-d H:i:s');
        }

        return $ret;
    }

    public static function isLastWeek($date)
    {
        $lastWeekDates = self::getLastWeek();

        foreach ($lastWeekDates['last_weeks_days'] as $k=>$v) {
            if ($v == $date) {
                return true;
            }
        }

        return false;
    }

    public static function isThisWeek($date)
    {
        $lastWeekDates = self::getLastWeek();

        foreach ($lastWeekDates['this_weeks_days'] as $k=>$v) {
            if ($v == $date) {
                return true;
            }
        }

        return false;
    }
}
