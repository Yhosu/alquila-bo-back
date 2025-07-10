<?php

namespace App\DTOs;

use App\Models\Subscriber;
use App\Enums\SubscriptionStatus;

class SubscriberDTO
{
    public function __construct(
        public string $email,
        public string $name,
        public string $subscription_status,
        public bool $confirmation_email_sent,
    ) {}

    public static function fromModel(Subscriber $subscriber): self
    {
        return new self(
            email: $subscriber->email,
            name: $subscriber->name,
            subscription_status: $subscriber->subscription_status,
            confirmation_email_sent: $subscriber->confirmation_email_sent
        );
    }


    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'subscription_status' => $this->subscription_status,
            'confirmation_email_sent' => $this->confirmation_email_sent
        ];
    }
}
