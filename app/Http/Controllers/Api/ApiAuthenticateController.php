<?php

namespace App\Http\Controllers\Api;

use App\ApiResponser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RequestResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOTPRequest;
use App\Jobs\SendRequestResetAccountEmailJob;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ApiAuthenticateController extends Controller
{
    use ApiResponser;

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Auth"},
     *     summary="Login a user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User login successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="token", type="string", example="1|qwertyuiopasdfghjklzxcvbnm1234567890"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="slug", type="string", example="john-doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="Edit Articles"),
     *                         @OA\Property(property="slug", type="string", example="edit-articles"),
     *                         @OA\Property(property="start_at", type="string", format="date-time", example="2023-10-01T00:00:00Z"),
     *                         @OA\Property(property="expires_at", type="string", format="date-time", example="2024-10-01T00:00:00Z")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('phone', 'password');

            $result = $this->userRepository->login($credentials);

            if ($result === []) {
                return $this->responseError(__('auth.password'));
            }

            return $this->responseSuccess(__('auth.success'), $result);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            $accountInfo = $request->get('accountInfo');
            $token = $request->bearerToken();

            $this->userRepository->logout($accountInfo['id'], $token);

            return $this->responseSuccess(__('auth.logout'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/change-password",
     *     summary="Change Password",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="old_password", type="string", format="password", example="oldpassword123"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newpassword456"),
     *             @OA\Property(property="confirm_password", type="string", format="password", example="newpassword456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password change successfully",
     *     )
     * )
     */

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $useOTP = false;
            if ($request->has('otp_token')) {
                $token = $request->input('otp_token');
                $useOTP = true;
            } else {
                $oldPassword = $request->input('old_password');
                $token = $request->bearerToken();
            }
            $newPassword = $request->input('new_password');

            if (!$token) {
                return $this->responseError(__('auth.failed'));
            }
            $result = $this->userRepository->resetPassword($token, $newPassword, $useOTP, $oldPassword ?? null);

            if (!$result) {
                return $this->responseError(__('auth.password'));
            }

            return $this->responseSuccess(__('notification.api.update_success'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/request-reset-password",
     *     tags={"Auth"},
     *     summary="Request a password reset OTP",
     *     description="Send a reset OTP via email or phone. OTP expires after 5 minutes (300 seconds).",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             oneOf={
     *                 @OA\Schema(
     *                     required={"email"},
     *                     @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *                 ),
     *                 @OA\Schema(
     *                     required={"phone"},
     *                     @OA\Property(property="phone", type="string", example="+84912345678")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP successfully sent to the user's email or phone",
     *         @OA\JsonContent(
     *             @OA\Property(property="ttl", type="integer", example=300, description="Time in seconds until OTP expires")
     *         )
     *     ),
     * )
     */

    public function requestResetPassword(RequestResetPasswordRequest $request)
    {
        try {
            $accountInfo = [];
            if ($request->has('email')) {
                $email = $request->input('email');
                $accountInfo = $this->userRepository->getUserByEmail($email);
            } elseif ($request->has('phone')) {
                $phone = $request->input('phone');
                $accountInfo = $this->userRepository->getUserByPhone($phone);
            }
            if (empty($accountInfo)) {
                return $this->responseError(__('auth.user_not_found'));
            }

            $ttl = Redis::ttl('otp_reset_' . $accountInfo['email']);
            // check existing OTP
            if ($ttl <= 0) {
                $otp = rand(100000, 999999);
                Redis::setex('otp_reset_' . $accountInfo['email'], 300, $otp); // OTP valid for 5 minutes
                $ttl = Redis::ttl('otp_reset_' . $accountInfo['email']);
                SendRequestResetAccountEmailJob::dispatch($otp, $accountInfo['email']);
            }
            return $this->responseSuccess(__('notification.api.create_success'), ['ttl' => $ttl]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/verify-otp",
     *     tags={"Auth"},
     *     summary="Verify the OTP for password reset",
     *     description="Checks if the provided OTP is valid and returns a temporary token if correct.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "otp"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="otp", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI...")
     *         )
     *     )
     * )
     */

    public function VerifyOTP(VerifyOTPRequest $request)
    {
        try {
            $otp = $request->input('otp');
            $email = $request->input('email');

            $storedOtp = Redis::get('otp_reset_' . $email);

            $user = $this->userRepository->getUserByEmail($email);
            if ($otp === $storedOtp) {
                Redis::del('otp_reset_' . $email);
                $token = $this->userRepository->generateAndSaveToken($user);
                return $this->responseSuccess(__('auth.valid_otp'), ['token' => $token]);
            } else {
                return $this->responseError(__('auth.invalid_otp'));
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }
}
