<?php

namespace App\Http\Controllers\Api;

use Throwable;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Api\Auth\RegisterRequest;

class AuthController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Handle register user
     *
     * @param RegisterRequest $request
     * 
     * @return mixed
     */
    public function register(RegisterRequest $request)
    {
        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => null,
                'verification_token' => Str::random(40) . time(),
            ];
            // Create a new user
            $user = $this->user->create($data);
            // Send verification email
            $this->sendVerificationEmail($user);

            return successResponse(Response::HTTP_CREATED, [], __('common.msg_register_success'));
        } catch (Throwable $thow) {
            Log::error('------ REGISTER ERROR ------ ' . $thow->getMessage());

            return failResponse(Response::HTTP_BAD_REQUEST, [], __('common.msg_register_error'));
        }
    }

    /**
     * Handle send main verification email
     *
     * @param User $user
     * 
     * @return void
     */
    private function sendVerificationEmail(User $user)
    {
        $token = $user->verification_token;
        $verificationUrl = '/verify-email?token=$token';

        Mail::send('emails.verify-email', compact('verificationUrl', 'token'), function ($message) use ($user) {
            $message->to($user->email)->subject(__('common.lbl_subject_mail_verify'));
        });
    }

    /**
     * Handle verify email
     *
     * @param Request $request
     * 
     * @return mixed
     */
    public function verifyEmail(Request $request)
    {
        // Validate token
        $request->validate([
            'token' => 'required|string|size:50',
        ]);
        // Find user by verification token
        $user = User::where('verification_token', $request->token)->whereNull('email_verified_at')->first();

        if (empty($user)) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_invalid_verify_token'));
        }

        try {
            // Mark email as verified
            $user->email_verified_at = now();
            $user->verification_token = null;
            $user->save();

            return successResponse(Response::HTTP_OK, [], __('common.msg_email_verify_success'));
        } catch (Throwable $thow) {
            Log::error('------ VERIFY EMAIL ERROR ------ ' . $thow->getMessage());

            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_invalid_verify_token'));
        }
    }

    /**
     * Handle send mail forgot password
     *
     * @param Request $request
     * 
     * @return mixed
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $user = $this->user->where('email', $request->get('email'))->first();

        if (empty($user)) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_find_not_found', ['data' => 'user']));
        }

        $now = new \DateTime();
        // 15 minutes
        $code = Str::random(10);
        $data = [
            'expiry' => $now->modify('+15 minutes')->format('Y-m-d H:i:s'),
            'code' => $code,
        ];

        try {
            $user->code_forgot_password = json_encode($data);
            $user->save();
            // Send mail
            $this->sendCodeForgotPasswordEmail($user, $data);

            return successResponse(Response::HTTP_OK, [], __('common.msg_email_forgot_success'));
        } catch (Throwable $thow) {
            Log::error('------ EMAIL SEND CODE RESET PASSWORD ERROR ------ ' . $thow->getMessage());

            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_email_forgot_fail'));
        }
    }

    /**
     * Handle send code forgot password email
     *
     * @param User $user
     * @param $data
     * 
     * @return void
     */
    private function sendCodeForgotPasswordEmail(User $user, $data)
    {
        $code = $data['code'];

        Mail::send('emails.forgot-email', compact('code'), function ($message) use ($user) {
            $message->to($user->email)->subject(__('common.lbl_subject_mail_forgot'));
        });
    }

    /**
     * Reset password
     *
     * @param Request $request
     * 
     * @return mixed
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ]);

        $user = $this->user->where('email', $request->get('email'))->first();

        if (empty($user) || empty($user->code_forgot_password)) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_find_not_found', ['data' => 'user']));
        }

        $dataCodeForgot = json_decode($user->code_forgot_password);

        if (empty($dataCodeForgot->code) || empty($dataCodeForgot->expiry) || $dataCodeForgot->code != $request->get('code')) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_find_not_found', ['data' => 'user']));
        }

        $expiry = $dataCodeForgot->expiry;
        $expiryDateTime = new \DateTime($expiry);
        $currentDateTime = new \DateTime();
        $currentDateTime->modify('+15 minutes');

        if ($expiryDateTime > $currentDateTime) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_code_expiry_or_wrong'));
        }

        try {
            $user->password = Hash::make($request->get('password'));
            $user->code_forgot_password = null;
            $user->save();

            return successResponse(Response::HTTP_OK, [], __('common.msg_reset_password_success'));
        } catch (Throwable $thow) {
            Log::error('------ RESET PASSWORD ERROR ------ ' . $thow->getMessage());

            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_code_expiry_or_wrong'));
        }
    }

    /**
     * Handle login
     *
     * @param Request $request
     * 
     * @return mixed
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_login_fail'));
        }

        try {
            $user = Auth::user();
            $result = [
                'name' => $user->name,
                'email' => $user->email,
                'token' => $user->createToken(config('auth.token_api'))->plainTextToken,
            ];

            return successResponse(Response::HTTP_OK, $result, __('common.msg_login_success'));
        } catch (Throwable $thow) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_login_fail'));
        }
    }
}
