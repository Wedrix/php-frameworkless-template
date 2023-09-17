<?php

declare(strict_types=1);

namespace App;

interface Email
{
    public function subject(): Text;

    public function sender(): EmailAddress;

    public function recipients(): EmailAddresses;

    public function carbonCopyRecipients(): ?EmailAddresses;

    public function blindCarbonCopyRecipients(): ?EmailAddresses;

    public function replyTo(): ?EmailAddress;

    public function textBody(): ?Text;

    public function htmlBody(): ?HTML;

    public function attachments(): ?FilePaths;
}

function Email(
    Text $subject,
    EmailAddress $sender,
    EmailAddresses $recipients,
    ?EmailAddresses $carbonCopyRecipients,
    ?EmailAddresses $blindCarbonCopyRecipients,
    ?EmailAddress $replyTo,
    ?Text $textBody,
    ?HTML $htmlBody,
    ?FilePaths $attachments
): Email
{
    return new class(
        subject: $subject,
        sender: $sender,
        recipients: $recipients,
        carbonCopyRecipients: $carbonCopyRecipients,
        blindCarbonCopyRecipients: $blindCarbonCopyRecipients,
        replyTo: $replyTo,
        textBody: $textBody,
        htmlBody: $htmlBody,
        attachments: $attachments
    ) implements Email
    {
        public function __construct(
            private readonly Text $subject,
            private readonly EmailAddress $sender,
            private readonly EmailAddresses $recipients,
            private readonly ?EmailAddresses $carbonCopyRecipients,
            private readonly ?EmailAddresses $blindCarbonCopyRecipients,
            private readonly ?EmailAddress $replyTo,
            private readonly ?Text $textBody,
            private readonly ?HTML $htmlBody,
            private readonly ?FilePaths $attachments
        ){}
    
        public function subject(): Text
        {
            return $this->subject;
        }
    
        public function sender(): EmailAddress
        {
            return $this->sender;
        }
    
        public function recipients(): EmailAddresses
        {
            return $this->recipients;
        }

        public function carbonCopyRecipients(): ?EmailAddresses
        {
            return $this->carbonCopyRecipients;
        }

        public function blindCarbonCopyRecipients(): ?EmailAddresses
        {
            return $this->blindCarbonCopyRecipients;
        }

        public function replyTo(): ?EmailAddress
        {
            return $this->replyTo;
        }

        public function textBody(): ?Text
        {
            return $this->textBody;
        }

        public function htmlBody(): ?HTML
        {
            return $this->htmlBody;
        }

        public function attachments(): ?FilePaths
        {
            return $this->attachments;
        }
    };
}