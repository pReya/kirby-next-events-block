<?php

Kirby::plugin('preya/kirby-next-events-block', [
  'blueprints' => [
    'blocks/next-events' => __DIR__ . '/blueprints/blocks/next-events.yml'
  ],
  'snippets' => [
    'blocks/next-events' => __DIR__ . '/snippets/blocks/next-events.php'
  ],
  'options' => [
    'cache' => true
  ]
]);
