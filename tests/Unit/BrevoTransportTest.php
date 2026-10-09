<?php

namespace Tests\Unit;

use App\Mail\BrevoTransport;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Tests\TestCase;

class BrevoTransportTest extends TestCase
{
    public function test_it_sends_email_through_brevo_https_api(): void
    {
        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => '<test-id>'], 201),
        ]);

        $email = (new Email())
            ->from(new Address('sender@example.com', 'Food Vending System'))
            ->to(new Address('customer@example.com', 'Customer'))
            ->subject('Verify your email')
            ->text('Open the verification link.')
            ->html('<p>Open the verification link.</p>');

        $transport = new BrevoTransport('test-api-key', 'sender@example.com', 'Food Vending System');
        $sentMessage = $transport->send($email);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->hasHeader('api-key', 'test-api-key')
                && $request['sender']['email'] === 'sender@example.com'
                && $request['to'][0]['email'] === 'customer@example.com'
                && $request['subject'] === 'Verify your email'
                && $request['htmlContent'] === '<p>Open the verification link.</p>'
                && $request['textContent'] === 'Open the verification link.';
        });

        $this->assertSame('<test-id>', $sentMessage?->getMessageId());
    }

    public function test_laravel_brevo_mailer_uses_the_https_api(): void
    {
        config([
            'mail.default' => 'brevo',
            'mail.from.address' => 'sender@example.com',
            'mail.from.name' => 'Food Vending System',
            'services.brevo.key' => 'test-api-key',
        ]);

        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => '<test-id>'], 201),
        ]);

        Mail::raw('Verification message', function ($message): void {
            $message->to('customer@example.com')->subject('Verify your email');
        });

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request['sender']['email'] === 'sender@example.com'
                && $request['to'][0]['email'] === 'customer@example.com'
                && $request['subject'] === 'Verify your email'
                && $request['textContent'] === 'Verification message';
        });
    }
}
