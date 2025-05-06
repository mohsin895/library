<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\Response;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|regex:/^[A-Za-z]/',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
              
            ]);
    
            $existingUser = User::where('email', trim($validatedData['email']))->first();
    
            if (!$existingUser) {
              
                $user = new User();
                $user->name = $validatedData['name'];
                $user->email = strtolower(trim($validatedData['email']));
                $user->password = Hash::make($validatedData['password']);
                $user->email_verified_at =Carbon::now();
                $user->created_at = Carbon::now();
    
                if ($user->save()) {
                   
    
                    DB::commit();
                    $msg = 'Registration successfully. Please login your account.';
                    return Response::successResponse($msg, $user);
                } else {
                    DB::rollBack();
                    $msg = 'Failed to sign up. Please try again.';
                    return Response::failedResponse($msg, $user);
                }
            } else {
                DB::rollBack();
                $msg = 'Customer is already registered.';
                return Response::failedResponse($msg);
            }
        } catch (Exception $err) {

            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }

    public function login(Request $request):JsonResponse
    {
    
        $userInfo = User::where('email', $request->email) 
                        ->first();
    
        if ($userInfo) {
            if($userInfo->email_verified_at	== null){
                $msg = 'Your Email  is not verified.Plese verify your email';
                return Response::successResponse($msg);

            }else{
              
                    if (Hash::check($request->password, $userInfo->password)) {
                        $token = $userInfo->createToken('auth_token')->plainTextToken;
        
                        return response()->json([
                            'status' => true,
                            'token_type' => 'Bearer',
                            'token' => $token,
                            'message' => 'Login successfully',
                           
                            'dataInfo' => [
                                'id' => $userInfo->id,
                                'name' => $userInfo->name,
                                'email' => $userInfo->email,
                                'created_at' => $userInfo->created_at,
                               
                            ],
                        ]);
                    } else {
                        
                        $msg = 'Wrong credentials. Please enter valid credentials.';
                        return Response::successResponse($msg);
                    }
                

            }
           
        } else {
           
            $msg = 'Invalid credentials.';
            return Response::successResponse($msg);
        }
    }
}
