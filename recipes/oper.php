<?php

/**
 * Deployer general deploy recipes
 *
 */

namespace Deployer;

desc('Remove files and dirs');
task('oper:removefiles', function () {

    // abandon if host is local (or this could be and askConfirmation() )
    if ( get('hostname') === 'localhost' ) {
        throw new \Exception("Don't delete local files!");
    }

    foreach (get('remove_files') as $file) {
        run("rm -rf {{release_path}}/{$file}");
    }
    foreach (get('remove_dirs') as $file) {
        run("rm -rf {{release_path}}/{$file}");
    }
});