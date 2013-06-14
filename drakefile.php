<?php

/**
 * @file
 * Drakefile for Billund Bibliotek. Requires drake_reload.
 *
 * Custom modifications should go at the end of the file to be retained by
 * drake-rebuild-generate.
 *
 * You can override standard tasks by simply moving them below the marker line
 * (search for "retained" to find it) and modifying them.
 */

$api = 1;

/*
 * Drake Reload settings. This allows us to re-run drg.
 */
$drake_reload = array(
  'core' => '6.x',
  'site_name' => 'Billund Bibliotek',
  'type' => 'ding',
  'ding_url' => 'git@github.com:billundbib/ding-deploy.git',
  'envs' => array(
    'prod' => array(
      'alias' => '@r.billund.prod',
      'name' => 'Prod',
    ),
    'stg' => array(
      'alias' => '@r.billund.stg',
      'name' => 'Staging',
    ),
  ),
);


/*
 * Global context.
 */
$context = array(
  // Drupal core version.
  'core' => '6.x',
  // Prod site alias.
  '@env.prod' => '@r.billund.prod',
  // Staging site alias.
  '@env.stg' => '@r.billund.stg',
  // ding_deploy repository.
  'repository' => 'git@github.com:billundbib/ding-deploy.git',
);

/*
 * Build site via ding_deploy.
 */
$tasks['build'] = array(
  'depends' => array('reload-ding-build'),
  'help' => 'Build site from a ding_deploy repo.',
  'context' => array(
    'root' => drake_argument(1, 'Directory to build to.'),
    'repo' => context('repository'),
  ),
);

/*
 * Rebuild site via ding_deploy.
 */
$tasks['rebuild'] = array(
  'depends' => array('reload-ding-rebuild'),
  'help' => 'Rebuild the current site.',
  'context' => array(
    'root' => context('@self:site:root'),
    'repo' => context('repository'),
  ),
);

/*
 * Import database from "Prod".
 */
$tasks['import-prod'] = array(
  'depends' => array('reload-import-site'),
  'help' => 'Import database form "Prod".',
  'context' => array(
    '@sync_source' => context('@env.prod'),
    '@sync_target' => drake_argument('1', "Target alias."),
  ),
);

/*
 * Import database from "Staging".
 */
$tasks['import-stg'] = array(
  'depends' => array('reload-import-site'),
  'help' => 'Import database form "Staging".',
  'context' => array(
    '@sync_source' => context('@env.stg'),
    '@sync_target' => drake_argument('1', "Target alias."),
  ),
);

/*
 * Import database from a file.
 */
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
  'depends' => array('reload-ding-fix-error-level', 'sanitize-drush', 'reload-fix-mobile-tools'),
  'help' => 'Sanitizes database post-import.',
);

/*
 * Runs misc sanitation drush commands.
 */
$tasks['sanitize-drush'] = array(
  'action' => 'drush',
  'commands' => array(
    // Disable trampoline first thing, or else it'll kill everything later on.
    // Same for memcache_admin.
    array(
      'command' => 'pm-disable',
      'args' => array('trampoline', 'memcache_admin', 'securepages', 'y' => TRUE),
    ),
    // Set site name to "Billund Bibliotek [hostname]"
    array(
      'command' => 'vset',
      'args' => array('site_name', 'Billund Bibliotek ' . php_uname('n')),
    ),
  ),
);

/*
 * Regenerate drakefile.
 */
$tasks['redrake'] = array(
  'action' => 'drush',
  'help' => 'Regenerate the drakefile using drake-reload-generate',
  'command' => 'drake-reload-generate',
  'args' => array(__FILE__, 'y' => TRUE),
);

// ### Everything below this will be retained by drush-reload-generate ###

