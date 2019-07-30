<?php
/**
 * User: greg simsic
 * Date: 7/30/19
 * Time: 7:17 AM
 */

namespace gsimsic\deployerrecipes;

use Deployer\Deployer;

class Utils
{


    const CRAFT_IGNORED_DB_TABLES_STRING = "(
        'assetindexdata'
        'assettransformindex'
        'cache'
        'sessions'
        'templatecaches'
        'templatecachecriteria'
        'templatecacheelements'
        'templatecachequeries'
        )";

    const CRAFT_MYSQLDUMP_DATA_ARGS = "--add-drop-table --comments --create-options --dump-date --no-autocommit --routines --set-charset --triggers ";

    /**
     * Returns an attribute of the named host
     *
     *
     * @param string $host
     * @param string $attr
     *
     * @return string
     */
    public static function getHostAttribute($host = 'local', $attr)
    {
        return Deployer::get()->hostSelector->getByHostnames($host)[0]->get($attr);
    }

    /**
     * Returns a formatted filename with timestamp.
     *
     * @param $name
     *
     * @return string
     */
    public static function createDbDumpName($name)
    {
        return "_db_export_{$name}_" . date( 'Y-m-d_H-i-s' ) . ".sql";
    }

    /**
     * Returns a mysqldump command
     *
     * @param $db_name
     * @param $user
     * @param $pass
     * @param $filename
     *
     * @return string
     */
    public static function createMysqlDumpCommand($db_name, $user, $pass, $filename)
    {
        return "mysqldump -u{$user} -p'{$pass}' {$db_name} > {$filename}";
    }

    /**
     * Returns a mysql command to import a file
     *
     * @param $db_name
     * @param $user
     * @param $pass
     * @param $filename
     *
     * @return string
     */
    public static function createMysqlImportCommand($db_name, $user, $pass, $filename)
    {
        return "mysql -u{$user} -p'{$pass}' {$db_name} < {$filename}";
    }

    /**
     * ** NOT IN USE **
     *
     * Would return the values of a remote .env file
     *
     * @return false|string
     */
    public static function getEnv()
    {

        $tmpEnvFile = get( 'local_root' ) . '/.env-remote';
        download( get( 'current_path' ) . '/.env', $tmpEnvFile );

        // TODO: read contents of an ini-style file
        $envArray = '';

        // Cleanup tempfile
        runLocally( "rm {$tmpEnvFile}" );

        if ( empty($envArray) ) {
            writeln( "<error>.env file not found</error>" );
            return false;
        }

        return $envArray;
    }
}

