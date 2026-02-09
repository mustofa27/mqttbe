<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $body = "Nama: {$data['name']}\n" .
            "Email: {$data['email']}\n" .
            "Subjek: {$data['subject']}\n\n" .
            $data['message'];

        Mail::raw($body, function ($mail) use ($data) {
            $mail->to(config('contact.to_email', 'icminovasi@gmail.com'))
                ->subject('Contact Us: ' . $data['subject'])
                ->replyTo($data['email'], $data['name']);
        });

        return back()->with('success', 'Pesan berhasil dikirim. Tim kami akan segera menghubungi Anda.');
    }
}
