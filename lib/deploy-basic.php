<?php
/*
 * BASIC DEPLOY FILE -- no git, non-atomic -- just a library of helpers
 *
 */

namespace Deployer;

require 'recipe/common.php';
require 'vendor/gregsimsic/deployer-recipes/recipes/db.php';
require 'vendor/gregsimsic/deployer-recipes/recipes/sync.php';
require 'vendor/gregsimsic/deployer-recipes/lib/Utils.php';

use \gregsimsic\deployerrecipes\Utils;
use \Symfony\Component\Yaml\Yaml;

inventory('hosts.yml');

$project = Yaml::parseFile('deploy.yml');

//set('default_stage', 'stage');
set('application', 'deploy test');

// Writable dirs by web server
set('writable_dirs', $project['writable_dirs']);

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

set('local_name', host('local')->get('name') );
set('local_db_name', host('local')->get('db_name') );
set('local_db_user', host('local')->get( 'db_user') );
set('local_db_pass', host('local')->get('db_pass') );
set('local_root', host('local')->get( 'deploy_path') );

set('sync_dirs', host('local')->get( 'sync_dirs') );

set('default_stage', 'stage');


/////////////////////

// test
desc('Test Task: dep t -o tt=rabbit');
task('t', function () {

    // this works: dep t -o tt=rabbit
    writeln( 'tt: '. get('tt') ); // rabbit

});

// main deploy task
// TODO: push db, sync:up assets, sync up templates
desc('Basic (No Git, No Atomic) Deploy Task');
task('deploy', [
    'deploy:info'
]);
