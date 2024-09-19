<?php

namespace App\Services\Users;

use App\Models\Contributor;
use App\Services\CoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class ContributorService extends CoreService
{

    public function getModelClass()
    {
        return Contributor::class;
    }

    public function create(array $data): Model
    {
        $anonyme = data_get($data, "anonymous", false);

        $name = $anonyme ? "Anonyme" : data_get($data, "lastname") . " " . data_get($data, "firstname");
        return parent::create([
            "lastname" => data_get($data, "lastname"),
            "firstname" => data_get($data, "firstname"),
            "anonyme" => $anonyme,
            "email" => data_get($data, "email"),
            "phone" => data_get($data, "phone"),
            'user_id' => $data['user_id'] ??  Auth::id(),
            "name" =>  str($name)->length() ? $name : null,
        ]);
    }
}
