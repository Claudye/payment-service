<?php

namespace App\Models;

use Database\Factories\WalletHistoryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\WalletHistory
 *
 * @property int $id
 * @property string $uuid
 * @property string $wallet_uuid
 * @property int|null $transaction_id
 * @property Transaction $transaction
 * @property string $type
 * @property float $amount
 * @property float $amount_rate
 * @property string|null $note
 * @property string $status
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $author
 * @property-read User|null $user
 * @property-read Wallet|null $wallet
 * @method static WalletHistoryFactory factory(...$parameters)
 * @method static Builder|WalletHistory newModelQuery()
 * @method static Builder|WalletHistory newQuery()
 * @method static Builder|WalletHistory query()
 * @method static Builder|WalletHistory whereCreatedAt($value)
 * @method static Builder|WalletHistory whereCreatedBy($value)
 * @method static Builder|WalletHistory whereId($value)
 * @method static Builder|WalletHistory whereNote($value)
 * @method static Builder|WalletHistory whereAmount($value)
 * @method static Builder|WalletHistory whereStatus($value)
 * @method static Builder|WalletHistory whereTransactionId($value)
 * @method static Builder|WalletHistory whereType($value)
 * @method static Builder|WalletHistory whereUpdatedAt($value)
 * @method static Builder|WalletHistory whereUuid($value)
 * @method static Builder|WalletHistory whereWalletUuid($value)
 * @mixin Eloquent
 */
class WalletHistory extends Model
{
    use HasFactory,  SoftDeletes;

    protected $guarded = ['id'];

    const PROCESSED = 'processed';
    const REJECTED  = 'rejected';
    const CANCELED  = 'canceled';

    const TYPES     = [
        'topup',
        'withdraw',
    ];

    const STATUTES = [
        self::PROCESSED => self::PROCESSED,
        self::REJECTED  => self::REJECTED,
        self::CANCELED  => self::CANCELED,
    ];


    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
