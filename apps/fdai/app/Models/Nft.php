<?php

namespace App\Models;

use App\Nfts\Events\Tokenized;
use App\Nfts\Events\Untokenized;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
/**
 * App\Models\Nft
 *
 * @property int $id
 * @property int $user_id user_id
 * @property string $tokenizable_type
 * @property int $tokenizable_id
 * @property string $chain
 * @property string $token_address
 * @property string $token_id
 * @property string $title
 * @property string $description
 * @property string $social_media_url
 * @property int $quantity
 * @property string $minting_address
 * @property string $file_url
 * @property string $ipfs_cid
 * @property string $tx_hash
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $client_id
 * @property-read Model|\Eloquent $tokenizable
 * @property-read \App\Models\User $tokenizer
 * @property-read \App\Models\User $user
 * @method static Builder|Nft newModelQuery()
 * @method static Builder|Nft newQuery()
 * @method static Builder|Nft query()
 * @method static Builder|Nft whereChain($value)
 * @method static Builder|Nft whereClientId($value)
 * @method static Builder|Nft whereCreatedAt($value)
 * @method static Builder|Nft whereDescription($value)
 * @method static Builder|Nft whereFileUrl($value)
 * @method static Builder|Nft whereId($value)
 * @method static Builder|Nft whereIpfsCid($value)
 * @method static Builder|Nft whereMintingAddress($value)
 * @method static Builder|Nft whereQuantity($value)
 * @method static Builder|Nft whereSocialMediaUrl($value)
 * @method static Builder|Nft whereTitle($value)
 * @method static Builder|Nft whereTokenAddress($value)
 * @method static Builder|Nft whereTokenId($value)
 * @method static Builder|Nft whereTokenizableId($value)
 * @method static Builder|Nft whereTokenizableType($value)
 * @method static Builder|Nft whereTxHash($value)
 * @method static Builder|Nft whereUpdatedAt($value)
 * @method static Builder|Nft whereUserId($value)
 * @method static Builder|Nft withType(string $type)
 * @mixin \Eloquent
 */
class Nft extends BaseModel
{
	public const TABLE = 'nfts';
    protected $guarded = [];

    protected $dispatchesEvents = [
        'created' => Tokenized::class,
        'deleted' => Untokenized::class,
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = 'nfts';

        parent::__construct($attributes);
    }

    protected static function boot()
    {
        parent::boot();

        self::saving(function ($tokenize) {
            $tokenize->user_id = $tokenize->user_id ?: auth()->id();
        });
    }

    public function tokenizable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tokenizer()
    {
        return $this->user();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithType(Builder $query, string $type)
    {
        return $query->where('tokenizable_type', app($type)->getMorphClass());
    }
}
