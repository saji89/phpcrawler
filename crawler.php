<?php
    require __DIR__.'/src/bootstrap.php';

    use phpcrawler\Crawler;

    if(php_sapi_name () == 'cli')
    {
        //0 => No errors, 1 => Only errors 2=> Show warnings and notices too
        switch(DEBUG) {
            case 0:
                error_reporting(0);
                break;
            case 1:
                error_reporting(E_ERROR);
                break;
            case 2:
                error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
                break;
        }

        //Instantiate the crawler
        $crawler = new phpcrawler\Crawler(URL);
    }else{
        die("Only CLI access is allowed");
    }