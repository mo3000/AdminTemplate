<?php


class Route {
	public static $actions = [];
	public static function any($route, $action)
	{
		self::checkRoute($route, $action);
	}

	public static function get($route, $action)
	{
		self::checkRoute($route, $action);
	}

	public static function post($route, $action)
	{
		self::checkRoute($route, $action);
	}

	public static function checkRoute($route, $action)
	{
		self::$actions[] = $action;
	}

	public static function group(array $config, callable $call)
	{
		$array = self::$actions;
		self::$actions = [];
		$call();
		self::$actions = array_merge($array, self::$actions);
	}
}

include './routes/api.php';

foreach (Route::$actions as $line) {
	echo "$line\n";
}
