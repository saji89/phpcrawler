<?php
    /**
     * Crawler
     *
     * @package phpcrawler
     */

    namespace std;
    namespace phpcrawler;

    class Crawler
    {
        private $url;
        private $curl;
        private $database;
        private $linksList;

        /**
         * Constructor
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         * @param $url The URL to be crawled
         */
        public function __construct($url)
        {
            $this->url = $url;

            // Instantiate database.
            $this->database = new \Database();

            $this->emptyDbTables();

            echo "\nParsing URL for links.\n\n";
            $this->parseUrlForLinks($this->url);

            echo "Links fetched and stored in database.\n\n";
            $linksList = $this->getAllLinksInDatabase();

            echo "Finding image count in each link, this might take some time, depending "
            ."on number of URL's to be parsed.\n\n";

            echo "Please wait while the crawler is processing.\n\n";

            $this->iterateOverLinks($linksList);

            echo "Crawling completed, here is the final report:\n\n";

            $this->generateReport();
        }

        /**
         * Initialise curl
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         */
        private function initialiseCurl()
        {
            $this->curl = curl_init();
        }

        /**
         * Close curl
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         */
        private function closeCurl()
        {
            curl_close($this->curl);
        }

        /**
         * Get URL is curl
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         * @return mixed|null The HTML content returned from CURL
         */
        public function getUrlInCurl($url)
        {
            $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml, text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
            $header[] = "Cache-Control: max-age=0";
            $header[] = "Connection: keep-alive";
            $header[] = "Keep-Alive: 300";
            $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
            $header[] = "Accept-Language: en-us,en;q=0.5";

            $curlOptions = array(
                CURLOPT_RETURNTRANSFER => true,     // return web page
                CURLOPT_HEADER => false,    // don't return headers
                CURLOPT_FOLLOWLOCATION => true,     // follow redirects
                CURLOPT_ENCODING => "",       // handle all encodings
                CURLOPT_USERAGENT => "PHP crawler", // USER agent string
                CURLOPT_AUTOREFERER => true,     // set referrer on redirect
                CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
                CURLOPT_TIMEOUT => 120,      // timeout on response
                CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects,
                CURLOPT_URL => $url,
                CURLOPT_SSL_VERIFYPEER => $url,
                CURLOPT_SSL_VERIFYHOST => $url,
            );

            curl_setopt_array($this->curl, $curlOptions);

            $html = curl_exec($this->curl); // execute the curl command

            if ( ! $html) {
                if (DEBUG > 1) {
                    echo "<pre>";
                    var_dump(curl_error($this->curl));
                    die("HTML could not be retrieved.");
                } else {
                    return null;
                }
            } else {
                //Get CURL transfer information
                $info = curl_getinfo($this->curl);

                if ($info['http_code'] == 200) {
                    return $html;
                }
            }
        }

        /**
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         * @param $html
         *
         * @return \DOMDocument An instance using available HTML content
         */
        public function createDomDocument($html)
        {
            // Create a new DOM Document
            $dom = new \DOMDocument();

            $dom->recover = true;
            $dom->strictErrorChecking = false;

            // Load the url's contents into the DOM
            $dom->loadHTML($html);

            return $dom;
        }

        /**
         * Parse DOM and find links in it, store in db
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         * @param string $html HTML page content
         */
        public function getLinks($html)
        {
            // Create a new DOM Document
            $dom = $this->createDomDocument($html);

            //Loop through each <a> tag in the dom and add it to the link array
            foreach ($dom->getElementsByTagName('a') as $link) {
                $currentHref = $link->getAttribute('href');

                $url_parts = parse_url($currentHref);

                //Ensure that link is not empty, and not a hash link
                if ($this->isLinkAcceptable($currentHref, $url_parts)) {
                    //Removing all query strings, this might cause problems in non SEO optimised sites,
                    //but handling such cases need to be looked into, later

                    $currentHref = $this->reconstruct_url($url_parts);

                    $this->database->query("INSERT INTO links (url, created) VALUES (:url, now())");

                    $this->database->bind(':url', $currentHref);

                    $this->database->execute();
                }

                //TODO: Handle the possibililty of relative links, w.r.t parent link

                //TODO: Handle recursion and parent level and parent link id storing
            }
        }

        /**
         * Parse the URL to fetch all unique and acceptable links in it
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         * @param $url The URL to be parsed
         */
        public function parseUrlForLinks($url)
        {
            $this->initialiseCurl();

            //Get relevant links, store in db
            $html = $this->getUrlInCurl($url);

            $this->getLinks($html);

            $this->closeCurl();
        }

        /**
         * Crawl the URL for image tag
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         * @param $link The URL to be crawled
         */
        public function crawlUrlForImgTags($link)
        {
            //To find the execution time taken
            $start = microtime(true);

            $this->initialiseCurl();

            //Get relevant links, store in db
            $html = $this->getUrlInCurl($link['url']);

            $this->closeCurl();

            // Create a new DOM Document
            $dom = $this->createDomDocument($html);

            $image_count = $dom->getElementsByTagName('img')->length;

            $time_elapsed_secs = microtime(true) - $start;

            if ($image_count > 0) {
                $this->database->query("INSERT INTO images (link_id, img_count, time_taken) VALUES (:link_id, :img_count , :time_taken)");

                $this->database->bind(':link_id', $link['id']);
                $this->database->bind(':img_count', $image_count);
                $this->database->bind(':time_taken', $time_elapsed_secs);

                $this->database->execute();
            }
        }

        /**
         * Check if the link is acceptable
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         * @param $link      The link to be checked
         * @param $url_parts The URL segments returned from parse_url() function
         *
         * @return bool Returns true if link is acceptable
         */
        private function isLinkAcceptable($link, $url_parts)
        {
            if (
                empty($link) ||
                filter_var($link, FILTER_VALIDATE_URL) === FALSE ||
                (substr($link, 0, 1) === '#')
            ) {
                return false;
            }

            $currentHost = $url_parts['scheme'] . '://' . $url_parts['host'] . "/";

            //Check of link is within the given initial URL
            if ($currentHost != $this->url) {
                return false;
            }

            return true;
        }

        /**
         * Reconstruct the URL removing all query strings
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         * @param $url_parts The URL segments returned from parse_url() function
         *
         * @return string The reconstructed URL
         */
        private function reconstruct_url($url_parts)
        {
            $constructed_url = $url_parts['scheme'] . '://' . $url_parts['host'] . (isset($url_parts['path']) ? $url_parts['path'] : '');

            return $constructed_url;
        }

        /**
         * Get all the parsed links in the database
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         * @return mixed Array of all links in the database
         */
        private function getAllLinksInDatabase()
        {
            $this->database->query("SELECT id, url FROM links ORDER BY id ASC");

            $links = $this->database->getResultSet();

            return $links;
        }

        /**
         * Iterate over all links and crawl each link
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         * @param $linksList
         */
        private function iterateOverLinks($linksList)
        {
            foreach ($linksList as $k => $link) {
                if( $k > MAXURLCOUNT) {
                    return;
                }

                $this->crawlUrlForImgTags($link);
            }
        }

        /**
         * Generate the final report based on the data available
         * in the images table
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         */
        private function generateReport()
        {
            //TODO: Split very long URLs to multi-lines, so as to print result in smaller consoles
            //TODO: If possible decide column width based on available maximum console width

            $cellWidth = 0;

            $this->database->query("SELECT url, img_count, ROUND(time_taken, 2) AS time FROM `images`
                INNER JOIN `links`
                ON images.link_id = links.id
                ORDER BY url ASC");

            $resultSet = $this->database->getResultSet();

            if (count($resultSet) > 0) {
                foreach ($resultSet as $result) {
                    if (strlen($result['url']) > $cellWidth) {
                        $cellWidth = strlen($result['url']);
                    }
                }

                echo $this->getPaddedString("URL", $cellWidth) . "\t Image Count\t Time Taken \n";
                echo $this->getPaddedString("========", $cellWidth) . "\t ===========\t ========== \n";


                foreach ($resultSet as $result) {
                    echo $this->getPaddedString($result['url'], $cellWidth) . "\t "
                        . $result['img_count'] . "\t\t " . $result['time'] . " \n";
                }
            }
        }


        /**
         * Pad the string so that its uniformly displayed in the console
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         * @param $text         The string to be padded
         * @param $maxCellWidth The maximum cell width
         *
         * @return string
         */
        private function getPaddedString($text, $maxCellWidth)
        {
            $textLength = strlen($text);
            $requiredLength = max($maxCellWidth, $textLength);

            if ($requiredLength > $textLength) {
                $diff = $requiredLength - $textLength;

                $text .= str_repeat(' ', $diff);
            }

            return $text;
        }

        /**
         * Empty the database tables to handle a new execution
         *
         * @author Saji Nediyanchath<saji89@gmail.com>
         *
         */
        private function emptyDbTables()
        {
            $this->database->query("TRUNCATE links");
            $this->database->execute();

            $this->database->query("TRUNCATE images");
            $this->database->execute();
        }
    }