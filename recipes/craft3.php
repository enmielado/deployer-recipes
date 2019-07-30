<?php
/*
 *
 * a craft deploy script
 * https://gist.github.com/mtwalsh/fce3c4aa416996e5900e8ac9f471dd6c
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
require 'vendor/gregsimsic/deployer-recipes/lib/DeployerUtils.php';

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

set('local_db_name', Utils::getHostAttribute('local', 'db_name') );
set('local_db_user', Utils::getHostAttribute('local', 'db_user') );
set('local_db_pass', Utils::getHostAttribute('local', 'db_pass') );
set('local_root', Utils::getHostAttribute('local', 'deploy_path') );


/////////////////////

// test
task('t', function () {

    // this works: dep t -o tt=rabbit
    writeln( 'tt: '. get('tt') );

});

// upload assets
task('upload:assets', function () {

    // must pick a remote host
    if ( get('hostname') === 'localhost' ) {
        throw new \Exception("No remote host specified");
    }
    upload('{{local_root}}/assets/', '{{release_path}}/assets');
});

// upload assets
task('download:assets', function () {

    // must pick a remote host
    if ( get('hostname') === 'localhost' ) {
        throw new \Exception("No remote host specified");
    }
    download('{{release_path}}/assets','{{local_root}}/assets/');
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