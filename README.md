# PHPCrawler

A project to parse a website and get the count of all `<img>` tags in each link, within the site.

## Requirements

- Mysql
- PHPUnit

## Setup
- Setup the required dependencies using composer:
```ShellSession
$ composer install
```
- Create a new database, in the MySQL server, and run `setupdb.sql` in the newly created database,
 so as to setup the required tables.
- Update the database settings in the `config.ini` file.
- Set the desired debug level, in the verbosity section
    - **0** - Don't show any error.
    - **1** - Show only errors.
    - **2** - Show errors and warnings.
- In the `url` section, set the URL to be parsed and also the maximum number of 
URL's to be parsed for images, from the total list of links retrieved and stored
in the database.

## How to run

```ShellSession
$ php crawler.php
```


## How to run the test cases

```ShellSession
$ phpunit --bootstrap src/bootstrap.php src/Tests/CrawlerTest.php 
```

## Future Ideas
- Use Guzzle
- Rewrite using some minimal PHP framework like Slim or Lumen
- Implement better URL check
- Time limiting for execution
- Number of URL's based limiting
- Level based limiting
- Allow links from other domains
- Refactor code
- Create a setup file to initialise DB.
- Use `tput` linux command to get dimension of console window
- Implement proper unit tests, setup code coverage check, etc., using Travis CI, or other Continuous integration tool.