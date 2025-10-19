<?php

namespace App\Services\SMS;

use App\Models\DB;

class SMSManager
{
    protected $providers = [];
    protected $cfg;
    protected $pdo;

    protected $redis;

    public function __construct()
    {
        $this->cfg = DB::getConfig()['sms'];
        $this->pdo = DB::getConnection();
        $this->providers['provider_a'] = new SMSProviderA();
        $this->providers['provider_b'] = new SMSProviderB();

        // !! THIS CAN BE IMPORTANT FOR OPTIMIZATION
        /* Simplest is to use database each time, but we need to optimize this

        - we can use something like redis
        - or we can use static file or server to avoid calling db

        WE WILL USE STATIC file for counting

        * After this we can optimize even more. We can put logs into batch and then pushed them with cron job from time to time

        */
    }

    /**
     * Count logs in last minute for specific provider
     * @param $providerName
     * @return int
     */
    protected function countSentInLastMinute($providerName) : int
    {
        $query = $this->pdo->prepare("SELECT COUNT(*) FROM sms_logs WHERE provider = :p AND created_at >= (NOW() - INTERVAL 5 MINUTE)");
        $query->execute(['p' => $providerName]);
        return (int)$query->fetchColumn();
    }

    /**
     * Record a new sent SMS timestamp in memory
     */
    protected function addToCache(string $providerName): void
    {
       // this is now empty, but can be used together with @see countSentInLastMinute to check number of sms logs
    }

    public function sendWithFailover(string $to, string $content) : bool
    {
        $providersOrder = [];

        // we can use primary if we want. If not, we can use it random.
        $primary = $this->cfg['primary'] ?? 'provider_a';
        $providersOrder[] = $this->providers[$primary];

        // then add others, or we can set secondary, third etc.
        foreach ($this->providers as $name => $provService) {
            if ($name !== $primary) {
                $providersOrder[] = $provService;
            }
        }

        // try to send sms on each provider
        foreach ($providersOrder as $provider) {

            $name = $provider->getName();
            $count = $this->countSentInLastMinute($name);

            if ($count >= 5) {
                // we already reach, try other provider
                continue;
            }

            try {
                $sendingStatus = $provider->sendSMS($to, $content);

                $this->logSms($name, $to, $content, $sendingStatus, $sendingStatus ? null : 'Provider returned false');

                // update in-memory counter only if SMS was attempted
                $this->addToCache($name);

                if ($sendingStatus) {
                    return true; // return only if it is successful. If it is failed, anyway put in log
                }

            } catch (\Exception $e) {
                // in case of error, put in log file
                $this->logSms($name, $to, $content, false, $e->getMessage());
                continue;
            }
        }

        // if we reach this line, then all provider hit maximum, we may use some other service
        throw new \Exception('All SMS providers failed or are rate-limited.');
    }

    /**
     * @param $provider
     * @param $to
     * @param $content
     * @param $sendingStatus
     * @param $errorMessage
     * @return void
     */
    private function logSms($provider, $to, $content, $sendingStatus, $errorMessage = null): void
    {

        $query = $this->pdo->prepare("INSERT INTO sms_logs (provider, to_phone, content, success, error_message) 
                VALUES (:provider, :to, :content, :success, :error)");
        $query->execute([
            'provider' => $provider,
            'to' => $to,
            'content' => $content,
            'success' => $sendingStatus ? 1 : 0,
            'error' => $errorMessage
        ]);

    }
}