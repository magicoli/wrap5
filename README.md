# W.R.A.P. Legacy (Transitional)

![Stable](https://img.shields.io/github/release/magicoli/wrap5?label=stable&color=green&include_prerelease)
![GitHub Tag](https://img.shields.io/github/tag/magicoli/wrap5?label=latest&include_prereleases)
![GitHub commits since latest release](https://img.shields.io/github/commits-since/magicoli/wrap5/latest?label=dev)
![PHP](https://img.shields.io/badge/PHP-8.2+-7884bf)
[![License](https://img.shields.io/badge/license-AGPL--3.0-552b55)](LICENSE)
![GitHub Downloads (all assets, all releases)](https://img.shields.io/github/downloads/magicoli/wrap5/total)
[![Donate](https://img.shields.io/badge/-Donate-yellow)](https://magiiic.org/donate/)

This is a transitional package including the legacy version of the CMS (3.1.1) and a port of the command-line tools from version 5.5.0. It will not receive any further updates. All new development will take place in separate projects within the 6.x branch:

- **[magicoli/wrap-app](https://github.com/magicoli/wrap-app)**: a brand-new, modern application that covers not only the features of the legacy CMS but also a wide range of new capabilities.
- **[magicoli/wrap-tools](https://github.com/magicoli/wrap-tools)**: command-line tools only.

## Original Description

Wrap is a basic CMS, aimed to display mostly galleries of images or videos.
The idea is to allow the website maintainer to push media in subfolders.
The structure of the websites and the menus is detected automatically.

It is not intended to be a full-featured CMS. Instead, it allows to
automatically publish videos and pictures playlists.

It is designed for fast, efficient media transmission. Although it is
possible to make a pretty beautiful website with this system (and I did), it's
not the goal.

It is poorly documented, and requires PHP 8.2 or later.

## Installation

The command-line tools, from the Magiiic apt repository:

```bash
curl -fsSL https://apt.magiiic.com/magiiic-packaging.asc | sudo gpg --dearmor -o /usr/share/keyrings/magiiic-packaging.gpg
echo "deb [signed-by=/usr/share/keyrings/magiiic-packaging.gpg] https://apt.magiiic.com stable main" | sudo tee /etc/apt/sources.list.d/magiiic.list
sudo apt update && sudo apt install wrap5-tools
```

They are installed in `/usr/local/lib/wrap5-tools/bin`, added to the PATH of login shells (open a new session after installing). A `/opt/wrap/bin` folder added to the PATH, like a clone of this repository, still comes first.

The CMS is a separate package, `wrap3-cms`, made from the `wrap` submodule: see [magicoli/wrap3-cms](https://github.com/magicoli/wrap3-cms) for its installation.
