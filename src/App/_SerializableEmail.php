<?php

declare(strict_types=1);

namespace App;

/**
 * Needed since PHP cannot serialize Anonymous Classes (as of v8.2).
 * The fields cannot use the readonly keyword due a caveat of laravel/serializable-closures.
 * @see https://github.com/laravel/serializable-closure#caveats
 */
final class _SerializableEmail implements Email
{
    public function __construct(
        /**
         * @readonly
         */
        private EmailAddress $sender,
        /**
         * @readonly
         */
        private EmailAddresses $recipients,
        /**
         * @readonly
         */
        private Text $subject,
        /**
         * @readonly
         */
        private string $body
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
}