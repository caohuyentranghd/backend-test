<?php

namespace App\Http\Controllers\Api;

use App\Events\SendMailCodeForgotPasswordEvent;
use App\Events\SendMailVerificationEvent;
use Throwable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Services\Internals\User\UserServiceInterface;

class AuthController extends Controller
{
    private $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
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
            $user = $this->userService->store($data);
            // Send verification email
            event(new SendMailVerificationEvent($user));

            return successResponse(Response::HTTP_CREATED, [], __('common.msg_register_success'));
        } catch (Throwable $thow) {
            Log::error('------ REGISTER ERROR ------ ' . $thow->getMessage());

            return failResponse(Response::HTTP_BAD_REQUEST, [], __('common.msg_register_error'));
        }
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
        $user = $this->userService->getFirstBy(
            collect([
                'verification_token' => $request->token,
                'is_where_null_email_verified_at' => true,
            ])
        );

        if (empty($user)) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_invalid_verify_token'));
        }

        try {
            // Mark email as verified
            $this->userService->update(
                collect([
                    'email_verified_at' => now(),
                    'verification_token' => null,
                ], collect([
                    'id' => $user->id,
                ]))
            );

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
        $user = $this->userService->getFirstBy(
            collect([
                'email' => $request->get('email'),
            ])
        );

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
            $this->userService->update(
                collect([
                    'code_forgot_password' => json_encode($data),
                ]),
                collect([
                    'id' => $user->id,
                ])
            );
            // Send mail
            event(new SendMailCodeForgotPasswordEvent($user, $data));

            return successResponse(Response::HTTP_OK, [], __('common.msg_email_forgot_success'));
        } catch (Throwable $thow) {
            Log::error('------ EMAIL SEND CODE RESET PASSWORD ERROR ------ ' . $thow->getMessage());

            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_email_forgot_fail'));
        }
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

        $user = $this->userService->getFirstBy(
            collect([
                'email' => $request->get('email'),
            ])
        );

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
            $this->userService->update(
                collect([
                    'password' => Hash::make($request->get('password')),
                    'code_forgot_password' => null,
                ]),
                collect([
                    'id' => $user->id,
                ])
            );

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

        $user = $this->userService->getFirstBy(
            collect([
                'email' => $request->get('email'),
            ])
        );

        if (!empty($user) && empty($user->email_verified_at)) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_not_verify_email'));
        }

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
