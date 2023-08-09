<?php

declare(strict_types=1);

namespace App;

interface Email
{
    public function sender(): EmailAddress;

    public function recipients(): EmailAddresses;

    public function subject(): Text;

    public function body(): string;
}

function Email(
    EmailAddress $sender,
    EmailAddresses $recipients,
    Text $subject,
    string $body
): Email
{
    return new class(
        sender: $sender,
        recipients: $recipients,
        subject: $subject,
        body: $body
    ) implements Email
    {
        public function __construct(
            private readonly EmailAddress $sender,
            private readonly EmailAddresses $recipients,
            private readonly Text $subject,
            private readonly string $body
        ){}
    
        public function sender(): EmailAddress
        {
            return $this->sender;
        }
    
        public function recipients(): EmailAddresses
        {
            return $this->recipients;
        }
    
        public function subject(): Text
        {
            return $this->subject;
        }
    
        public function body(): string
        {
            return $this->body;
        }
    };
}