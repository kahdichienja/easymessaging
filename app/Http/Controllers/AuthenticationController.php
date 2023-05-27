<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\TwilioService;
use Illuminate\Validation\Rule;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    protected $twilioService;

    public function __construct()
    {
        $this->twilioService = new TwilioService();
    }
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


        // TODO: implement send otp to user either through email or phone
        $this->twilioService->sendSms($user->phone, "OTP CODE: {$otpCode}");

        $user->save();

        return  $this->success([
            'message' => 'Two-factor authentication enabled',
            'code' => $otpCode
        ]);
    }
    /**
     * Enable 2FA for the user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticateWithTwoFactor(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        if($user->google2fa_enabled){

            $google2fa = new Google2FA();
            $secretKey = $google2fa->generateSecretKey();
            $user->google2fa_secret =  $secretKey;


            // Generate an OTP code based on the secret key
            $otpCode = $google2fa->getCurrentOtp($secretKey);


            // TODO: implement send otp to user either through email or phone
            // $this->twilioService->sendSms($user->phone, "OTP CODE: {$otpCode}");

            $user->save();
            return  $this->success([
                'message' => 'Verification Code sent to you phone',
                'code' => $otpCode,
                'access_token' => $token,
            ]);
        }else{
            return  $this->success([
                'message' => 'Auth success',
                'code' => "",
                'access_token' => $token,
            ]);

        }

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
        $user->google2fa_enabled = false;
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

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->success([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);

            // return $this->success(['message' => 'Two-factor authentication enabled']);
        }

        return $this->error('Invalid verification code', 422);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            // 'password' => 'required',
        ]);

        $user = User::where('phone', $request->phone)->first();

        // if (!$user || !Hash::check($request->password, $user->password)) {
        //     return response()->json([
        //         'message' => 'Invalid phone or password',
        //     ], 401);
        // }

        // $token = $user->createToken('auth_token')->plainTextToken;

        return $this->authenticateWithTwoFactor($request);

        // return $this->success([
        //     'access_token' => $token,
        //     'token_type' => 'Bearer',
        // ]);
    }
    public function register(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'phone' => 'starts_with:+|unique:users|min:13|max:13|required|regex:/^([0-9\s\-\+\(\)]*)$/',
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
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'profile_picture' => $profilePicturePath,
        ]);



        // 2FA.
        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();
        $user->google2fa_secret =  $secretKey;


        // Generate an OTP code based on the secret key
        $otpCode = $google2fa->getCurrentOtp($secretKey);

        $user->save();

        // TODO: implement send otp to user either through email or phone
        $this->twilioService->sendSms($user->phone, "OTP CODE: {$otpCode}");

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
            'phone' => 'sometimes|starts_with:+|unique:users|min:13|max:13|required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . Auth::id(),
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Update the user's profile information
        if (isset($validatedData['phone'])) {
            $user->phone = $validatedData['phone'];
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
