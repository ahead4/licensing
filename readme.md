# Licensing

## Creating a new keypair.
Before you're able to create a license, you must create a private and public keypair. The private key should be kept in secure storage and should only be used to generate new licenses. The public key will need to be generated alongside the license key in order to perform verification on the license data.
```php
<?php

$licensing = new \Ahead4\Licensing\Licensing;
$licensing->createKeypair('/path/to/store/private.key', '/path/to/store/public.key');

?>
```

## Creating a license.
A license is a simple array of data, which gets signed by the private key you previously created. This signature is then embedded into the license to allow you to verify it later to ensure the license has not been modified or tampered with in any way.
```php
<?php

$licensing = new \Ahead4\Licensing\Licensing;
$data = [
	'licensee' => [
		'name' => 'Joe Bloggs',
	],
	'features' => [
		'gallery',
		'shop',
	],
];
$license = $licensing->createLicense($data, '/path/to/private.key');

?>
```

## Verifying a license.
A license can be verified by providing the path to the license and the path to the public key.
```php
<?php

$licensing = new \Ahead4\Licensing\Licensing;
$verified = $licensing->verifyLicense('/path/to/license.txt', '/path/to/public.key');

?>
```

## Getting the data from a license.
In most cases you will simply want to get back the original array of data that you created the license with. **NOTE:** You do not need to verify the license before calling this method as it will automatically perform this check and will instead return null if the license is invalid.
```php
<?php

$licensing = new \Ahead4\Licensing\Licensing;
$data = $licensing->getLicenseData('/path/to/license.txt', '/path/to/public.key');

?>
```