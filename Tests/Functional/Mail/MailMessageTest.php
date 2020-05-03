<?php

namespace Extcode\MailConfOverride\Tests\Functional\Mail;

/*
 * This file is part of the package extcode/contacts.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class MailMessageTest extends FunctionalTestCase
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var MailMessage
     */
    protected $mailMessage;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/mail_conf_override'
    ];

    /**
     * @var array[]
     */
    protected $configurationToUseInTestInstance = [
        'MAIL' => [
            'defaultMailFromAddress' => 'contact@example.com',
            'defaultMailFromName' => 'default',
            'transport' => 'smtp',
            'transport_smtp_encrypt' => 'tls',
            'transport_smtp_password' => 'very-secure-password',
            'transport_smtp_server' => 'mail.example.com:587',
            'transport_smtp_username' => 'contact@example.com',
            'overrides' => [
                'kontakt@example.de' => [
                    'transport_smtp_password' => 'sehr-sicheres-passwort',
                    'transport_smtp_server' => 'mail.example.de:587',
                    'transport_smtp_username' => 'kontakt',
                ],
                'contact@example.nl' => [
                    'transport_smtp_encrypt' => 'ssl',
                    'transport_smtp_password' => 'zeer-veilig-wachtwoord',
                    'transport_smtp_server' => 'smtp.example.nl:465',
                    'transport_smtp_username' => 'contact@example.nl',
                ],
                'contact@example.fr' => [
                    'transport' => 'sendmail',
                    'transport_sendmail_command' => '/usr/sbin/sendmail -t -f contact@example.fr',
                    'transport_smtp_encrypt' => '',
                    'transport_smtp_password' => '',
                    'transport_smtp_server' => '',
                    'transport_smtp_username' => '',
                ],
            ],
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->mailMessage = $this->objectManager->get(MailMessage::class);
    }

    public function tearDown(): void
    {
        unset($this->objectManager);
    }

    /**
     * @test
     */
    public function checkInstanceOfClass()
    {
        $this->assertTrue(
            is_subclass_of($this->mailMessage, MailMessage::class)
        );
        $this->assertInstanceOf(
            \Extcode\MailConfOverride\Mail\MailMessage::class,
            $this->mailMessage
        );
    }

    /**
     * @test
     */
    public function getDefaultMailConfigurationForUnsetFromAddress()
    {
        $this->mailMessage->injectMailSettings();

        $mailSettings = $this->getPrivateProperty(
            \Extcode\MailConfOverride\Mail\MailMessage::class,
            'mailSettings'
        )->getValue($this->mailMessage);

        $this->assertSame(
            'mail.example.com:587',
            $mailSettings['transport_smtp_server']
        );
        $this->assertSame(
            'contact@example.com',
            $mailSettings['transport_smtp_username']
        );
        $this->assertSame(
            'very-secure-password',
            $mailSettings['transport_smtp_password']
        );
    }

    /**
     * @test
     */
    public function getDefaultMailConfigurationForUnconfiguredFromAddress()
    {
        $this->mailMessage->setFrom('contatto@example.it');
        $this->mailMessage->injectMailSettings();

        $mailSettings = $this->getPrivateProperty(
            \Extcode\MailConfOverride\Mail\MailMessage::class,
            'mailSettings'
        )->getValue($this->mailMessage);

        $this->assertSame(
            'mail.example.com:587',
            $mailSettings['transport_smtp_server']
        );
        $this->assertSame(
            'contact@example.com',
            $mailSettings['transport_smtp_username']
        );
        $this->assertSame(
            'very-secure-password',
            $mailSettings['transport_smtp_password']
        );
    }

    /**
     * @test
     */
    public function getMailConfigurationForConfiguredFromAddress()
    {
        $this->mailMessage->setFrom('kontakt@example.de');
        $this->mailMessage->injectMailSettings();

        $mailSettings = $this->getPrivateProperty(
            \Extcode\MailConfOverride\Mail\MailMessage::class,
            'mailSettings'
        )->getValue($this->mailMessage);

        $this->assertSame(
            'mail.example.de:587',
            $mailSettings['transport_smtp_server']
        );
        $this->assertSame(
            'kontakt',
            $mailSettings['transport_smtp_username']
        );
        $this->assertSame(
            'sehr-sicheres-passwort',
            $mailSettings['transport_smtp_password']
        );

        $this->mailMessage->setFrom('contact@example.nl');
        $this->mailMessage->injectMailSettings();

        $mailSettings = $this->getPrivateProperty(
            \Extcode\MailConfOverride\Mail\MailMessage::class,
            'mailSettings'
        )->getValue($this->mailMessage);

        $this->assertSame(
            'smtp.example.nl:465',
            $mailSettings['transport_smtp_server']
        );
        $this->assertSame(
            'contact@example.nl',
            $mailSettings['transport_smtp_username']
        );
        $this->assertSame(
            'zeer-veilig-wachtwoord',
            $mailSettings['transport_smtp_password']
        );
    }

    /**
     * @test
     */
    public function getUnsetMailConfigurationForConfiguredFromAddressFromParentMailConfiguration()
    {
        $this->mailMessage->setFrom('kontakt@example.de');
        $this->mailMessage->injectMailSettings();

        $mailSettings = $this->getPrivateProperty(
            \Extcode\MailConfOverride\Mail\MailMessage::class,
            'mailSettings'
        )->getValue($this->mailMessage);

        $this->assertSame(
            'tls',
            $mailSettings['transport_smtp_encrypt']
        );
    }

    /**
     * @test
     */
    public function getMailConfigurationAllowsToOverrideTransport()
    {
        $this->mailMessage->setFrom('contact@example.fr');
        $this->mailMessage->injectMailSettings();

        $mailSettings = $this->getPrivateProperty(
            \Extcode\MailConfOverride\Mail\MailMessage::class,
            'mailSettings'
        )->getValue($this->mailMessage);

        $this->assertSame(
            'sendmail',
            $mailSettings['transport']
        );
        $this->assertNotEmpty(
            $mailSettings['transport_sendmail_command']
        );
        $this->assertEmpty(
            $mailSettings['transport_smtp_encrypt']
        );
        $this->assertEmpty(
            $mailSettings['transport_smtp_server']
        );
        $this->assertEmpty(
            $mailSettings['transport_smtp_username']
        );
        $this->assertEmpty(
            $mailSettings['transport_smtp_password']
        );
    }

    /**
     * getPrivateProperty
     *
     * @param string $className
     * @param string $propertyName
     *
     * @return \ReflectionProperty
     */
    protected function getPrivateProperty($className, $propertyName)
    {
        $reflector = new \ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }
}
