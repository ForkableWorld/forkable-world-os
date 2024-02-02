<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files;
use App\DataSources\CredentialStorage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use LogicException;
class QMEncrypt {
	/**
	 * @param string $encryptedFilePath
	 * @return string
	 */
	public static function decryptFile(string $encryptedFilePath): string{
		$fp = fopen($encryptedFilePath, 'rb');
		stream_filter_append($fp, self::getEncryptionFilter(), STREAM_FILTER_READ, self::options());
		$data = rtrim(stream_get_contents($fp));
		fclose($fp);
		@unlink($encryptedFilePath);
		// save decrypted file data
		$decryptedFilePath = str_replace(".enc", "", $encryptedFilePath);
		$decryptedFile = fopen($decryptedFilePath, "wb");
		fwrite($decryptedFile, base64_decode($data));
		fclose($decryptedFile);
		return $decryptedFilePath;
	}
	/**
	 * @param string $fileName
	 * @param UploadedFile $uploadedFile
	 * @return string
	 */
	public static function encryptFile(string $fileName, UploadedFile $uploadedFile): string{
		$fp = fopen(storage_path('app/' . $fileName . '.enc'), 'wb');
		stream_filter_append($fp, self::getEncryptionFilter(), STREAM_FILTER_WRITE, self::options());
		// Get File Content
		try {
			$fileContent = $uploadedFile->get();
		} catch (FileNotFoundException $e) {
			le($e);
		}
		fwrite($fp, base64_encode($fileContent));
		fclose($fp);
		return storage_path('app/' . $fileName . '.enc');
	}
	/**
	 * @return string
	 */
	private static function getEncryptionFilter(): string{
		if(version_compare(phpversion(), '7.0.0', '>=')){
			$filterName = 'string.rot13';
		} else{
			$filterName = 'mcrypt.tripledes';
		}
		return $filterName;
	}
	/**
	 * @return array
	 */
	private static function options(): array{
		//$passphrase = \App\Utils\Env::get('ENCRYPTION_KEY');
		$passphrase = CredentialStorage::getEncryptionKey();
		$iv = substr(md5('iv' . $passphrase, true), 0, 8);
		$key = substr(md5('pass1' . $passphrase, true) . md5('pass2' . $passphrase, true), 0, 24);
		$opts = [
			'iv' => $iv,
			'key' => $key,
		];
		return $opts;
	}
	/**
	 * @return Encrypter
	 */
	public static function encryptor(): Encrypter{
		$key = config('app.key');
		if(Str::startsWith($key, 'base64:')){
			$key = base64_decode(substr($key, 7));
		}
		$encrypter = new Encrypter($key, config('app.cipher'));
		return $encrypter;
	}
}
