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

        if (!($carbonCopyRecipients instanceof Nothing)) {
            $symfonyEmail->cc(...\explode(',', (string) $carbonCopyRecipients));
        }

        if (!($blindCarbonCopyRecipients instanceof Nothing)) {
            $symfonyEmail->bcc(...\explode(',', (string) $blindCarbonCopyRecipients));
        }

        if (!($replyTo instanceof Nothing)) {
            $symfonyEmail->replyTo((string) $replyTo);
        }

        if (!($textBody instanceof Nothing)) {
            $symfonyEmail->text((string) $textBody);
        }

        if (!($htmlBody instanceof Nothing)) {
            $symfonyEmail->html((string) $htmlBody);
        }

        if (!($attachments instanceof Nothing)) {
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