TedivmStashBundle
=================

[![Build Status](https://secure.travis-ci.org/tedivm/TedivmStashBundle.png?branch=master)](http://travis-ci.org/tedivm/TedivmStashBundle)

The **TedivmStashBundle** integrates the [Stash caching library](https://github.com/tedivm/Stash) into Symfony, providing a
powerful abstraction for a range of caching engines.

## Installation ##

Add the bundle using composer, either by requiring it on the command line:

`composer require jms/serializer-bundle`

or by adding it directly to your `composer.json` file:

`"require": {
    "tedivm/stash-bundle": "dev-master"
}`

Add the bundle to `app/AppKernel.php`:

`public function registerBundles()
{
    return array(
        new Tedivm\StashBundle\TedivmStashBundle(),
    );
}`

And then set the basic configuration in `app/config/config.yml`:

`tedivm_stash: ~`

## Usage ##

Just fetch the default cache pool service:

`$pool = $this->container->get('cache');`

Or a custom-defined cache pool:

`$pool = $this->container->get('stash.custom_cache');`

Then you can use the cache service directly:

`
$item = $pool->getItem($id, 'info');

$info = $item->get();

if($item->isMiss())
{
    $info = loadInfo($id);
    $item->store($userInfo);
}

return $info;
`

(See the [Stash documentation](http://stash.tedivm.com/) for more information on using the cache service.)

## Configuration ##

### Single Cache ###

To get started quickly, you can define a single caching service with a single driver:

`tedivm_stash:
    cache:
        handlers: [ FileSystem]
        FileSystem: ~`


The complete configuration reference:

`tedivm_stash:

    # Use a single
