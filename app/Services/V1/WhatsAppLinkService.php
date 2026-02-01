<?php
namespace App\Services\V1;

class WhatsAppLinkService{
    public function make(string $phone , string $msg)
    {
        $phone = preg_replace('/\D/', '', $phone);
        $text = urlencode($msg);
        return "https://wa.me/{$phone}?text={$text}";

    }
}
