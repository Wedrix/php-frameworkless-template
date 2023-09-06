<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

/**
 * Needed since PHP cannot serialize Anonymous Classes (as of v8.2).
 */
final class _SerializableEmailClient implements EmailClient
{
    public function sendEmail(
        Email $email
    ): void
    {
        $symfonyEmail = (new SymfonyEmail())
            ->subject((string) $email->subject())
            ->from((string) $email->sender())
            ->to(...\explode(',', (string) $email->recipients()));

        if (!$email->carbonCopyRecipients() instanceof EmptyText) {
            $symfonyEmail->cc(...\explode(',', (string) $email->carbonCopyRecipients()));
        }

        if (!$email->blindCarbonCopyRecipients() instanceof EmptyText) {
            $symfonyEmail->bcc(...\explode(',', (string) $email->blindCarbonCopyRecipients()));
        }

        if (!$email->replyTo() instanceof EmptyText) {
            $symfonyEmail->replyTo((string) $email->replyTo());
        }

        if (!$email->textBody() instanceof EmptyText) {
            $symfonyEmail->text((string) $email->textBody());
        }

        if (!$email->htmlBody() instanceof EmptyText) {
            $symfonyEmail->html((string) $email->htmlBody());
        }

        if (!$email->attachments() instanceof EmptyText) {
            foreach (\explode(',', (string) $email->attachments()) as $file) {
                $symfonyEmail->addPart(new DataPart(new File($file)));
            }
        }

        TaskQueue()->addTask(
            task: static function() use($symfonyEmail) {
                SymfonyMailer()->send($symfonyEmail);
            }
        );
    }
}