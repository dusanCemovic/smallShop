<?php

namespace App\Services\SMS;
class SMSProviderA implements SMSInterface
{
    /**
     * @throws \Exception
     */
    public function sendSMS(string $to, string $content): bool
    {
        // here we will have connection to real service
        // now, we are just simulate that it is working

        if ($this->sameCondition()) {
            throw new \Exception('Provider A error');
        }

        return true;
    }

    public function getName(): string
    {
        return 'provider_a';
    }

    /**
     * This is custom method that can be used for testing that something went down
     * @return true
     */
    public function sameCondition() : bool {
        return false;
    }
}

