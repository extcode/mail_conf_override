<?php

namespace Extcode\MailConfOverride\Mail;

/*
 * This file is part of the package extcode/mail-conf-override.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Mail\TransportFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailMessage extends \TYPO3\CMS\Core\Mail\MailMessage
{
    /**
     * @var \Swift_Transport
     */
    protected $transport;

    /**
     * @var array
     */
    protected $mailSettings = [];

    /**
     * Sends the message.
     *
     * This is a short-hand method. It is however more useful to create
     * a Mailer instance which can be used via Mailer->send($message);
     *
     * @return bool whether the message was accepted or not
     */
    public function send(): bool
    {
        $this->initializeMailer();
        $this->sent = false;
        $this->mailer->send($this);
        $sentMessage = $this->mailer->getSentMessage();
        if ($sentMessage) {
            $this->sent = true;
        }
        return $this->sent;
    }

    /**
    */
    private function initializeMailer()
    {
        $this->injectMailSettings();
        $this->initializeTransport();

        $this->mailer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Mail\Mailer::class,
            $this->transport
        );
    }

    /**
     * This method is only used in unit tests
     *
     * @param array $mailSettings
     * @internal
     */
    public function injectMailSettings(array $mailSettings = null)
    {
        if (is_array($this->getFrom()) && ($this->getFrom()[0] instanceof \Symfony\Component\Mime\Address)) {
            $from = $this->getFrom()[0]->getAddress();
        }
        if (is_array($mailSettings)) {
            $this->mailSettings = $mailSettings;
        } elseif (is_array($GLOBALS['TYPO3_CONF_VARS']['MAIL']['overrides'][$from])) {
            $this->mailSettings = array_replace(
                (array)$GLOBALS['TYPO3_CONF_VARS']['MAIL'],
                (array)$GLOBALS['TYPO3_CONF_VARS']['MAIL']['overrides'][$from]
            );
            unset($this->mailSettings['overrides']);
        } else {
            $this->mailSettings = (array)$GLOBALS['TYPO3_CONF_VARS']['MAIL'];
        }
    }

    /**
     * Prepares a transport using the TYPO3_CONF_VARS configuration
     *
     * Used options:
     * $TYPO3_CONF_VARS['MAIL']['transport'] = 'smtp' | 'sendmail' | 'mail' | 'mbox'
     *
     * $TYPO3_CONF_VARS['MAIL']['transport_smtp_server'] = 'smtp.example.org';
     * $TYPO3_CONF_VARS['MAIL']['transport_smtp_port'] = '25';
     * $TYPO3_CONF_VARS['MAIL']['transport_smtp_encrypt'] = FALSE; # requires openssl in PHP
     * $TYPO3_CONF_VARS['MAIL']['transport_smtp_username'] = 'username';
     * $TYPO3_CONF_VARS['MAIL']['transport_smtp_password'] = 'password';
     *
     * $TYPO3_CONF_VARS['MAIL']['transport_sendmail_command'] = '/usr/sbin/sendmail -bs'
     *
     * @throws \TYPO3\CMS\Core\Exception
     * @throws \RuntimeException
     */
    private function initializeTransport()
    {
        $this->transport = $this->getTransportFactory()->get($this->mailSettings);
    }

    /**
     * @return TransportFactory
     */
    protected function getTransportFactory(): TransportFactory
    {
        return GeneralUtility::makeInstance(TransportFactory::class);
    }
}
