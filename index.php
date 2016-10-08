<?php
/******************************************************************************
 * Screaming Liquid Tiger
 *
 * Minimalistic podcast feed generator script for audiobooks
 *
 * @author     Marcel Bischoff
 * @copyright  2015-2016 Marcel Bischoff
 * @license    http://opensource.org/licenses/MIT The MIT Licence
 * @version    0.3.0
 * @link       https://github.com/herrbischoff/screaming-liquid-tiger
 * @since      File available since Release 0.1.0
 *****************************************************************************/

/**
 * Check PHP version
 */
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50400) :
    die('You need PHP 5.4+. You have ' . PHP_VERSION . '.');
endif;

/**
 * Make sure that unicode characters in file names are not dropped.
 */
setlocale(LC_CTYPE, "C.UTF-8");

/******************************************************************************
 * Configuration Start
 *****************************************************************************/

if (!file_exists('./config.php')) :

    /**
     * Whether to check for mediainfo
     */
    $mediainfo_check = false;

    /**
     * Feed info
     *
     * Basic feed information.
     *
     * description: basically anything you want, may appear in feed reader
     * link: dummy or real URL
     * title: your feed title as it appears in the feed reader
     * image: main image for feed (optional)
     */
    $conf = array(
        'description' => 'Personal Audiobook Feed',
        'link'        => 'http://www.example.com',
        'title'       => 'Audiobook Podcast',
        'image'       => 'cast.jpg'
    );

    /**
     * File extensions
     *
     * Extensions to use for feed item creation. Add your own extensions to be
     * included, the corresponding MIME types are generated automatically.
     */
    $exts = array(
        'flac' => 'audio/flac',
        'm4a'  => 'audio/mp4',
        'm4b'  => 'audio/mp4',
        'mp3'  => 'audio/mp3',
        'mp4'  => 'audio/mp4',
        'oga'  => 'audio/ogg',
        'ogg'  => 'audio/ogg'
    );

else :

    require_once('./config.php');

endif;

/******************************************************************************
 * Configuration End
 *****************************************************************************/

/**
 * Output correct content type
 */
header('Content-type: application/rss+xml; charset=utf-8');

/**
 * Get mediainfo path
 */
$mediainfo = '';
if ($mediainfo_check) :
    $mediainfo_global = trim(shell_exec('which mediainfo'));
    $mediainfo_static = file_exists('./mediainfo');
    if ($mediainfo_global) $mediainfo = $mediainfo_global;
    if ($mediainfo_static) $mediainfo = './mediainfo';
    if (!$mediainfo) :
        die("For automatic tag reading functionality, you need mediainfo. Either install it globally on your server or download the correct static binary for your system here:\n\nhttps://mediaarea.net/en/MediaInfo/Download\n\nPut it in the same folder as this script. If you download a static build, make sure to configure your web server to block access to the binary for visitors.\n\nIn case you don't want this functionality at all, set the \$mediainfo_check variable back to 'false'.");
    endif;
endif;

/**
 * Format date according to RFC 822
 */
$date_fmt = 'D, d M Y H:i:s e';

/**
 * Check for HTTPS
 */
$prot = (isset($_SERVER['HTTPS']) != "on") ? 'http://' : 'https://';

/**
 * Determine base URL
 */
$base_url = str_replace("index.php", "", $prot . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]);

/**
 * Set feed image if present
 * */
if ($conf['image'] && file_exists($conf['image'])) :
    $castimg_url = $base_url . rawurlencode($conf['image']);
endif;

/**
 * Construct feed
 */

$xmlstr = '<?xml version="1.0" encoding="UTF-8"?><rss/>';
$rss = new SimpleXMLElement($xmlstr);
$rss->addAttribute('version', '2.0');
$rss->addAttribute('xmlns:xmlns:atom', 'http://www.w3.org/2005/Atom');
$rss->addAttribute('xmlns:xmlns:itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
$channel = $rss->addChild('channel');
$channel->addChild('title', $conf['title']);
$channel->addChild('link', $conf['link']);
$channel->addChild('description', $conf['description']);
$channel->addChild('pubDate', date($date_fmt));
$channel->addChild('lastBuildDate', date($date_fmt));
$atomlink = $channel->addChild('atom:link');
$atomlink->addAttribute('rel', 'self');
$atomlink->addAttribute('type', 'application/rss+xml');
if (isset($castimg_url)) :
    $itunes_image = $channel->addChild('xmlns:itunes:image');
    $itunes_image->addAttribute('href', $castimg_url);
endif;

/**
 * Open file handler for current directory
 */
if ($handle = opendir('.')) :

    /**
     * Start item generation loop
     */
    while (false !== ($entry = readdir($handle))) :

        /**
         * Make sure file matches extensions from array
         */
        if (array_key_exists(pathinfo($entry, PATHINFO_EXTENSION), $exts) && !preg_match('/^\./', $entry)) :
            $p = pathinfo($entry);
            $filename = $p['filename'];
            $fileimg_path = escapeshellarg('./tmp/' . $filename . '.jpg');
            $fileimg_url = $base_url . 'tmp/' . rawurlencode($filename . '.jpg');

            /**
             * Get mediainfo output for current file
             */
            ob_start();
            $opt = ' --Inform=\'General;%Duration/String3%#####%Performer% — %Album%\' ';
            $cmd = $mediainfo . $opt  . escapeshellarg($entry) . ' 2>&1';
            passthru($cmd);
            $mediainfo_out = ob_get_contents();
            ob_end_clean();

            /**
             * Parse mediainfo output
             */
            if ($mediainfo) :
                preg_match('/(\d{2}:\d{2}:\d{2}.\d+)#####(.+)/', $mediainfo_out, $matches);
                $duration = $matches[1];
                $title = $matches[2];
            else :
                $title = $p['filename'];
            endif;

            /**
             * Contruct feed item
             */
            $item = $channel->addChild('item');
            $item->addChild('title', $title);
            $guid = $item->addChild('guid', $base_url . rawurlencode($entry));
            $guid->addAttribute('isPermalink', 'false');
            $enclosure = $item->addChild('enclosure');
            $enclosure->addAttribute('url', $base_url . rawurlencode($entry));
            $enclosure->addAttribute('length', filesize($entry));
            $enclosure->addAttribute('type', $exts[$p['extension']]);
            $item->addChild('pubDate', date($date_fmt, filemtime($entry)));
            if ($mediainfo) :
                $item->addChild('xmlns:itunes:duration', $duration);
            endif;

        endif;

    /**
     * End item generation loop
     */
    endwhile;

endif;

/**
 * Close file handler
 */
closedir($handle);

/**
 * Output feed
 */
echo $rss->asXML();
