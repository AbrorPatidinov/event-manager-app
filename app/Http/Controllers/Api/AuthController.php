<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
    * Handle user registration.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function register(Request $request) 
    {
        try {
            // Validate incoming request data
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
            ]); 
    
            // Create new user
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
    
            // Generate authentication token
            $token = $user->createToken('auth_token')->plainTextToken;
    
            // Return success response
            return response()->json([
                'success' => true,
                'errorMessage' => '',
                'token' => $token,
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            // Handle validation error for duplicate email
            if ($e->validator->errors()->has('email')) {
                return response()->json([
                    'success' => false,
                    'errorMessage' => 'User already registered',
                    'token' => '',
                ], Response::HTTP_CONFLICT); // HTTP 409 Conflict
            }
    
            // If another validation error occurs
            return response()->json([
                'success' => false,
                'errorMessage' => 'Validation failed. Please try again.',
                'token' => '',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // HTTP 422 Unprocessable Entity
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'success' => false,
                'errorMessage' => 'Registration failed. Please try again.',
                'token' => '',
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // HTTP 500 Internal Server Error
        } 
    }

    /**
    * Handle user login.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function login(Request $request)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to find the user by email
        $user = User::where('email', $validated['email'])->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'errorMessage' => 'Invalid credentials.',
                'token' => '',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Generate authentication token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return success response
        return response()->json([
            'success' => true,
            'errorMessage' => '',
            'token' => $token,
        ], Response::HTTP_OK);
    }

    /**
    * Handle user logout.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function logout(Request $request)
    {
        // Check if the user is authenticated and revoke the token
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
    
            return response()->json([
                'success' => true,
                'errorMessage' => '',
                'token' => '',
            ], Response::HTTP_OK); // HTTP 200 OK
        }
    
        // If the token is invalid, return an error response
        return response()->json([
            'success' => false,
            'errorMessage' => 'Invalid token or user not authenticated.',
            'token' => '',
        ], Response::HTTP_UNAUTHORIZED); 
    }
    
}
