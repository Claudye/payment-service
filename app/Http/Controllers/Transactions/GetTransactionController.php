<?php

namespace App\Http\Controllers\Transactions;

use App\Helpers\Resp;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;

class GetTransactionController extends Controller
{
    public function index() {}

    public function show(Transaction $transaction)
    {
        return Resp::readed(TransactionResource::make($transaction));
    }
}
