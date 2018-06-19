<?php namespace Ahead4\Licensing;

class Licensing
{
	/**
	 * The config for new private keys.
	 *
	 * @var array
	 */
	protected $config = [
		'digest_alg'       => 'sha512',
		'private_key_bits' => 4096,
		'private_key_type' => OPENSSL_KEYTYPE_RSA,
	];

	/**
	 * The public key.
	 *
	 * @var null
	 */
	protected $publicKey = null;

	/**
	 * Get the config.
	 *
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Set the config.
	 *
	 * @param array $config
	 */
	public function setConfig(array $config)
	{
		$this->config = $config;
	}

	/**
	 * Create a new keypair.
	 *
	 * @param  string      $privateKeyPath
	 * @param  string      $publicKeyPath
	 * @param  string|null $passphrase
	 * @return boolean
	 */
	public function createKeypair($privateKeyPath, $publicKeyPath, $passphrase = null)
	{
		// Create a new private key.
		$res = openssl_pkey_new($this->getConfig());

		// Export the private key to a string.
		openssl_pkey_export($res, $privKey, $passphrase);

		// Get details about the private key.
		$details = openssl_pkey_get_details($res);

		// Put the keys into files.
		file_put_contents($privateKeyPath, $privKey);
		file_put_contents($publicKeyPath, $details['key']);

		return true;
	}

	/**
	 * Create a new license from an array of data.
	 *
	 * @param  array       $data
	 * @param  string      $privateKeyPath
	 * @param  string|null $passphrase
	 * @return string
	 */
	public function createLicense(array $data, $privateKeyPath, $passphrase = null)
	{
		$data      = json_encode($data);
		$signature = base64_encode($this->signData($data, file_get_contents($privateKeyPath), $passphrase));
		$license   = json_encode([
			'data'      => $data,
			'signature' => $signature,
		]);

		return base64_encode($license);
	}

	/**
	 * Sign some data with a private key.
	 *
	 * @param  string      $data
	 * @param  string      $privateKey
	 * @param  string|null $passphrase
	 * @return string
	 */
	protected function signData($data, $privateKey, $passphrase = null)
	{
		$privateKey = openssl_pkey_get_private($privateKey, $passphrase);

		openssl_sign($data, $signature, $privateKey);
		openssl_free_key($privateKey);

		return $signature;
	}

	/**
	 * Verify a license with a public key.
	 *
	 * @param  string|null $publicKeyPath
	 * @return boolean
	 */
	public function verifyLicense($licensePath, $publicKeyPath = null)
	{
		$license = json_decode(base64_decode(file_get_contents($licensePath)));
		if (!$license) {
			return false;
		}

		$data      = $license->data;
		$signature = base64_decode($license->signature);

		$verify = openssl_verify($data, $signature, $this->getPublicKey($publicKeyPath));
		
		$this->freePublicKey();

		return $verify == 1;
	}

	/**
	 * Get the data from a license.
	 *
	 * @param  string      $licensePath
	 * @param  string|null $publicKeyPath
	 * @return array|null
	 */
	public function getLicenseData($licensePath, $publicKeyPath = null)
	{
		if ($this->verifyLicense($licensePath, $publicKeyPath)) {
			$license = json_decode(base64_decode(file_get_contents($licensePath)));
			if (!$license) {
				return;
			}

			$data = json_decode($license->data, true);
			if (is_array($data)) {
				return $data;
			}
		}
	}

	/**
	 * Get the public key.
	 *
	 * @param  string|null $path
	 * @return string
	 */
	protected function getPublicKey($path = null)
	{
		if (!$path) {
			$path = __DIR__ . '/../resources/public.key';
		}

		$this->freePublicKey();

		return $this->publicKey = openssl_pkey_get_public(file_get_contents($path));
	}

	/**
	 * Free up a public key.
	 *
	 * @return boolean
	 */
	protected function freePublicKey()
	{
		if ($this->publicKey) {
			openssl_free_key($this->publicKey);

			$this->publicKey = null;

			return true;
		}

		return false;
	}
}
