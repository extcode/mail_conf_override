<?php

namespace Extcode\MailConfOverride\Mail;

/*
 * This file is part of the package extcode/mail-conf-override.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\MailUtility;

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
     * @return int the number of recipients who were accepted for delivery
     */
    public function send()
    {
        // Ensure to always have a From: header set
        if (empty($this->getFrom())) {
            $this->setFrom(MailUtility::getSystemFrom());
        }
        $this->initializeMailer();
        $this->sent = true;
        $this->getHeaders()->addTextHeader('X-Mailer', $this->mailerHeader);
        return $this->mailer->send($this, $this->failedRecipients);
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
        $from = array_keys($this->getFrom())[0];
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
        switch ($this->mailSettings['transport']) {
            case 'smtp':
                // Get settings to be used when constructing the transport object
                list($host, $port) = preg_split('/:/', $this->mailSettings['transport_smtp_server']);
                if ($host === '') {
                    throw new \TYPO3\CMS\Core\Exception('$TYPO3_CONF_VARS[\'MAIL\'][\'transport_smtp_server\'] needs to be set when transport is set to "smtp"', 1291068606);
                }
                if ($port === null || $port === '') {
                    $port = '25';
                }
                $useEncryption = $this->mailSettings['transport_smtp_encrypt'] ?: null;
                // Create our transport
                $this->transport = \Swift_SmtpTransport::newInstance($host, $port, $useEncryption);
                // Need authentication?
                $username = $this->mailSettings['transport_smtp_username'];
                if ($username !== '') {
                    $this->transport->setUsername($username);
                }
                $password = $this->mailSettings['transport_smtp_password'];
                if ($password !== '') {
                    $this->transport->setPassword($password);
                }
                break;
            case 'sendmail':
                $sendmailCommand = $this->mailSettings['transport_sendmail_command'];
                if (empty($sendmailCommand)) {
                    throw new \TYPO3\CMS\Core\Exception('$TYPO3_CONF_VARS[\'MAIL\'][\'transport_sendmail_command\'] needs to be set when transport is set to "sendmail"', 1291068620);
                }
                // Create our transport
                $this->transport = \Swift_SendmailTransport::newInstance($sendmailCommand);
                break;
            case 'mbox':
                $mboxFile = $this->mailSettings['transport_mbox_file'];
                if ($mboxFile == '') {
                    throw new \TYPO3\CMS\Core\Exception('$TYPO3_CONF_VARS[\'MAIL\'][\'transport_mbox_file\'] needs to be set when transport is set to "mbox"', 1294586645);
                }
                // Create our transport
                $this->transport = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Mail\MboxTransport::class, $mboxFile);
                break;
            case 'mail':
                // Create the transport, no configuration required
                $this->transport = \Swift_MailTransport::newInstance();
                break;
            default:
                // Custom mail transport
                $customTransport = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($this->mailSettings['transport'], $this->mailSettings);
                if ($customTransport instanceof \Swift_Transport) {
                    $this->transport = $customTransport;
                } else {
                    throw new \RuntimeException($this->mailSettings['transport'] . ' is not an implementation of \\Swift_Transport,
							but must implement that interface to be used as a mail transport.', 1323006478);
                }
        }
    }
}
