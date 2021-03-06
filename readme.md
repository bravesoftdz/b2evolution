![b2evolution CMS](media/shared/global/logos/b2evolution_1016x208_wbg.png)

# b2evolution CMS

This is b2evolution CMS version **6.9.7**.

## A complete engine for your website !

Multiblog/CMS + user community + email marketing + social network + more...
b2evolution includes everything you need to run and maintain a modern website.
Plus, it's optimized for low maintenance with easy upgrades and effective antispam. Full Bootstrap & RWD support.

More info: http://b2evolution.net

## Differences to upstream

This is [a fork](https://github.com/keithbowes/b2evolution) of the [upstream project](https://github.com/b2evolution/b2evolution).  I regularly merge with upstream and push it to GitHub.  The primary changes in this fork are:

1.  Includes changes to validate as XHTML.  In general that means replaces the entities &amp;nbsp;, &amp;raquo;, &amp;laquo;, &amp;middot;, and &amp;hellip; with their UTF-8 literals or numerical references.
1.  Sorting of categories.  Categories are sorted alphabetically rather than based on ID.
1.  I still use xg.php (where upstream seems to have settled for xgmac.sh and completely removed xg.php), though I've made several changes.

## Requirements

Basically, all you need is a standard web host with PHP 5.3+ (5.6.x or 7.x recommended), MySQL 5+ (5.5+ recommended) or MariaDB 5+ (10+ recommended), and a web server (Apache 2.4+ recommended for the automatic use of clean slugs).

b2evolution 6.6+ requires the PHP mysqli extension.  If your host only offers the mysql extension (which has been deprecated in PHP 5.5 and removed in PHP 7.0), you'll have to use an older version.  You should be able to see which PHP extensions are supported by your web host in your host's control panel.

## Downloading

### With Bower

If you're familiar with bower, just type: `bower install b2evolution`

### Manual Download

You can download releases either from GitHub or from b2evolution.net :

- https://github.com/b2evolution/b2evolution/releases
- http://b2evolution.net/downloads/

## Installation: Amazing 3-minute install ;)

Upload everything to your web server and call the installation script that you will find at `/install/index.php` on your website. Then you just need to enter your database connection details and the installer will take care of everything for you.

Now, you might ask for more details here... Totally legitimate! Please check out our [Getting Stated - Installation Guide](http://b2evolution.net/man/getting-started).

Hint: It is possible to install b2evolution in less than 3 minutes. Probably not the first time though. (And the same is true for anyone else claiming a 5 minute install process.)

## Upgrading

### Automatic upgrade

b2evolution includes an automatic upgrade feature which you can use to automatically download the lastest stable version and perform the upgrade operations.

### Manual upgrade

You can download any newer version (including beta releases), overwrite the files of your current installation (after backup) and then run the install script.

The installation script will detect that the b2evolution database is already installed (any version) and offer to upgrade it to the current version.

There are [several other upgrade options](http://b2evolution.net/man/upgrading).

## GitHub

All bug fixes and all new code are made available through GitHub before being packaged as releases. If you are interested in cutting-edge versions, we recommend you [follow us on GitHub](https://github.com/b2evolution/b2evolution).
