<?php

namespace App\Services;

use App\Models\Support;
use App\Traits\PayableCalcule;
use Illuminate\Database\Eloquent\Collection;

class SupportService extends CoreService
{
    use PayableCalcule;
    public function getModelClass()
    {
        return Support::class;
    }
}
