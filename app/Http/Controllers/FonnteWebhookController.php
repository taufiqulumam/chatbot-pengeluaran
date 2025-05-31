<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class FonnteWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Ambil pesan dan data penting dari request
        $message = $request->input('message');
        $sender = $request->input('sender');
        $fromName = $request->input('from_name'); // nama grup
        $number = $request->input('number'); // nomor pengirim

        // Cek apakah ini dari grup
        if ($request->input('is_group') !== true) {
            return response('Bukan dari grup', 200);
        }

        // Cek apakah pesan berformat "deskripsi jumlah"
        if (preg_match('/^(.+)\s+(\d{3,})$/', $message, $matches)) {
            $deskripsi = trim($matches[1]);
            $nominal = (int) $matches[2];

            // Format pesan balasan
            $reply = "✅ Pengeluaran dicatat!\n" .
                     "📌 $deskripsi\n" .
                     "💰 Rp" . number_format($nominal, 0, ',', '.') . "\n" .
                     "📅 " . now()->format('d M Y') . "\n" .
                     "👤 Dari: $sender";

            // Kirim balasan ke grup
            $this->sendReply($fromName, $reply);

            // Simpan ke Google Sheets
            $this->logToGoogleSheet($deskripsi, $nominal, $sender);
        }

        return response('OK', 200);
    }

    protected function sendReply($groupName, $message)
    {
        // Ganti dengan token dari dashboard Fonnte kamu
        $token = env('FONNTE_TOKEN'); // simpan di .env

        Http::withToken($token)->post('https://api.fonnte.com/send', [
            'target' => $groupName, // nama grup sesuai Fonnte
            'message' => $message,
            'delay' => 1,
        ]);
    }

    protected function logToGoogleSheet($desc, $amount, $sender)
    {
        $url = env('SHEET_WEBHOOK_URL'); // URL dari Google Apps Script Web App

        Http::post($url, [
            'nama' => $desc,
            'nominal' => $amount,
            'pengirim' => $sender,
        ]);
    }
}
