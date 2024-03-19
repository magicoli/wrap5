# Web Reel Advanced Publishing: it's a W.R.A.P.

![Version 5.0.1-dev](https://badgen.net/badge/Version/5.0.1-dev/333333)
![Stable 5.0.0](https://badgen.net/badge/Stable/5.0.0/00aa00)
![Requires PHP 7.4](https://badgen.net/badge/PHP/7.4/7884bf)
![License AGPLv3 or later](https://badgen.net/badge/License/AGPLv3%20or%20later/552b55)

Wrap is a back to the basics CMS, aimed to display mostly galleries of images or videos.
The idea is to allow the website maintainer to push media in subfolders.
The structure of the websites and the menus is detected automatically.

It is not intended to be a full-featured CMS. Instead, it allows to
automatically publish videos and pictures playlists.

It is designed for fast, efficient media transmission. Although it is
possible to make a pretty beautiful website with this system (and I did), it's
not the goal.

## Installation

1. VERY IMPORTANT: **Install this project outside your web directory**, as it contains
  unprotected scripts and tools aimed to alter your disk content
    ```bash
    git clone https://github.com/magicoli/wrap wrap5 --branch 5.x
    sudo mv wrap5 /opt/
    cd /opt/wrap5/
    composer update # see below for php < 8.2
    npm update
    cp .htaccess wrap-loader.php /var/www/html/ # or where your web root is
    ```
2. make a "data" directory alongside your document root. E.g. if your document root is /var/www/html, create /var/www/data
    ```bash
    mkdir /var/www/data
    ```

### If Apache2 open_basedir is set

Add wrap folder, data folder, as well as ffmpeg and ffprobe binaries to base dir like this (adjust to your setup):
```
  php_admin_value open_basedir "/opt/wrap5:/usr/bin/ffmpeg:/usr/bin/ffprobe:/var/www/data:<<whatever config you already have>>"
```

### Note for PHP < 8.2

Project is compatible with PHP version 7.4 and later. However, it was built with PHP 8.2. 

No worry! If you encounter the error:
```
Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.2.0".
```

run from within your wrap directory: 
```bash
composer config platform.php 7.4 # or whatever version configured in Apache
composer update
git checkout -- composer.json # revert to allow future git updates
```
