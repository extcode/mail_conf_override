.. include:: ../../Includes.txt

=============
Configuration
=============

::

   $GLOBALS['TYPO3_CONF_VARS']['MAIL'] = [
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
   ];

As the example shows, all e-mail configuration options can also be used for the
alternative configurations.
When the TransportFactory is configured, the values of the default configuration
are overwritten with the values for the corresponding e-mail address. This means
that you only need to configure those values that differ.