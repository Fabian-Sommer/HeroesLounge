<?php namespace Rikki\Heroeslounge\classes\Discord;

use Rikki\Heroeslounge\classes\Discord\AuthCode;
use Log;

class Webhook
{
    public static function sendMatchReschedule($message = "", $embed)
    {
        $url = 'https://discordapp.com/api/webhooks/' . Authcode::getCasterWebhookId() . '/' . Authcode::getCasterWebhookSecret();

        $headers = [
            "Content-Type: application/json",
            "User-Agent: HeroesLounge (http://heroeslounge.gg, 0.1)"
        ];

        $payload = json_encode(['content' => $message, 'embeds' => [$embed]]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );

        $output = curl_exec($ch);
        curl_close($ch);
    }  
}
