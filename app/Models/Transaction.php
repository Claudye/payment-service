<?php

namespace App\Models;

use Eloquent;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Transaction
 *
 * @property int $id
 * @property string $payable_type
 * @property string $payer_uuid
 * @property float $amount
 * @property float $commission
 * @property Currency $currency
 * @property int|null $user_id
 * @property int|string|null $transaction_id
 * @property Aggregator $aggregator
 * @property  string | null $receiver_uuid
 * @property string $uuid
 * @property string|null $payment_trx_id
 * @property string|null $note
 * @property string|null $request
 * @property string|null $perform_time
 * @property string|null $refund_time
 * @property int|null $currency_id
 * @property string $status
 * @property string $type
 * @property string $status_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read User|null $user
 * @property-read self|null $parent
 * @property-read Collection|self[] $children
 * @method static TransactionFactory factory(...$parameters)
 * @method static Builder|self filter($array = [])
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereDeletedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereNote($value)
 * @method static Builder|self wherePayableId($value)
 * @method static Builder|self wherePayableType($value)
 * @method static Builder|self wherePaymentSysId($value)
 * @method static Builder|self wherePaymentTrxId($value)
 * @method static Builder|self wherePerformTime($value)
 * @method static Builder|self whereAmount($value)
 * @method static Builder|self whereRefundTime($value)
 * @method static Builder|self whereStatus($value)
 * @method static Builder|self whereStatusDescription($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereUserId($value)
 * @mixin Eloquent
 */
class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    const STATUS_PROGRESS   = 'progress';
    const STATUS_SPLIT       = 'split';
    const STATUS_PAID       = 'paid';
    const STATUS_CANCELED   = 'canceled';
    const STATUS_REJECTED   = 'rejected';
    const STATUS_REFUND     = 'refund';

    const STATUSES = [
        self::STATUS_PROGRESS   => self::STATUS_PROGRESS,
        self::STATUS_PAID       => self::STATUS_PAID,
        self::STATUS_CANCELED   => self::STATUS_CANCELED,
        self::STATUS_REJECTED   => self::STATUS_REJECTED,
        self::STATUS_REFUND     => self::STATUS_REFUND,
    ];

    const REQUEST_WAITING = 'waiting';
    const REQUEST_PENDING = 'pending';
    const REQUEST_APPROVED = 'approved';
    const REQUEST_REJECT = 'reject';

    const REQUESTS = [
        self::REQUEST_WAITING,
        self::REQUEST_PENDING,
        self::REQUEST_APPROVED,
        self::REQUEST_REJECT,
    ];

    const TYPE_WITHDRAW = "withdraw";
    const TYPE_TRANSFERT = "transfer";
    const TYPE_PAYMENT = "payment";
    const TYPE_FUNDING = "funding";

    const TYPES = [
        self::TYPE_WITHDRAW,
        self::TYPE_TRANSFERT,
        self::TYPE_PAYMENT,
        self::TYPE_FUNDING,
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aggregator(): BelongsTo
    {
        return $this->belongsTo(Aggregator::class);
    }

    public function scopeFilter($query, $filter = [])
    {
        return $query
            ->when(data_get($filter, 'request'), function (Builder $query, $request) {
                $query->where('request', $request);
            })
            ->when(isset($filter['deleted_at']), fn($q) => $q->onlyTrashed())
            ->when(data_get($filter, 'model') == 'wallet', fn($q) => $q->where(['payable_type' => Wallet::class]))
            ->when(data_get($filter, 'user_id'), fn($q, $userId) => $q->where('user_id', $userId))
            ->when(data_get($filter, 'status'), fn($q, $status) => $q->where('status', $status));
    }

    public function notPaid()
    {
        return $this->status !== self::STATUS_PAID;
    }

    public function scopePaid($query)
    {
        return $query->filter('status', self::STATUS_PAID);
    }
}
