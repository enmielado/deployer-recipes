# Deployer Recipes



## Features

* Deploy site 
* Sync uploads
* Pull/push MySql databases to/from remotes and local

## Requirements

* [Deployer PHP](http://deployer.org/)

## Installation

run `composer require gregsimsic/deployer-recipes`.

Make sure to include recipe files in your `deploy.php`:

    require 'vendor/gregsimsic/deployer-recipes/recipes/db.php';

## Configuration



## Available tasks

Upload your WP database : `dep db:push prod`
Download your WP database : `dep db:pull prod`
Pull WP uploads with rsync : `dep uploads:pull prod`
Push WP uploads with rsync : `dep uploads:push prod`
Upload your local copy of WP uploads with rsync : `dep uploads:push prod`

You can also use those rules below in your `deploy.php` file to compile and deploy assets and cleanup some useless files on your staging/production server :

    after('deploy', 'deploy:cleanup');

You can use `deploy:assets` as part of your deploy process. For example:

    task('deploy', [
        'deploy:prepare',
        'deploy:lock',
        'deploy:release',
        'deploy:update_code',
        'deploy:shared',
        'deploy:vendors',
        'deploy:assets',
        'deploy:writable',
        'deploy:symlink',
        'deploy:unlock',
        'cleanup',
        'varnish:reload',
        'php-fpm:reload',
    ])->desc('Deploy your Bedrock project');
    after('deploy', 'success');

