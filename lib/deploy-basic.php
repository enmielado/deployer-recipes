<?php
/*
 *
 * BASIC DEPLOY RECIPE -- no git, non-atomic
 *
 *
 */

namespace Deployer;

require 'recipe/common.php';
require 'vendor/gregsimsic/deployer-recipes/recipes/db.php';
require 'vendor/gregsimsic/deployer-recipes/recipes/sync.php';
require 'vendor/gregsimsic/deployer-recipes/recipes/oper.php';
require 'vendor/gregsimsic/deployer-recipes/lib/Utils.php';

/**
 *  CONFIG
 *
 */

// read hosts from config
inventory('deploy.yml');

set('application', 'APP_NAME');

set('default_stage', 'stage');

// The list of directories given as options to the sync task -- no trailing slashes
set('sync_options_dirs', [
    'public/assets',
    'craft/templates',
    'craft/plugins/hudson205',
    'public/media'
]);

/**
 *  TASKS
 *
 */

desc('Custom Task');
task('custom', function () {

    writeln( 'This is a custom task' );

});

/**
 *  DEPLOY TASK
 *
 */
// TODO: push db, sync:up files (assets, templates, etc.)
desc('Basic Deploy Task (No Git, Not Atomic)');
task('deploy', [
    'custom'
]);
