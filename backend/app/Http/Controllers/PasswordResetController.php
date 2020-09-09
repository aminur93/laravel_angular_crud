<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordmail;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use DB;

class PasswordResetController extends Controller
{
    public function sendEmail(Request $request)
    {
        if(!$this->validateEmail($request->email))
        {
            return $this->faildResponse();
        }

        $this->send($request->email);

        return $this->successResponse();
    }

    public function send($email)
    {
        $token = $this->createToToken($email);

        Mail::to($email)->send(new ResetPasswordmail($token));
    }

    public function createToToken($email)
    {
        $oldToken = DB::table('password_resets')->where('email',$email)->first();

        if($oldToken)
        {
            return $oldToken;
        }

        $token = Str::random(60);
        $this->saveToken($token,$email);

        return $token;
    }

    public function saveToken($token,$email)
    {
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }

    public function validateEmail($email)
    {
        return !!User::where('email',$email)->first();
    }

    public function faildResponse()
    {
        return response()->json([
            'error' => 'Email is not found our Database'
        ], Response::HTTP_NOT_FOUND);
    }

    public function successResponse()
    {

        return response()->json([
            'data' => 'Reset Email Send Successfully! Please Check Your Email Inbox'
        ], Response::HTTP_OK);
    }

  
}
