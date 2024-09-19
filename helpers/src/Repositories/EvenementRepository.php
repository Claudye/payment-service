<?php

namespace Helpers\Repositories;

use App\Models\Evenement;
use App\Models\Payment;
use Helpers\Repositories\AbstractRepository;

class EvenementRepository extends AbstractRepository
{
    protected $counts = ["sponsorings"];
    protected $modelName =  Evenement::class;
    public function data($model, bool $private = false): array

    {
        $data = [
            'id' => $model->id,
            'name' => $model->name,
            'description' => $model->description,
            'startDate' => $model->startDate,
            'endDate' => $model->endDate,
            'contact' => [
                'phone' => $model->contact_phone,
                'email' => $model->contact_email,
            ],
            "location" => $model->location,
            "slug" => $model->slug,
            "image" => $model->image,
            "user" => $model->user,
            "qrcode" => $model->qrcode,
            "views_count" => $model->views_count,
            "sponsorings_count" => $model->sponsorings_count,
        ];

        if (intval($model->expectation) > 0) {

            $data["expectation"] = $model->expectation;
            $data["badge_min"] = $model->badge_min;
            $data["badge_pro_min"] = $model->badge_pro_min;
            $data["amount_sum"] = 0;
        }

        return $data;
    }

    public function findBySlug($slug, ?callable $callback = null): array
    {
        $evenement = $this->model
            ->where(['slug' => $slug])
            ->withCount('sponsorings')
            ->first();

        if (!$evenement) {
            if ($callback) {
                return $callback($slug);
            }
            return [];
        }
        return $this->transform($evenement);
    }

    public function myLatests($perPage = 10)
    {
        $evenements = $this->model->latest()
            ->paginate($perPage);

        $evenements->each(function (Evenement $evenement) {
            $evenement->setAttribute("amount_sum", 0);
            $evenement->setAttribute("sponsorings_count", 0);
        });
        return $evenements;
    }
}
