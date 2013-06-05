# TedivmStashBundle

This bundle integrates the [Stash](https://github.com/tedivm/Stash) key-value cache library into Symfony2. Both the bundle and Stash are licensed under the New BSD License.

The bundle adds Stash information to the Web Profiler toolbar, provides a service for your own use, and integration with the Doctrine Common Cache which is part of the Doctrine ORM that is installed with the Symfony2 standard edition.



## Installation

For composer-based Symfony installations, to download the latest stable release:

```sh
php composer.phar require tedivm/stash-bundle:~0.1
```

To install the current development version
```sh
php composer.phar require tedivm/stash-bundle:dev-master
```

Then add the bundle to your `app/AppKernel.php` file:

```php
<?php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Tedivm\StashBundle\TedivmStashBundle(),
        // ...
    );
    ...
}
```

## Configuration

To configure StashBundle, create a `stash` key in your `app/config/config.yml` and register a cache name (`mycache` in the example below):

```yaml
stash:
    default_cache: mycache # Match the name of your cache below
    caches:
        mycache: # Choose your own cache name
            handlers:
                - FileSystem
            registerDoctrineAdapter:  true
            FileSystem:
                dirSplit:             2
                path:                 %kernel.cache_dir%/stash
                filePermissions:      432
                dirPermissions:       504
                memKeyLimit:          200
```

With this configuration, a `stash.mycache_cache` service is created for your own use, as well as a `stash.adapter.doctrine.mycache_cache` service, which provides a [Doctrine Cache](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/caching.html) interface to your Stash. Stash will store key entries in the `app/stash` folder (so make sure it exists and is writeable by your web server). Alternative backends include APC, Memcached, SQLite, Ephemeral (an in-memory cache that lasts only for the duration of the request) and BlackHole (similar, except that nothing is stored at all, even in memory). 

### SQLite handler

Instead of storing values in a directory structure, the SQLite handler uses an SQLite database file stored in the `app/stash` folder.

```yaml
stash:
    default_cache: mycache # Match the name of your cache below
    caches:
        mycache: # Choose your own cache name
            handlers:
                - SQLite
            registerDoctrineAdapter:  true
            SQLite:
                filePermissions:      432
                dirPermissions:       504
                busyTimeout:          500
                nesting:              0
                subhandler:           PDO
                path:                 %kernel.cache_dir%/stash
```

### APC/APCu handler

```yaml
stash:
    default_cache: mycache # Match the name of your cache below
    caches:
        mycache: # Choose your own cache name
            handlers:
                - APC
            registerDoctrineAdapter:  true
            Apc:
                ttl:                  300 # expire entries after 5 minutes
                namespace:            ~
```

### Memcached handler

```yaml
stash:
    default_cache: mycache # Match the name of your cache below
    caches:
        mycache: # Choose your own cache name
            handlers:
                - Memcache
            registerDoctrineAdapter:  true
            Memcache:
                servers:
                    - { server: 127.0.0.1, port: 11211, weight: 1 }
```


### Ephemeral and Blackhole handlers

```yaml
stash:
    default_cache: mycache # Match the name of your cache below
    caches:
        mycache: # Choose your own cache name
            handlers:
                - Ephemeral
            registerDoctrineAdapter:  true
```

```yaml
stash:
    default_cache: mycache # Match the name of your cache below
    caches:
        mycache: # Choose your own cache name
            handlers:
                - BlackHole
            registerDoctrineAdapter:  true
```

## Using the Doctrine Cache Adapter

In the example above, a Stash called `mycache` is configured, with `registerDoctrineAdapter: true`. This creates a `stash.adapter.doctrine.mycache_cache` service which you can use on anything that supports a Doctrine Cache adapter.

### Doctrine ORM Metadata, Query and Result caches

```yaml
doctrine:
    orm:
        metadata_cache_driver:
            type: service
            id: stash.adapter.doctrine.mycache_cache
        query_cache_driver:
            type: service
            id: stash.adapter.doctrine.mycache_cache
        result_cache_driver:
            type: service
            id: stash.adapter.doctrine.mycache_cache
```

