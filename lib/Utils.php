<?php
/**
 * User: greg simsic
 * Date: 7/30/19
 * Time: 7:17 AM
 */

namespace gregsimsic\deployerrecipes;

use Deployer\Deployer;

class Utils
{

    const CRAFT_IGNORE_DB_TABLES = [
        'assetindexdata',
        'assettransformindex',
        'cache',
        'sessions',
        'templatecaches',
        'templatecachecriteria',
        'templatecacheelements',
        'templatecachequeries'
    ];

    const CRAFT_MYSQLDUMP_DATA_ARGS = '--add-drop-table --comments --create-options --dump-date --no-autocommit --routines --set-charset --triggers ';

    const SHOW_TABLE_SIZES_QUERY = 'SELECT table_name AS `Table`, round(((data_length + index_length) / 1024 / 1024), 2) `Size in MB`  FROM information_schema.TABLES WHERE table_schema = "mfahudso_205hudson" ORDER BY (data_length + index_length) DESC;';

    /**
     * Returns an attribute of the named host
     *
     *
     * @param host
     * @param attr
     *
     * @return string
     */
    public static function getHostAttribute($host, $attr)
    {
        return Deployer::get()->hostSelector->getByHostnames($host)[0]->get($attr);
    }

    /**
     * Returns a formatted filename with timestamp.
     *
     * @param name
     *
     * @return string
     */
    public static function createDbDumpName($name)
    {
        return "_db_export_{$name}_" . date( 'Y-m-d_H-i-s' ) . ".sql";
    }

    /**
     * Returns a mysql connection command
     *
     * @param db_name
     * @param user
     * @param pass
     * @param filename
     *
     * @return string
     */
    public static function createMysqlCommand($db_name, $user, $pass)
    {
        return "mysql -u{$user} -p'{$pass}' {$db_name}";
    }

    /**
     * Returns a mysqldump command
     *
     * @param db_name
     * @param user
     * @param pass
     * @param filename
     *
     * @return string
     */
    public static function createMysqlDumpCommand($db_name, $user, $pass, $filename)
    {
        $excludeTables = '';
        $args = self::CRAFT_MYSQLDUMP_DATA_ARGS;
        foreach (self::CRAFT_IGNORE_DB_TABLES as $table) {
            $excludeTables .= "--ignore-table={$db_name}.craft_" . $table . ' ';
        }

        return "mysqldump -u{$user} -p'{$pass}' {$db_name} {$args} {$excludeTables} > {$filename}";
    }

    /**
     * Returns a mysql command to import a file
     *
     * @param db_name
     * @param user
     * @param pass
     * @param filename
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

