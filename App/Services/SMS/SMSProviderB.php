<?php

namespace App\Services\SMS;
class SMSProviderB implements SMSInterface
{
    public function sendSMS(string $to, string $content): bool
    {

        // similar thing like other provider

        return true;
    }

    public function getName(): string
    {
        return 'provider_b';
    }
}

