<?php

declare(strict_types=1);

namespace App;

/**
 * Needed since PHP cannot serialize Anonymous Classes (as of v8.2).
 */
final class _SerializableEmailClient implements EmailClient
{
    public function sendEmail(
        Email $email
    ): void
    {
        /**
         * Needed since PHP cannot serialize Anonymous Classes (as of v8.2).
         */
        $serializableEmail = new _SerializableEmail(
            sender: $email->sender(),
            recipients: $email->recipients(),
            subject: $email->subject(),
            body: $email->body()
        );

        TaskQueue()->addTask(
            task: static function() use($serializableEmail) {
                MailgunClient()->messages()->send(
                    domain: MailgunConfig()->domain(),
                    params: [
                        'from' => (string) $serializableEmail->sender(),
                        'to' => (string) $serializableEmail->recipients(),
                        'subject' => (string) $serializableEmail->subject(),
                        'html' => $serializableEmail->body()
                    ]
                );
            }
        );
    }
}