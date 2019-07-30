<?php

/**
 * Deployer recipes to push and pull mysql databases between local & remote environments
 *
 */

namespace Deployer;

use \gregsimsic\deployerrecipes\Utils;

/**
 * Create symlink to find Mamp mysql.sock
 *
 */
task( 'mysql:ln', function () {
    runLocally( "ln -s /Applications/MAMP/tmp/mysql/mysql.sock /tmp/mysql.sock" );
});

/**
 * Create a backup of the database (on any server)
 *  defaults to local
 *
 */
task( 'backup:db', function ()  {

    // Note: there is also a craft cli command: ./craft backup/db

    $DbBackupFilename = Utils::createDbDumpName( get('name'));
    $mysqlDumpCommand = Utils::createMysqlDumpCommand(
        get('db_name'),
        get('db_user'),
        get('db_pass'),
        $DbBackupFilename
    );

    writeln( "<comment>Exporting DB to {{deploy_path}}/{{db_backups_dir}}/{$DbBackupFilename}</comment>" );

//    writeln($mysqlDumpCommand); return false;

    run("mkdir -p {{deploy_path}}/{{db_backups_dir}}");
    run( "cd {{deploy_path}}/{{db_backups_dir}} && " . $mysqlDumpCommand );
    run( "gzip -f {{deploy_path}}/{{db_backups_dir}}/{$DbBackupFilename}" );

});

/**
 * Pull the database from a remote server and dump it into the local database
 **
 */
task( 'pull:db', function ()  {

    // check for server -- can't be local
    if ( get('hostname') === 'localhost' ) {
        throw new \Exception("You must specifiy a server to pull from.");
    }

    $remoteDumpFilename = Utils::createDbDumpName( get('name'));
    $remoteDumpCommand = Utils::createMysqlDumpCommand(
        get('db_name'),
        get('db_user'),
        get('db_pass'),
        $remoteDumpFilename
    );

    $localDumpFilename = Utils::createDbDumpName( get('local_name'));
    $localDbName = get( 'local_db_name');
    $localDbUser = get( 'local_db_user');
    $localDbPass = get( 'local_db_pass');

    $localDumpCommand = Utils::createMysqlDumpCommand( $localDbName, $localDbUser, $localDbPass, $localDumpFilename );
    $localImportCommand = Utils::createMysqlImportCommand( $localDbName, $localDbUser, $localDbPass, $remoteDumpFilename );

    // dump remote
    writeln( "<comment>Exporting DB to {$remoteDumpFilename}</comment>" );
    run( "cd {{deploy_path}} && " . $remoteDumpCommand );
    run( "gzip -f {{deploy_path}}/{$remoteDumpFilename}" );

    // download remote dump
    writeln( "<comment>Downloading DB export to {$remoteDumpFilename}</comment>" );
    download( get('deploy_path') . '/' . $remoteDumpFilename . '.gz', $remoteDumpFilename . '.gz' );

    // Delete remote dump on server
    writeln( "<comment>Cleaning up {$remoteDumpFilename} on server</comment>" );
    run( "rm {{deploy_path}}/{$remoteDumpFilename}" . '.gz' );

    // backup local db
    writeln( "<comment>Dumping Local DB backup up {$localDumpFilename}</comment>" );
    runLocally( $localDumpCommand );

    // unzip remote dump
    runLocally( "gzip -d {$remoteDumpFilename}" . '.gz' );

    // import remote db to local
    writeln( "<comment>Importing Remote DB to local DB</comment>" );
    try {
        runLocally( $localImportCommand );
    } catch (\Throwable $exception) {
        die($exception->getMessage());
    }

    // delete local backup & downloads
    writeln( "<comment>Deleting local dump {$localDumpFilename} & downloads</comment>" );
    runLocally( "rm {$localDumpFilename}" );
    runLocally( "rm {$remoteDumpFilename}" );

});

/**
 * Push the local database to a remote server
 *
 * TODO: check this task
 *
 */
task( 'push:db', function ()  {

    // check for server -- can't be local
    if ( get('hostname') === 'localhost' ) {
//        Context::get()->getHost()->getHostname();
        throw new \Exception("You must specifiy a server to push to.");
    }

    $localDumpFilename = Utils::createDbDumpName( get('local_name'));
    $localDumpCommand = Utils::createMysqlDumpCommand(
        get( 'local_db_name'),
        get( 'local_db_user'),
        get( 'local_db_pass'),
        $localDumpFilename
    );

    $remoteDumpFilename = Utils::createDbDumpName( get('name'));
    $remoteDbName = get('db_name');
    $remoteDbUser = get('db_user');
    $remoteDbPass = get('db_pass');

    $remoteDumpCommand = Utils::createMysqlDumpCommand( $remoteDbName, $remoteDbUser, $remoteDbPass, $remoteDumpFilename );
    $remoteImportCommand = Utils::createMysqlImportCommand( $remoteDbName, $remoteDbUser, $remoteDbPass, $localDumpFilename );

    // dump local db
    writeln( "<comment>Dumping Local DB backup up {$localDumpFilename}</comment>" );
    runLocally( $localDumpCommand );

    // backup remote db
    writeln( "<comment>backing up remote DB to {$remoteDumpFilename}</comment>" );
    run( "cd {{deploy_path}} && " . $remoteDumpCommand );

    // upload local dump
    writeln( "<comment>Uploading DB export to {{deploy_path}}/{$localDumpFilename}</comment>" );
    upload( $localDumpFilename, get('deploy_path') . '/' . $localDumpFilename );

    // delete local backup
    writeln( "<comment>Deleting local dump {$localDumpFilename}</comment>" );
    runLocally( "rm {$localDumpFilename}" );

    // import remote db to local
    writeln( "<comment>Importing local DB remote</comment>" );
    try {
        run( $remoteImportCommand );
    } catch (\Throwable $exception) {
        throw new \Exception($exception->getMessage());
    }

    // Delete dump on server
    writeln( "<comment>Cleaning up {$localDumpFilename} on server</comment>" );
    run( "rm {{deploy_path}}/{$localDumpFilename}" );

});

/**
 * Check Db Connection
 *
 */
task( 'check:db', function () {

    $name = get('db_name');
    $user = get('db_user');
    $pass = get('db_pass');
    $host = get('hostname');

    $cmd = Utils::createMysqlCommand($name, $user, $pass);

    try {
        run($cmd);
    } catch (\Exception $exception) {
        throw new \Exception("Could not connect to  {$name} on {$host}<br/>: {$exception->getMessage()}");
    }

    writeln( "<comment>Fantastic! Connected to {$name} on {$host}</comment>" );

});