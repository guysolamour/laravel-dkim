<?php

namespace Guysolamour\Dkim;

use Illuminate\Mail\SentMessage;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Guysolamour\Dkim\Exception\MissingConfigurationException;


class Mailer extends \Illuminate\Mail\Mailer
{
      /**
     * Send a new message using a view.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($view, array $data = [], $callback = null)
    {
        if ($view instanceof MailableContract) {
            return $this->sendMailable($view);
        }

        $data['mailer'] = $this->name;

        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
        [$view, $plain, $raw] = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        if (! is_null($callback)) {
            $callback($message);
        }

        $this->addContent($message, $view, $plain, $raw, $data);

        // If a global "to" address has been set, we will set that address on the mail
        // message. This is primarily useful during local development in which each
        // message should be delivered into a single mail address for inspection.
        if (isset($this->to['address'])) {
            $this->setGlobalToAndRemoveCcAndBcc($message);
        }

        // Next we will determine if the message should be sent. We give the developer
        // one final chance to stop this message and then we will send it to all of
        // its recipients. We will then fire the sent event for the sent message.
        // $symfonyMessage = $message->getSymfonyMessage();

        // PATCH NOW
        $symfonyMessage = $this->getSignedEmail($message);
        // END PATCH

        if ($this->shouldSendMessage($symfonyMessage, $data)) {
            $symfonySentMessage = $this->sendSymfonyMessage($symfonyMessage);

            if ($symfonySentMessage) {
                $sentMessage = new SentMessage($symfonySentMessage);

                $this->dispatchSentEvent($sentMessage, $data);

                return $sentMessage;
            }
        }
    }

     /**
     * Patch email with DKIM signer
     *
     * @param \Illuminate\Mail\Message $message
     * @return \Symfony\Component\Mime\Email
     */
    protected function getSignedEmail($message)
    {
        $symfonyMessage = $message->getSymfonyMessage();

        if (config('dkim.enabled') !== true) {
            return $symfonyMessage;
        }

        if (!in_array(strtolower(config('mail.default')), ['smtp', 'sendmail', 'log', 'mail'])) {
            return $symfonyMessage;
        }

        // PATCH START
        $privateKey = config('dkim.private_key');
        $selector   = config('dkim.selector');
        $domain     = config('dkim.domain');


        if (empty($privateKey)) {
            throw new MissingConfigurationException('No private key set.', 1588115551);
        }

        if (!file_exists($privateKey)) {
            throw new MissingConfigurationException('Private key file does not exist.', 1588115609);
        }

        if (empty($selector)) {
            throw new MissingConfigurationException('No selector set.', 1588115373);
        }

        if (empty($domain)) {
            throw new MissingConfigurationException('No domain set.', 1588115434);
        }

        $signer = new DkimSigner(file_get_contents($privateKey), $domain, $selector, [], config('dkim.passphrase'));
        $signedEmail = $signer->sign($message->getSymfonyMessage());
        $symfonyMessage->setHeaders($signedEmail->getHeaders());
        // PATCH END

        return $symfonyMessage;
    }
}
