<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    /**
     * Enable 2FA for the user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function enableTwoFactorAuthentication()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();
        $user->google2fa_secret =  $secretKey;


        // Generate an OTP code based on the secret key
        $otpCode = $google2fa->getCurrentOtp($secretKey);


        // TODO: implement send otp to user either through email or phone in the name field i the users table.

        $user->save();

        return  $this->success([
            'message' => 'Two-factor authentication enabled',
            'code' => $otpCode
        ]);
    }

    /**
     * Disable 2FA for the user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function disableTwoFactorAuthentication()
    {
        $user = Auth::user();
        $user->google2fa_secret = null;
        $user->save();

        return $this->success(['message' => 'Two-factor authentication disabled']);
    }

     /**
     * Verify the 2FA setup with the provided code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyTwoFactorSetup(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $user = Auth::user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);

        if ($valid) {
            $user->google2fa_enabled = true;
            $user->save();

            return $this->success(['message' => 'Two-factor authentication enabled']);
        }

        return $this->error('Invalid verification code', 422);
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('name', $request->name)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid username or password',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
    public function register(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_picture' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle the profile picture upload, if provided
        if ($request->hasFile('profile_picture')) {
            $profilePicture = $request->file('profile_picture');
            $profilePicturePath = $profilePicture->store('profile_pictures', 'public');
        } else {
            $profilePicturePath = null;
        }

        // Create a new user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'profile_picture' => $profilePicturePath,
        ]);

        // Optionally, you can perform additional actions after registration, such as sending a welcome email or setting up default settings for the user.

        // Return a response indicating successful registration
        return $this->success(['message' => 'Registration successful'],'Registration successful', 201);
    }


    public function userProfile(Request $request)
    {
        // Get the authenticated user

        $user= User::where('id', Auth::user()->id)->with('settings')->first();

        return $this->success($user, 'Profile updated successfully.', 200);

    }
    public function updateProfile(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . Auth::id(),
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Update the user's profile information
        if (isset($validatedData['name'])) {
            $user->name = $validatedData['name'];
        }

        if (isset($validatedData['email'])) {
            $user->email = $validatedData['email'];
        }

        // Check if a new profile picture is uploaded
        if ($request->hasFile('profile_picture')) {
            $profilePicture = $request->file('profile_picture');
            $fileName = time() . '_' . $profilePicture->getClientOriginalName();
            $profilePicture->storeAs('profile_pictures', $fileName);
            $user->profile_picture = $fileName;
        }

        // Save the updated user profile
        $user->update();

        // Optionally, you can redirect or return a response with a success message
        return $this->success($user, 'Profile updated successfully.', 201);
    }



}
