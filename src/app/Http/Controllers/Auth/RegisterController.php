<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request, CreateNewUser $creator)
    {
        $user = $creator->create($request->validated());

        event(new Registered($user));

        auth()->login($user);

        return redirect('/email/verify');
    }
}
