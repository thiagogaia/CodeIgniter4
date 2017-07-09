<?php namespace CodeIgniter\Encryption\Handlers;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use \Sodium;

class SodiumHandler extends BaseHandler
{
	// --------------------------------------------------------------------

	/**
	 * Initialize Sodium.
	 * 
	 * Cipher is ignored, and only GCM mode is available.
	 *
	 * @param	array	$params	Configuration parameters
	 * @return	void
	 */
	public function __construct($params = null)
	{
		parent::__construct();

		$this->cipher = 'N/A';
		$this->mode = 'N/A';

		if (sodium_init())
		{
			$this->logger->info('Encryption: Sodium initialized.');
		}
		else
		{
			$this->logger->error('Encryption: Unable to initialize Sodium.');
		}
	}

	/**
	 * Encrypt
	 *
	 * @param	string	$data	Input data
	 * @param	array	$params	Input parameters
	 * @return	string
	 */
	public function encrypt($data, array $params = null)
	{
		// allow key to be over-ridden
		$key = empty($params['key']) ? $this->key : $params['key'];

		$nonce = randombytes_buf(CRYPTO_SECRETBOX_NONCEBYTES);

		$ciphertext = crypto_secretbox($data, $nonce, $key);

		if ($ciphertext === false)
		{
			return false;
		}

		return $nonce . $ciphertext;
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt
	 *
	 * @param	string	$data	Encrypted data, with nonce pre-fixed
	 * @param	array	$params	Input parameters
	 * @return	string
	 */
	public function decrypt($data, array $params = null)
	{
		// allow key to be over-ridden
		$key = empty($params['key']) ? $this->key : $params['key'];

		// split the data into nonce & ciphertext
		$nonce = self::substr($data, 0, CRYPTO_SECRETBOX_NONCEBYTES);
		$data = self::substr($data, CRYPTO_SECRETBOX_NONCEBYTES);

		$plaintext = crypto_secretbox_open($data, $nonce, $key);

		if ($plaintext === false)
		{
			throw new EncryptionException("Bad ciphertext");
		}

		return $plaintext;
	}

	// --------------------------------------------------------------------

	/**
	 * Cipher alias
	 *
	 * Tries to translate cipher names as appropriate for this handler
	 *
	 * @param	string	$cipher	Cipher name
	 * @return	void
	 */
	protected function cipherAlias(&$cipher)
	{
		$cipher = 'N/A';
	}

}
