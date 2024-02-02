<?php

namespace App\Models;

use Elliptic\EC;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use kornrunner\Keccak;
/**
 * App\Models\Nonce
 *
 * @property int $id
 * @property string|null $nonce
 * @property string|null $content
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OAClient $client
 * @property mixed|null $calculated
 * @property-read array $invalid_record_for
 * @property-read string $name
 * @property mixed|null $raw
 * @property-read string $report_title
 * @property-read array|mixed|string|string[]|null $rule_for
 * @property-read array $rules_for
 * @property-read string $subtitle
 * @property-read string $title
 * @property-read \App\Models\OAClient $oa_client
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, int $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|Nonce newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Nonce newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Nonce query()
 * @method static \Illuminate\Database\Eloquent\Builder|Nonce whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nonce whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nonce whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nonce whereNonce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nonce whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nonce whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Nonce extends BaseModel
{
	use HasFactory;
	public const TABLE = "nonces";
	protected $table = 'nonces';
	public $fillable = [
		'nonce', 'content', 'type'
	];
	public function verifyAndDelete($signature, $address){
		try {
			$hash = Keccak::hash(sprintf("\x19Ethereum Signed Message:\n%s%s", 
			                             strlen($this->nonce), $this->nonce), 256);
		} catch (Exception $e) {le($e);}
		$sign = [
			'r' => substr($signature, 2, 64),
			's' => substr($signature, 66, 64),
		];
		$j = ord(hex2bin(substr($signature, 130, 2))) - 27;
		if ($j != ($j & 1)) {return false;}
		try {$pubkey = (new EC('secp256k1'))->recoverPubKey($hash, $sign, $j);
		} catch (Exception $e) {le($e);}
		try {$derived_address = '0x'.substr(Keccak::hash(substr(hex2bin($pubkey->encode('hex')), 1), 256), 24);
		} catch (Exception $e) {le($e);}
		$this->delete();
		return (Str::lower($address) === $derived_address);
	}
}
