<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

/**
 * Sends mail through Brevo's transactional email HTTP API (port 443) instead
 * of SMTP. Container hosts commonly block outbound SMTP ports, so the API is
 * the only reliable transport from a Bunny Magic Container.
 */
class BrevoApiTransport extends AbstractTransport
{
    public function __construct(private readonly string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $from = $email->getFrom()[0] ?? $message->getEnvelope()->getSender();

        $payload = [
            'sender' => $this->address($from),
            'to' => $this->addresses($email->getTo()),
            'subject' => $email->getSubject() ?? '',
        ];

        if ($html = $email->getHtmlBody()) {
            $payload['htmlContent'] = is_string($html) ? $html : stream_get_contents($html);
        }
        if ($text = $email->getTextBody()) {
            $payload['textContent'] = is_string($text) ? $text : stream_get_contents($text);
        }
        if (! isset($payload['htmlContent']) && ! isset($payload['textContent'])) {
            $payload['htmlContent'] = ' ';
        }
        if ($cc = $email->getCc()) {
            $payload['cc'] = $this->addresses($cc);
        }
        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = $this->addresses($bcc);
        }
        if ($replyTo = $email->getReplyTo()) {
            $payload['replyTo'] = $this->address($replyTo[0]);
        }

        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                'name' => $attachment->getFilename() ?: 'attachment',
                'content' => base64_encode($attachment->getBody()),
            ];
        }
        if ($attachments) {
            $payload['attachment'] = $attachments;
        }

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->timeout(30)->post('https://api.brevo.com/v3/smtp/email', $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Brevo API rejected the message: '.$response->status().' '.$response->body());
        }
    }

    private function address(Address $address): array
    {
        $out = ['email' => $address->getAddress()];

        if ($address->getName() !== '') {
            $out['name'] = $address->getName();
        }

        return $out;
    }

    /** @param  Address[]  $addresses */
    private function addresses(array $addresses): array
    {
        return array_map(fn (Address $a) => $this->address($a), $addresses);
    }

    public function __toString(): string
    {
        return 'brevo-api';
    }
}
