<?php

namespace App\Helpers;

use App\Exceptions\GlobalException;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ApiExceptionRender
{
    public function routeNotFound(RouteNotFoundException $e, Request $request)
    {
        if ($e->getMessage() == "Route [login] not defined.") {
            return Resp::error("Vous n'êtes pas connecté(e) ou votre session a expiré", statuscode: 401);
        }
        return Resp::error("Url de requête introuvable", statuscode: 404);
    }

    public function notFound()
    {
        return Resp::error("Aucune donnée trouvée", statuscode: 404);
    }

    public function unauthorized()
    {
        return Resp::error("Vous n'êtes pas autorisé (e) à accéder à cette page", statuscode: 403);
    }


    public function validation(ValidationException $exception, Request $request)
    {
        return Resp::error("La validation a échoué", $exception->validator->errors(), statuscode: 400);
    }

    public function authentication(AuthenticationException $e, Request $request)
    {
        return Resp::error("Vous n'êtes pas connecté(e) ou votre session a expiré", statuscode: 401);
    }

    public function global(GlobalException $e, Request $request)
    {
        return Resp::error($e->getMessage());
    }
}
