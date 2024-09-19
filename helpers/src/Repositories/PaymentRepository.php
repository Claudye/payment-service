<?php

namespace Helpers\Repositories;

use App\Models\User;
use App\Models\Payment;
use App\Models\Evenement;
use App\Models\Withdraw;

class PaymentRepository extends AbstractRepository
{
    protected $modelName = Payment::class;
    public function data($model, bool $private = false): array
    {
        return [];
    }

    /**
     * Summary of earned
     * @param \App\Models\User $user
     * @param mixed $select
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function earned(User $user, $select = ["*"], array $eventIds = [])
    {
        $eventIds = !empty($eventIds) ? $eventIds : Evenement::select('id', 'user_id')
            ->where("user_id", $user->id)
            ->pluck("id")
            ->all();

        $select = $select[0] == "*" ? [] : $select;
        return $this->getNewInstance($select + ["user_id", "evenement_id"])
            ->whereIn("evenement_id", $eventIds)
            ->orWhere("user_id", $user->id);
    }

    public function withdraws(User $user)
    {
        return Withdraw::filter(["user_id" => $user->id]);
    }

    public function retrived(User $user, array $select = [])
    {
        return $this->withdraws($user)
            ->select($select + ['user_id', 'status', 'amount'])
            ->procced();
    }
}
