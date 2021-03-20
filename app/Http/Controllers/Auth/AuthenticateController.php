<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticateController extends Controller
{
    /**
     * @param Request $request
     * @return array
     * @throws ValidationException
     */
    public function login(Request  $request): array
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        /** @var User $user */
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return ["token" => $user->createToken($request->email)->plainTextToken];
    }

    public function destroy(Request $request)
    {

        // Revoke the token that was used to authenticate the current request...
        /** @var User $user */
        $user = $request->user();
        /** @var \Laravel\Sanctum\PersonalAccessToken $accessToken */
        $accessToken = $user->currentAccessToken();

        $accessToken->delete();


        return response( null,204);

    }
}
