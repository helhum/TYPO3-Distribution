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
      'typo3' => '8.7.0-8.7.99',
    ],
    'conflicts' => [
    ],
    'suggests' => [
    ],
  ],
];
