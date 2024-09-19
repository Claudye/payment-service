<?php

namespace Helpers\Repositories;

use App\Models\Service;
use App\Models\Payment;
use Helpers\Repositories\AbstractRepository;

class ServiceRepository extends AbstractRepository
{
    protected $modelName =  Service::class;
    public function data($model, bool $private = false): array

    {
        $data = [
            'id' => $model->id,
            'name' => $model->name,
            "slug" => $model->slug,
            'description' => $model->description,
            "image" => $model->image,
            "active" => $model->active,
        ];

        return $data;
    }

    public function findBySlug($slug, ?callable $callback = null): array
    {
        $service = $this->model
            ->where(['slug' => $slug])
            ->first();

        if (!$service) {
            if ($callback) {
                return $callback($slug);
            }
            return [];
        }
        return $this->transform($service);
    }

}

