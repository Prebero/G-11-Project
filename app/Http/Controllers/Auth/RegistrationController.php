<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationEmail;
use App\Models\User;
use App\Notifications\RegistrationNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    /**
     * Register user.
     *
     * @return json
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:4',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'age' => 'required|integer',
            'gender' =>  ['required', 'string', Rule::in(['female', 'male'])],
            'community_id' => 'required|exists:communities,id'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'age' => $request->age,
            'gender' =>  $request->gender,
            'community_id' => $request->community_id,
        ]);

        try {
            $details = [
                'message' => "Dear $user->name, your  regristartion was successfully registered ",
            ];
            
            Mail::to("$user->email")->send(new RegistrationEmail($details));
        } catch (Exception $e) {
            //no need to return an error because already the registration has been completed
        }

        return response()->json([
            'success' => true,
            'message' => 'User registered succesfully, Use Login method to receive token.',
            'user' => $user->refresh(),
        ], 200);
    }
}
