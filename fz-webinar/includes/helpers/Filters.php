<?php


namespace fzwebinar\Helpers;


class Filters
{
    private static $filterObject;


    private static $namesFilter
        = [
            '_company' => 'companies',
            '_topic' => 'topics',
            '_interest_group' => 'projects',
        ];

    public static function loadFilters()
    {
            self::$filterObject = new \stdClass();
			self::$filterObject->companies = get_option('fz_webinar_filter_company');
			self::$filterObject->topics = get_option('fz_webinar_filter_topic');
			self::$filterObject->projects = get_option('fz_webinar_filter_projects');
      
		return self::$filterObject;
    }

    public static function getFilters()
    {
        return self::$filterObject;
    }

    public static function checkFilter($name){
        $name = self::$namesFilter[$name]??false;
        return ($name and isset(self::$filterObject->$name) and self::$filterObject->$name);
    }

    public static function writeFilters($filters)
    {
       update_option('fz_webinar_filter_company',$filters->companies);
	   update_option('fz_webinar_filter_topic',$filters->topics);
	   update_option('fz_webinar_filter_projects',$filters->projects);
    }
}