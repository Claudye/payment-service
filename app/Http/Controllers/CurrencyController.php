<?php

namespace App\Http\Controllers;

use App\Helpers\Resp;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Http\Resources\CurrencyResource;
use LaravelHooks\Traits\HasControllerHooks;

class CurrencyController extends Controller
{
    use HasControllerHooks;

    public function index(Request $request)
    {
        return CurrencyResource::collection(Currency::all());
    }

    public function useHooks()
    {
        $this->beforeCalling(['index'], function ($request) {
            $request->merge([
                'username' => "Claude"
            ]);
        });


        $this->afterCalling(['index'], function ($result) {
            return Resp::readed($result);
        });
    }
}
