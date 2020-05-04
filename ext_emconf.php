<?php

$EM_CONF['mail_conf_override'] = [
    'title' => 'Mail Conf Override',
    'description' => 'The extension allows different mail configurations for mail from addresses.',
    'category' => 'plugin',
    'shy' => false,
    'version' => '1.0.0',
    'dependencies' => '',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'modify_tables' => '',
    'clearcacheonload' => true,
    'lockType' => '',
    'author' => 'Daniel Gohlke',
    'author_email' => 'ext@extco.de',
    'author_company' => 'extco.de UG (haftungsbeschränkt)',
    'CGLcompliance' => null,
    'CGLcompliance_note' => null,
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
