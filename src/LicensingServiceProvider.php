<?php namespace Ahead4\Licensing;

use Illuminate\Support\ServiceProvider;

class LicensingServiceProvider extends ServiceProvider
{

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('ahead4.licensing', function () {
			return $this->app->make('Ahead4\Licensing\Licensing');
		});
	}

	/**
	 * Boot the service provider.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'ahead4.licensing');

		$this->registerValidatorExtensions();
	}

	/**
	 * Register validator extensions.
	 *
	 * @return void
	 */
	protected function registerValidatorExtensions()
	{
		$this->app->validator->extend('license', function ($attribute, $value, $parameters) {
			$tempFile = temp_file();
			
			file_put_contents($tempFile, $value);

			return $this->app['ahead4.licensing']->verifyLicense($tempFile, __DIR__ . '/../resources/public.key');
		}, trans('ahead4.licensing::messages.invalid_license_entered'));
	}
}
