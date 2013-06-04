TedivmStashBundle
=================

[![Build Status](https://secure.travis-ci.org/tedivm/TedivmStashBundle.png?branch=master)](http://travis-ci.org/tedivm/TedivmStashBundle)

The **TedivmStashBundle** integrates the [Stash caching library](https://github.com/tedivm/Stash) into Symfony, providing a
powerful abstraction for a range of caching engines.

## Installation ##

Add the bundle using composer, either by requiring it on the command line:

    composer require jms/serializer-bundle

or by adding it directly to your `composer.json` file:

    "require": {
        "tedivm/stash-bundle": "dev-master"
    }

Add the bundle to `app/AppKernel.php`:

    public function registerBundles()
    {
        return array(
            new Tedivm\StashBundle\TedivmStashBundle(),
        );
    }

And then set the basic configuration in `app/config/config.yml`:

`tedivm_stash: ~`

## Usage ##

Just fetch the default cache pool service:

`$pool = $this->container->get('cache');`

Or a custom-defined cache pool:

`$pool = $this->container->get('stash.custom_cache');`

Then you can use the cache service directly:

    $item = $pool->getItem($id, 'info');

    $info = $item->get();

    if($item->isMiss())
    {
        $info = loadInfo($id);
        $item->store($userInfo);
    }

    return $info;

(See the [Stash documentation](http://stash.tedivm.com/) for more information on using the cache service.)

## Configuration ##

### Default Cache Service ###

To get started quickly, you can define a single caching service with a single driver:

    tedivm_stash:
        cache:
            handlers: [ FileSystem]
            FileSystem: ~

This cache service will be registered as `stash.default_cache`, which will also be automatically aliased to `cache`.

### Configuring Drivers ###

You can set the individual parameters of the cache driver directly in the configuration:

    tedivm_stash:
        cache:
            handlers: [ FileSystem ]
            FileSystem:
                dirSplit: 3
                path: /tmp

### Multiple Drivers ###

If you want to use multiple drivers in sequence, you can list them separately:

    tedivm_stash:
        cache:
            handlers: [ Apc, FileSystem ]
            Apc: ~
            FileSystem:
                path: /tmp

The cache service will automatically be configured with a Composite handler, with the drivers queried in the specified
order (for example, in this example, Apc would be queried first, followed by FileSystem if that query failed.)

### In-Memory ###

By default, every cache service includes in-memory caching: during the lifetime of a single request, any values stored
or retrieved from the cache service will be stored in memory, with the in-memory representation being checked before
any other drivers. In some circumstances, however (such as long-running CLI batch scripts) this may not be desirable.
In those cases, the in-memory handler can be disabled:

     tedivm_stash:
        cache:
            handlers: [ Apc ]
            inMemory: false
            Apc: ~

### Doctrine Adapter ###

Stash provides a Doctrine cache adapter so that your Stash caching service can be injected into any service that takes
a DoctrineCacheInterface object. To turn on the adapter for a service, set the parameter:

     tedivm_stash:
        cache:
            handlers: [ Apc ]
            registerDoctrineAdapter: true
            Apc: ~

For the default cache, the Adapter service will be added to the container under the name
`stash.adapter.doctrine.default_cache`.

### Multiple Services ###

You can also configure multiple services, each of which is entirely distinct:


     tedivm_stash:
        caches:
            first:
                handlers: [ FileSystem ]
                registerDoctrineAdapter: true
                FileSystem: ~
            second:
                handlers: [ Apc, FileSystem ]
                inMemory: false
                FileSystem ~

Each service is defined with keys inside a separate, distinct internal namespace, so you can use multiple services to
avoid key collisions between distinct services even if you only have a single backend available.

When multiple caches are defined, you can manually define a default, which will be aliased to the `stash` service:

    tedivm_stash:
        default_cache: first
        first:
            ...
        second:
            ...

However, if you don't, the first service defined will be set as the default.
