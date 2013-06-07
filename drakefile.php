<?php

/**
 * @file
 * Drakefile for Billund Bibliotek. Requires drake_reload.
 */

$api = 1;

$context = array(
  // Prod site alias.
  '@env.prod' => '@r.billund.prod',
  // Staging site alias.
  '@env.stg' => '@r.billund.stg',
  // Bootstrap makefile.
  'makefile' => 'https://raw.github.com/billundbib/ding-deploy/master/ding.make',
);

$tasks['build'] = array(
  'depends' => array('reload-situs'),
  'help' => 'Build site from nothing but a make file.',
  'context' => array(
    'root' => drake_argument(1, 'Directory to build to.'),
    'make-file' => context('makefile'),
  ),
);

$tasks['rebuild'] = array(
  'depends' => array('reload-situs'),
  'help' => 'Rebuild the current site.',
  'context' => array(
    'root' => context('@self:site:root'),
    'make-file' => context('makefile'),
  ),
);

$tasks['import-prod'] = array(
  'depends' => array('reload-import-site'),
  'help' => 'Import database form "Prod".',
  'context' => array(
    '@sync_source' => context('@env.prod'),
    '@sync_target' => drake_argument('1', "Target alias."),
  ),
);

$tasks['import-stg'] = array(
  'depends' => array('reload-import-site'),
  'help' => 'Import database form "Staging".',
  'context' => array(
    '@sync_source' => context('@env.stg'),
    '@sync_target' => drake_argument('1', "Target alias."),
  ),
);

$tasks['import-sql'] = array(
  'depends' => array('reload-import-file'),
  'help' => 'Import database form SQL dump.',
  'context' => array(
    '@sync_target' => drake_argument('1', "Target alias."),
    'file' => drake_argument('2', 'SQL file to load.'),
  ),
);

/*
 * Defines some way of loading an existing database from somewhere. It is
 * invoked by reload-import-site.
 *
 * This is just a normal task, but it is recommended that it's implemented by
 * depending on a reload helper task such as reload-sync-db or reload-import-db.
 */
$tasks['import-db'] = array(
  'depends' => array('reload-sync-db', 'sanitize'),
);

/*
 * Load a database from a SQL dump.
 */
$tasks['import-file'] = array(
  'depends' => array('reload-load-db', 'sanitize'),
);

/*
 * Custom sanitation function. Invoked by our own import-db.
 */
$tasks['sanitize'] = array(
  'action' => 'drush',
  'help' => 'Sanitizes database post-import.',
  'commands' => array(
    // Set site name to "Billund Bibliotek [hostname]"
    array('command' => 'vset', 'args' => array('site_name', 'Billund Bibliotek ' . php_uname('n'))),
  ),
);
