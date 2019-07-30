<?php

/**
 * My task tests
 *
 */

namespace Deployer;

// test task
task('test', function () {
    writeln('Hello world');
});

task('hostname', function () {
    writeln( Task\Context::get()->getHost()->getHostname() );
});

task('pwd', function () {

    $result = run('pwd');
    writeln("Current dir: $result");
});

task('disk_free', function() {
    $df = run('df -h /');
    writeln($df);
});

task('dump-config', function () {

    $data = loadConfig('project.yml');
    write($b = var_export($data, true));

    writeln( "<error>WP_HOME variable not found in local .env file</error>" );

})->local()->desc('Dump project.yml to command line');

task('get-test', function () {

    loadConfig('project.yml');

    $deployer = Deployer::get();

    $hosts = $deployer->hosts;

    foreach ($hosts as $host) {
        write($b = var_export($host->getHostname(), true));
    }


//    $data = get('application');

})->local()->desc('Dump project.yml to command line');
