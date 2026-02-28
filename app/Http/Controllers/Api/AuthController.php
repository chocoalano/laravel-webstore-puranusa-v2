<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginCustomerApiRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Customer;
use App\Services\Auth\CustomerAuthService;
use App\Services\Auth\CustomerRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly CustomerAuthService $authService,
        private readonly CustomerRegistrationService $registrationService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/auth/login-meta",
     *     tags={"Customer Auth"},
     *     summary="Metadata halaman login customer",
     *     description="Mengembalikan metadata SEO yang digunakan pada halaman login web customer.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Metadata login berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "data":{
     *                     "seo":{
     *                         "title":"Masuk ke Akun Member",
     *                         "description":"Masuk ke akun member Webstore.",
     *                         "canonical":"http://localhost/login"
     *                     }
     *                 }
     *             }
     *         )
     *     )
     * )
     */
    public function loginMeta(): JsonResponse
    {
        return response()->json([
            'data' => [
                'seo' => [
                    'title' => 'Masuk ke Akun Member',
                    'description' => 'Masuk ke akun member '.config('app.name').'. Nikmati harga eksklusif, pantau e-wallet, dan kelola jaringan afiliasi Anda kapan saja.',
                    'canonical' => route('login'),
                ],
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/register-meta",
     *     tags={"Customer Auth"},
     *     summary="Metadata halaman registrasi customer",
     *     description="Mengembalikan metadata SEO dan referral code aktif yang digunakan pada halaman registrasi web customer.",
     *
     *     @OA\Parameter(name="referral_code", in="query", required=false, @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Metadata registrasi berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "data":{
     *                     "referralCode":"ABCD1234",
     *                     "seo":{
     *                         "title":"Daftar Jadi Member",
     *                         "description":"Bergabunglah sebagai member Webstore.",
     *                         "canonical":"http://localhost/register"
     *                     }
     *                 }
     *             }
     *         )
     *     )
     * )
     */
    public function registerMeta(Request $request): JsonResponse
    {
        if ($request->filled('referral_code')) {
            $request->session()->put('referral_code', (string) $request->query('referral_code'));
        }

        return response()->json([
            'data' => [
                'referralCode' => $request->session()->get('referral_code'),
                'seo' => [
                    'title' => 'Daftar Jadi Member',
                    'description' => 'Bergabunglah sebagai member '.config('app.name').' dan nikmati harga eksklusif, bonus afiliasi, serta akses ke ribuan produk unggulan. Gratis daftar sekarang!',
                    'canonical' => route('register'),
                ],
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Customer Auth"},
     *     summary="Registrasi customer API",
     *     description="Mendaftarkan customer baru melalui API sesuai rule registrasi customer web.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","username","email","telp","gender","password","password_confirmation","terms"},
     *
     *             @OA\Property(property="name", type="string", example="Budi Santoso"),
     *             @OA\Property(property="username", type="string", example="budi01"),
     *             @OA\Property(property="email", type="string", format="email", example="budi@example.com"),
     *             @OA\Property(property="telp", type="string", example="08123456789"),
     *             @OA\Property(property="nik", type="string", nullable=true, example="3276010101010001"),
     *             @OA\Property(property="gender", type="string", example="L"),
     *             @OA\Property(property="alamat", type="string", nullable=true, example="Jl. Merdeka No. 1"),
     *             @OA\Property(property="referral_code", type="string", nullable=true, example="ABCD1234"),
     *             @OA\Property(property="password", type="string", format="password", example="secret12345"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret12345"),
     *             @OA\Property(property="terms", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Registrasi berhasil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Registrasi customer berhasil.",
     *                 "data":{
     *                     "customer":{
     *                         "id":1,
     *                         "name":"Budi Santoso",
     *                         "username":"budi01",
     *                         "email":"budi@example.com",
     *                         "phone":"08123456789",
     *                         "status":1,
     *                         "ref_code":"ABCD1234"
     *                     }
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Data tidak valid.",
     *                 "errors":{"email":{"Email sudah terdaftar."}}
     *             }
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $customer = $this->registrationService->register($request);

        return response()->json([
            'message' => 'Registrasi customer berhasil.',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'username' => $customer->username,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'status' => $customer->status,
                    'ref_code' => $customer->ref_code,
                ],
            ],
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Customer Auth"},
     *     summary="Login customer API",
     *     description="Autentikasi customer menggunakan username dan password, lalu generate Sanctum personal access token.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"username","password"},
     *
     *             @OA\Property(property="username", type="string", example="member001"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="device_name", type="string", nullable=true, example="android-app")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login berhasil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Login API berhasil.",
     *                 "data":{
     *                     "token_type":"Bearer",
     *                     "access_token":"1|abcdefghijklmnopqrstuvwxyz",
     *                     "customer":{
     *                         "id":1,
     *                         "name":"Budi Santoso",
     *                         "username":"budi01",
     *                         "email":"budi@example.com",
     *                         "phone":"08123456789",
     *                         "status":3
     *                     }
     *                 }
     *             },
     *
     *             @OA\Property(property="message", type="string", example="Login API berhasil."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="access_token", type="string", example="1|abcdefghijklmnopqrstuvwxyz"),
     *                 @OA\Property(
     *                     property="customer",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Budi Santoso"),
     *                     @OA\Property(property="username", type="string", example="budi01"),
     *                     @OA\Property(property="email", type="string", example="budi@example.com"),
     *                     @OA\Property(property="phone", type="string", nullable=true, example="08123456789"),
     *                     @OA\Property(property="status", type="integer", example=3)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal atau kredensial tidak valid",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Username atau kata sandi salah.",
     *                 "errors":{"username":{"Username atau kata sandi salah."}}
     *             }
     *         )
     *     )
     * )
     */
    public function login(LoginCustomerApiRequest $request): JsonResponse
    {
        $result = $this->authService->attemptApiLogin(
            $request->string('username')->toString(),
            $request->string('password')->toString(),
            $request->input('device_name')
        );

        if ($result === null) {
            return response()->json([
                'message' => 'Username atau kata sandi salah.',
                'errors' => [
                    'username' => ['Username atau kata sandi salah.'],
                ],
            ], 422);
        }

        /** @var Customer $customer */
        $customer = $result['customer'];

        return response()->json([
            'message' => 'Login API berhasil.',
            'data' => [
                'token_type' => $result['token_type'],
                'access_token' => $result['access_token'],
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'username' => $customer->username,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'status' => $customer->status,
                ],
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Customer Auth"},
     *     summary="Logout customer API",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout berhasil",
     *
     *         @OA\JsonContent(example={"message":"Logout API berhasil."})
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Tidak terautentikasi",
     *
     *         @OA\JsonContent(example={"message":"Tidak terautentikasi."})
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $customer = $request->user('sanctum');

        if (! $customer instanceof Customer) {
            return response()->json([
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $this->authService->logoutApi($customer);

        return response()->json([
            'message' => 'Logout API berhasil.',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     tags={"Customer Auth"},
     *     summary="Profil customer saat ini",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data profil customer",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "id":1,
     *                 "name":"Budi Santoso",
     *                 "username":"budi01",
     *                 "email":"budi@example.com",
     *                 "phone":"08123456789",
     *                 "status":3
     *             },
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Budi Santoso"),
     *             @OA\Property(property="username", type="string", example="budi01"),
     *             @OA\Property(property="email", type="string", example="budi@example.com"),
     *             @OA\Property(property="phone", type="string", nullable=true, example="08123456789"),
     *             @OA\Property(property="status", type="integer", example=3)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Tidak terautentikasi",
     *
     *         @OA\JsonContent(example={"message":"Tidak terautentikasi."})
     *     )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        $customer = $request->user('sanctum');

        if (! $customer instanceof Customer) {
            return response()->json([
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'username' => $customer->username,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'status' => $customer->status,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/impersonation/stop",
     *     tags={"Customer Auth"},
     *     summary="Hentikan sesi impersonasi customer",
     *     description="Mengakhiri sesi impersonasi customer dan mengembalikan konteks autentikasi admin jika tersedia.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Impersonasi berhasil dihentikan",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Impersonasi berhasil dihentikan.",
     *                 "data":{
     *                     "redirect_to":"http://localhost/admin/customers",
     *                     "admin_still_authenticated":true
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Tidak ada sesi impersonasi aktif",
     *
     *         @OA\JsonContent(example={"message":"Tidak ada sesi impersonasi aktif."})
     *     )
     * )
     */
    public function stopImpersonation(Request $request): JsonResponse
    {
        $impersonationSession = $request->session()->get('impersonation', []);

        if (! is_array($impersonationSession) || ! (bool) ($impersonationSession['is_active'] ?? false)) {
            return response()->json([
                'message' => 'Tidak ada sesi impersonasi aktif.',
            ], 400);
        }

        $adminStillAuthenticated = Auth::guard('web')->check();

        Auth::guard('customer')->logout();

        $request->session()->forget('impersonation');
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        $redirectUrl = $adminStillAuthenticated
            ? route('filament.control-panel.resources.customers.index')
            : route('home');

        return response()->json([
            'message' => 'Impersonasi berhasil dihentikan.',
            'data' => [
                'redirect_to' => $redirectUrl,
                'admin_still_authenticated' => $adminStillAuthenticated,
            ],
        ]);
    }
}
