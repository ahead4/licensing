<?php namespace Ahead4\Licensing\Facades;

use Illuminate\Support\Facades\Facade;

class Licensing extends Facade
{

	/**
	 * Get facade accessor
	 *
	 * @return string
	 */
	public static function getFacadeAccessor()
	{
		return 'ahead4.licensing';
	}
}
