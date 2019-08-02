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

    writeln('');
    writeln('<comment>Uploading to ' . get('stage') . '</comment>');
    writeln('');

    $dir = askChoice('Choose a directory to upload:', get('sync_options_dirs'));

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

    writeln('');
    writeln('<comment>Downloading from ' . get('stage') . '</comment>');
    writeln('');

    $dir = askChoice('Choose a directory to download:', get('sync_options_dirs'));

    download('{{deploy_path}}/' . $dir . '/', $dir );
});

/**
 *
 * Sync a directory between remote sites on the same server with --delete flag
 *
 * TODO: this could be updated to work across 2 servers since the command is being run on the remote host
 *
 */
desc('Sync a directory between remote sites on the same server with --delete flag');
task('sync:remotes', function () {

    $hosts = Deployer::get()->hosts;

    $remoteHostsByName = [];
    $remoteHostNames = [];

    foreach ($hosts as $host) {
        // exclude local hosts
        if($host->getHostname() !== 'local') {
            $remoteHostsByName[$host->getHostname()] = $host;
            $remoteHostNames[] = $host->getHostname();
        }
    }

    $from = askChoice('Sync from:', $remoteHostNames);

    // remove 'from' from the choices
    if (($key = array_search($from, $remoteHostNames)) !== false) {
        unset($remoteHostNames[$key]);
    }

    $to = askChoice('Sync to:', $remoteHostNames);

    $toPath = host($to)->get('deploy_path');

    $dir = askChoice('Select a directory:', get('sync_options_dirs'));

    $confirm = askConfirmation('Copy '. $dir . ' from ' . $from . ' to ' . $to . '?');

    if ($confirm) {

        // run command on source($from) host
        on( host($from), function ($host) use ($toPath, $dir) {

//            writeln('rsync -a --delete ' . get('deploy_path') . '/' . $dir . '/ ' . $toPath . '/' . $dir . '');
            run('rsync -a --delete ' . get('deploy_path') . '/' . $dir . '/ ' . $toPath . '/' . $dir . '');

        });
    } else {
        writeln("Perhaps that's best.");
    }

});