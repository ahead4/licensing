<?php

use Ahead4\Licensing\Licensing;

class LicensingTest extends TestCase
{
	/**
	 * Get a temporary file.
	 *
	 * @return string
	 */
	protected function getTempFile()
	{
		return tempnam(sys_get_temp_dir(), '');
	}

	/** @test */
	public function it_allows_config_to_be_set()
	{
		$licensing = new Licensing;

		$config = ['test' => 123];

		$licensing->setConfig($config);

		$this->assertEquals($licensing->getConfig(), $config);
	}

	/** @test */
	public function it_creates_a_successful_keypair()
	{
		$privateKeyPath = $this->getTempFile();
		$publicKeyPath  = $this->getTempFile();

		$licensing = new Licensing;

		$created = $licensing->createKeypair($privateKeyPath, $publicKeyPath);

		$this->assertTrue($created);
		$this->assertTrue(filesize($privateKeyPath) > 0);
		$this->assertTrue(filesize($publicKeyPath) > 0);
	}

	/** @test */
	public function it_creates_a_valid_license()
	{
		$licensing = new Licensing;

		$license = $licensing->createLicense(['test' => 123], __DIR__ . '/files/private.key');

		$this->assertTrue(strlen($license) > 0);
	}

	/** @test */
	public function it_verifies_a_valid_license()
	{
		$licensing = new Licensing;

		$verified = $licensing->verifyLicense(__DIR__ . '/files/license.txt', __DIR__ . '/files/public.key');

		$this->assertTrue($verified);
	}

	/** @test */
	public function it_doesnt_verify_an_invalid_license()
	{
		$licensing = new Licensing;

		$verified = $licensing->verifyLicense(__DIR__ . '/files/license-modified.txt', __DIR__ . '/files/public.key');

		$this->assertFalse($verified);
	}

	/** @test */
	public function it_returns_original_data_from_valid_license()
	{
		$licensing = new Licensing;

		$data = $licensing->getLicenseData(__DIR__ . '/files/license.txt', __DIR__ . '/files/public.key');

		$this->assertEquals($data, ['test' => 123]);
	}

	/** @test */
	public function it_returns_null_data_from_invalid_license()
	{
		$licensing = new Licensing;

		$data = $licensing->getLicenseData(__DIR__ . '/files/license-modified.txt', __DIR__ . '/files/public.key');

		$this->assertEquals($data, null);
	}
}
