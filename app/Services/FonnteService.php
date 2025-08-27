<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('FONNTE_API_KEY');
    }

    public function sendWhatsAppMessage($phoneNumber, $message)
    {
        // $response = Http::withHeaders([
        //     'Authorization' => $this->apiKey,
        // ])->post('https://api.fonnte.com/send', [
        //     'target' => $phoneNumber,    // Nomor tujuan dengan kode negara, misalnya: 6281234567890
        //     'message' => $message,       // Pesan yang ingin dikirim
        //     'countryCode' => '62',       // Kode negara Indonesia
        //     'image' => $imageUrl,
        // ]);

        // Payload dasar
        $payload = [
            'target' => $phoneNumber,    // Nomor tujuan dengan kode negara, misalnya: 6281234567890
            'message' => $message,       // Pesan yang ingin dikirim
            'countryCode' => '62',       // Kode negara Indonesia
        ];

        // Kirim permintaan ke API Fonnte
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
        ])->post('https://api.fonnte.com/send', $payload);

        return $response->json();
    }
}