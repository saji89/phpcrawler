<?php
    $loader = require __DIR__.'/../vendor/autoload.php';
    $loader->add('phpcrawler', __DIR__.'/../src/');

    $config = parse_ini_file("config.ini", true);

    // Define configuration
    define("DB_HOST", $config['database']['host']);
    define("DB_USER", $config['database']['user']);
    define("DB_PASS", $config['database']['pass']);
    define("DB_NAME", $config['database']['dbname']);
    define("DEBUG", $config['verbosity']['debuglevel']);
    define("URL", $config['url']['location']);
    define("MAXURLCOUNT", $config['url']['maxurlstocrawl']);