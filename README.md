# aniSeasons

## Prerequisites

- Have an AniList account
- Make [custom lists](https://anilist.co/settings/lists) in the `YYYY Season` format, ie:
  - `2022 Winter`
  - `2022 Spring`
  - `2022 Summer`
  - `2022 Fall`
  - `2023 Winter`
  - etc.
- Put your anime in those custom lists to when you expect to watch them

## Install

- Put this project in a webserver with PHP
- Make sure PHP can write in the folder
- Pull nodejs deps for our base CSS (`yarn` or `npm i`)
- Copy `config.php.dist` to `config.php` and edit it
  - Set `$user` to your username (the one we find in your profile URL)
  - Update the `$customLinks` array to your liking
- Open `/index.php` in your browser to see the results
- You can launch `php fetch.php` in CLI to force a refresh under 12 hours
- Add a cron to run `fetch.php` regularly maybe

## Notes

- The current code will expect tags in the exact same format (and in English) as listed in the prerequistes, so it doesn't get confused with any other custom lists you might create
- It will loop from 2010 Winter to and including 2050 Fall
- Data fetched from AniList is saved in `shows.json`, have fun doing what you want with it
