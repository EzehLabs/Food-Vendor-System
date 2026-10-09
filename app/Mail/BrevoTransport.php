<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class BrevoTransport extends AbstractTransport
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $fromAddress,
        private readonly string $fromName,
    ) {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'brevo+api';
    }

    protected function doSend(SentMessage $sentMessage): void
    {
        $message = $sentMessage->getOriginalMessage();

        if (! $message instanceof Email) {
            throw new RuntimeException('Brevo can only send structured email messages.');
        }

        if (! $this->apiKey) {
            throw new RuntimeException('BREVO_API_KEY is not configured.');
        }

        if ($message->getAttachments() !== []) {
            throw new RuntimeException('Brevo API email attachments are not supported by this transport.');
        }

        $sender = $message->getFrom()[0] ?? new Address($this->fromAddress, $this->fromName);

        $payload = [
            'sender' => $this->formatAddress($sender),
            'to' => $this->formatAddresses($message->getTo()),
            'subject' => $message->getSubject(),
        ];

        if ($payload['to'] === []) {
            $payload['to'] = $this->formatAddresses($sentMessage->getEnvelope()->getRecipients());
        }

        if ($payload['to'] === []) {
            throw new RuntimeException('Brevo email must have at least one recipient.');
        }

        if ($message->getCc() !== []) {
            $payload['cc'] = $this->formatAddresses($message->getCc());
        }

        if ($message->getBcc() !== []) {
            $payload['bcc'] = $this->formatAddresses($message->getBcc());
        }

        if ($message->getReplyTo() !== []) {
            $payload['replyTo'] = $this->formatAddress($message->getReplyTo()[0]);
        }

        $html = $this->bodyContents($message->getHtmlBody());
        $text = $this->bodyContents($message->getTextBody());

        if ($html !== null) {
            $payload['htmlContent'] = $html;
        }

        if ($text !== null) {
            $payload['textContent'] = $text;
        }

        $response = Http::acceptJson()
            ->withHeaders(['api-key' => $this->apiKey])
            ->timeout(15)
            ->post('https://api.brevo.com/v3/smtp/email', $payload)
            ->throw();

        $messageId = $response->json('messageId');

        if (is_string($messageId) && $messageId !== '') {
            $sentMessage->setMessageId($messageId);
        }
    }

    /**
     * @param  list<Address>  $addresses
     * @return list<array{email: string, name?: string}>
     */
    private function formatAddresses(array $addresses): array
    {
        return array_map(fn (Address $address) => $this->formatAddress($address), $addresses);
    }

    /**
     * @return array{email: string, name?: string}
     */
    private function formatAddress(Address $address): array
    {
        $formatted = ['email' => $address->getAddress()];

        if ($address->getName() !== '') {
            $formatted['name'] = $address->getName();
        }

        return $formatted;
    }

    /**
     * @param  resource|string|null  $body
     */
    private function bodyContents(mixed $body): ?string
    {
        if (is_resource($body)) {
            $contents = stream_get_contents($body);

            if ($contents === false) {
                throw new RuntimeException('Unable to read email message body.');
            }

            return $contents;
        }

        return $body;
    }
}
