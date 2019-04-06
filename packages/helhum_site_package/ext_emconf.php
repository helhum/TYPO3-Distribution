<?php
$EM_CONF[$_EXTKEY] = [
  'title' => 'Helhum TYPO3 Distribution Site Package',
  'description' => 'An example site package for Helhum TYPO3 Distribution',
  'category' => 'TYPO3 Console',
  'state' => 'stable',
  'uploadfolder' => 0,
  'createDirs' => '',
  'modify_tables' => '',
  'clearCacheOnLoad' => 0,
  'author' => 'Helmut Hummel',
  'author_email' => 'info@helhum.io',
  'author_company' => 'helhum.io',
  'version' => '0.1.0',
  'constraints' => [
    'depends' => [
      'typo3' => '9.5.0-9.5.99',
    ],
    'conflicts' => [
    ],
    'suggests' => [
    ],
  ],
];
