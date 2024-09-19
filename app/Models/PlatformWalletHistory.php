<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PlatformWalletHistory
 *
 * Représente l'historique des transactions du wallet de la plateforme.
 *
 * @property int $id
 * @property string $uuid
 * @property int $platform_wallet_id
 * @property string $type
 * @property float $amount
 * @property string|null $note
 * @property string $status
 * @property int|null $wallet_history_id
 * @property int|null $transaction_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class PlatformWalletHistory extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'platform_wallet_id',
        'type',
        'amount',
        'note',
        'status',
        'wallet_history_id',
        'transaction_id',
    ];

    /**
     * Les attributs à convertir en types natifs.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'double',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relation avec le modèle PlatformWallet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platformWallet()
    {
        return $this->belongsTo(PlatformWallet::class);
    }

    /**
     * Relation avec le modèle WalletHistory.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function walletHistory()
    {
        return $this->belongsTo(WalletHistory::class);
    }

    /**
     * Relation avec le modèle Transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
