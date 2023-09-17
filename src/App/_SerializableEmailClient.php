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
        $subject = $email->subject();
        $sender = $email->sender();
        $recipients = $email->recipients();
        $carbonCopyRecipients = $email->carbonCopyRecipients();
        $blindCarbonCopyRecipients = $email->blindCarbonCopyRecipients();
        $replyTo = $email->replyTo();
        $textBody = $email->textBody();
        $htmlBody = $email->htmlBody();
        $attachments = $email->attachments();

        $symfonyEmail = (new SymfonyEmail())
            ->subject((string) $subject)
            ->from((string) $sender)
            ->to(...\explode(',', (string) $recipients));

        if (!\is_null($carbonCopyRecipients)) {
            $symfonyEmail->cc(...\explode(',', (string) $carbonCopyRecipients));
        }

        if (!\is_null($blindCarbonCopyRecipients)) {
            $symfonyEmail->bcc(...\explode(',', (string) $blindCarbonCopyRecipients));
        }

        if (!\is_null($replyTo)) {
            $symfonyEmail->replyTo((string) $replyTo);
        }

        if (!\is_null($textBody)) {
            $symfonyEmail->text((string) $textBody);
        }

        if (!\is_null($htmlBody)) {
            $symfonyEmail->html((string) $htmlBody);
        }

        if (!\is_null($attachments)) {
            foreach (\explode(',', (string) $attachments) as $file) {
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