<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        return redirect()->route('register.toko.create');
    }
}
