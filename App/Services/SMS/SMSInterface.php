<?php

namespace App\Services\SMS;
// all providers share similar things - sending SMS
interface SMSInterface
{
    public function sendSMS(string $to, string $content): bool;

    public function getName(): string;
}

