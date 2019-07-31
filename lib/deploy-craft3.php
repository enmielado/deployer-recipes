<?php
/*
 *
 * a craft deploy script
 * https://gist.github.com/mtwalsh/fce3c4aa416996e5900e8ac9f471dd6c
 * https://www.enovate.co.uk/blog/2018/07/23/atomic-deployment-with-deployer
 *
 * a laravel example
 * https://medium.com/@nickdenardis/zero-downtime-local-build-laravel-5-deploys-with-deployer-a152f0a1411f
 *
 * example:
 * https://antonydandrea.com/deploying-with-deployer/
 *
 *
 * shared server deployment: good info
 * https://discourse.roots.io/t/heres-deployer-recipes-to-deploy-bedrock/9896
 * https://github.com/FlorianMoser/plesk-deployer
 *
 * wordpress recipes: good info
 * https://github.com/cstaelen/deployer-wp-recipes
 * https://github.com/danielroe/deployer-wp-recipes
 *
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
set('repository', $project['repo']);
set('keep_releases', 3);

// Shared files/dirs between deploys
set('shared_files', $project['shared_files']);
set('shared_dirs', $project['shared_dirs']);

// Shared files/dirs between deploys
set('remove_files', $project['remove_files']);
set('remove_dirs', $project['remove_dirs']);

// Writable dirs by web server
set('writable_dirs', $project['writable_dirs']);

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

set('local_name', Utils::getHostAttribute('local', 'name') );
set('local_db_name', Utils::getHostAttribute('local', 'db_name') );
set('local_db_user', Utils::getHostAttribute('local', 'db_user') );
set('local_db_pass', Utils::getHostAttribute('local', 'db_pass') );
set('local_root', Utils::getHostAttribute('local', 'deploy_path') );

set('sync_dirs', Utils::getHostAttribute('local', 'sync_dirs') );


/////////////////////

desc('Test Task: dep t -o tt=rabbit');
task('t', function () {

    // this works: dep t -o tt=rabbit
    writeln( 'tt: '. get('tt') ); // rabbit

});

task('removefiles', function () {

    // abandon if host if local
    if ( get('hostname') === 'localhost' ) {
        throw new \Exception("Don't delete local files!");
    }

    foreach (get('remove_files') as $file) {
        run("rm -rf {{release_path}}/{$file}");
    }
    foreach (get('remove_dirs') as $file) {
        run("rm -rf {{release_path}}/{$file}");
    }
})->desc('Remove files and dirs');


// main deploy task (atomic)
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