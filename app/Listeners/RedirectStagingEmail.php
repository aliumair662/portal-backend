<?php
namespace App\Listeners;
use Illuminate\Mail\Events\MessageSending;

class RedirectStagingEmail
{
    public function handle(MessageSending $event)
    {
        if (app()->environment('staging')) {
            $original = [];

            foreach (['To', 'Cc', 'Bcc'] as $type) {
                if ($address = $event->message->{'get' . $type}()) {
                    $original[$type] = $address;
                    $event->message->{'set' . $type}(null);
                }
            }

            $event->message->setTo(config('app.staging-catch-all-email'));

            $event->message->getHeaders()->addTextHeader(
                'X-Original-Emails',
                json_encode($original)
            );
        }
    }
}
