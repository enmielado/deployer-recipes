<?php

/**
 * Deployer recipes to sync a set of yml-defined folders between local & remote environments
 *
 */

namespace Deployer;

use \gregsimsic\deployerrecipes\Utils;

/**
 * Upload a directory -- does not delete existing files
 *
 */
desc('Upload a directory from a list of choices set in hosts.yml-- does not delete existing files');
task('sync:up', function () {

    // must pick a remote host
    if ( get('hostname') === 'localhost' ) {
        throw new \Exception("No remote host specified");
    }

    $dir = askChoice('What to upload?', get('sync_dirs'));

    upload($dir . '/', '{{deploy_path}}/' . $dir );
});

/**
 * Download a directory -- does not delete existing files
 *
 */
desc('Download a directory from a list of choices set in hosts.yml-- does not delete existing files');
task('sync:down', function () {

    // must pick a remote host
    if ( get('hostname') === 'localhost' ) {
        throw new \Exception("No remote host specified");
    }

    $dir = askChoice('What to download?', get('sync_dirs'));

    download('{{deploy_path}}/' . $dir . '/', $dir );
});