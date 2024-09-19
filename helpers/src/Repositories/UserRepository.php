<?php

namespace Helpers\Repositories;

use App\Models\User;

class UserRepository extends AbstractRepository
{
    protected $modelName = User::class;

    /**
     * Summary of data
     * @param \App\Models\User $user
     * @param bool $private
     * @return array
     */
    public function data($user, bool $private = false): array
    {
        $json = [
            "id" => $user->id,
            "socials" => $user->socials,
            "viewsCount" => $user->views_count ?? 0,
            "name" => $user->name ?? $user->username,
            "photo" => $user->photo ?? asset('storage/avatars/avatar.png'),
            "description" => $user->bio,
            "profession" => $user->profession,
            "username" => $user->username,
        ];

        if ($private) {
            $json['email'] = $user->email;
            $json['phone'] = $user->phone;
            $json['completed'] = $user->isCompleted();
        }

        return $json;
    }

    public function whereUnique($idOrUsername, ?callable $callback = null)
    {
        $user = is_numeric($idOrUsername) ?
            $this->model->find($idOrUsername)
            : $this->model
            ->where(['username' => $idOrUsername])
            ->first();


        if (!$user) {
            if ($callback) {
                return $callback($idOrUsername);
            }
            return null;
        }
        return $this->transform($user);
    }
}
