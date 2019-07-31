<?php
/*
 *
 * CRAFT 3 DEPLOY RECIPE
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
inventory('hosts.yml');

set('application', 'APP_NAME');
set('repository', 'https://gregsimsic@bitbucket.org/gregsimsic/XXXX.git');
set('keep_releases', 3);
set('default_stage', 'stage');

// Files & directories hared between deploys
set('shared_files', [
    '.env'
]);
set('shared_dirs', [
    'storage'
]);

// Files & directories removed after git pull on remote
set('remove_files', [
    '.babelrc',
    '.env.example',
    '.gitignore',
    'composer.json',
    'composer.lock',
    'delpoy.php',
    'gulpfile.babel.js',
    'hosts.example.yml',
    'package.json',
    'README.md',
    'site-setup'
]);
set('remove_dirs', [
    'src'
]);

// Writable dirs by web server
set('writable_dirs', [
    'XXX'
]);

// The list of directories given as options to the sync task -- no trailing slashes
set('sync_options_dirs', [
    'web/assets',
    'templates'
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
desc('Main Deploy Task');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'remove_files',
    'deploy:shared',
    'deploy:writable',
//    'deploy:vendors', // composer
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');