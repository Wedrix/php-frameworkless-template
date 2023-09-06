<?php

declare(strict_types=1);

namespace App;

interface Email
{
    public function subject(): Text;

    public function sender(): EmailAddress;

    public function recipients(): EmailAddresses;

    public function carbonCopyRecipients(): EmailAddresses|Nothing;

    public function blindCarbonCopyRecipients(): EmailAddresses|Nothing;

    public function replyTo(): EmailAddress|Nothing;

    public function textBody(): Text|Nothing;

    public function htmlBody(): HTML|Nothing;

    public function attachments(): FilePaths|Nothing;
}

function Email(
    Text $subject,
    EmailAddress $sender,
    EmailAddresses $recipients,
    EmailAddresses|Nothing $carbonCopyRecipients,
    EmailAddresses|Nothing $blindCarbonCopyRecipients,
    EmailAddress|Nothing $replyTo,
    Text|Nothing $textBody,
    HTML|Nothing $htmlBody,
    FilePaths|Nothing $attachments
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
            private readonly EmailAddresses|Nothing $carbonCopyRecipients,
            private readonly EmailAddresses|Nothing $blindCarbonCopyRecipients,
            private readonly EmailAddress|Nothing $replyTo,
            private readonly Text|Nothing $textBody,
            private readonly HTML|Nothing $htmlBody,
            private readonly FilePaths|Nothing $attachments
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

        public function carbonCopyRecipients(): EmailAddresses|Nothing
        {
            return $this->carbonCopyRecipients;
        }

        public function blindCarbonCopyRecipients(): EmailAddresses|Nothing
        {
            return $this->blindCarbonCopyRecipients;
        }

        public function replyTo(): EmailAddress|Nothing
        {
            return $this->replyTo;
        }

        public function textBody(): Text|Nothing
        {
            return $this->textBody;
        }

        public function htmlBody(): HTML|Nothing
        {
            return $this->htmlBody;
        }

        public function attachments(): FilePaths|Nothing
        {
            return $this->attachments;
        }
    };
}