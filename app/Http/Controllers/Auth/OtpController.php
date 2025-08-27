<?php

namespace App\Http\Controllers\Auth;

use App\Models\Auth\Otp;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OtpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Otp $otp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Otp $otp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Otp $otp)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Otp $otp)
    {
        //
    }

    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    // Tampilkan halaman login OTP
    public function showLoginForm()
    {
        return Inertia::render('Auth/OtpLogin');
    }

    // Kirim OTP ke WhatsApp
    public function checkPhone(Request $request)
    {
        $request->validate(['phone' => 'required|numeric']);

        $user = User::where('numberPhone', $request->phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'Nomor HP tidak terdaftar.']);
        }

        $otp = rand(1000, 9999); // 4 digit OTP
        $expiresAt = Carbon::now()->addMinutes(1);

        // Simpan OTP dan waktu kedaluwarsa ke dalam database dan session
        Otp::updateOrCreate(
            ['phone_number' => $request->phone],
            ['otp' => $otp, 'expires_at' => $expiresAt]
        );

        $message = "Hai! Kode OTP Anda untuk login adalah: $otp. Kode ini berlaku selama 2 menit. Harap masukkan kode OTP di aplikasi untuk melanjutkan proses login. Terima kasih!";
        $img = "";
        $this->fonnteService->sendWhatsAppMessage($request->phone, $message, $img);
        return redirect()->route('login-otp')->with([
            'otp_sent' => true,
            'phone' => $request->phone,
            'expires_at' => $expiresAt->timestamp,
        ]);
    }

    // Verifikasi OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric',
            'otp' => 'required|numeric|digits:4',
        ]);

        $otp = Otp::where('phone_number', $request->phone)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otp) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau telah kedaluwarsa.']);
        }

        $user = User::where('numberPhone', $request->phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'Nomor HP tidak terdaftar.']);
        }

        Auth::login($user, true); // Login dengan "Remember Me"
        $otp->delete(); // Hapus OTP setelah berhasil login

        return redirect()->intended('/dashboard');
    }

    // Kirim ulang OTP
    public function resendOtp(Request $request)
    {
        $request->validate(['phone' => 'required|numeric']);

        $user = User::where('numberPhone', $request->phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'Nomor HP tidak terdaftar.']);
        }

        $otp = rand(1000, 9999); // 4 digit OTP
        $expiresAt = Carbon::now()->addMinutes(1);

        Otp::updateOrCreate(
            ['phone_number' => $request->phone],
            ['otp' => $otp, 'expires_at' => $expiresAt]
        );

        $message = "Hai! Kode OTP Anda untuk login adalah: $otp. Kode ini berlaku selama 2 menit. Harap masukkan kode OTP di aplikasi untuk melanjutkan proses login. Terima kasih!";
        $img = "";
        $this->fonnteService->sendWhatsAppMessage($request->phone, $message, $img);

        return redirect()->route('login-otp')->with([
            'otp_sent' => true,
            'phone' => $request->phone,
            'expires_at' => $expiresAt->timestamp,
        ]);
    }
}