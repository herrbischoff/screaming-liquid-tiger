<?php
/******************************************************************************
 * Screaming Liquid Tiger
 *
 * Minimalistic podcast feed generator script for audiobooks
 *
 * @author     Marcel Bischoff
 * @copyright  2015 Marcel Bischoff
 * @license    http://opensource.org/licenses/MIT The MIT Licence
 * @version    0.1.2
 * @link       https://github.com/herrbischoff/screaming-liquid-tiger
 * @since      File available since Release 0.1.0
 *****************************************************************************/

/******************************************************************************
 * Configuration Start
 *****************************************************************************/

/**
 * Feed info
 *
 * Basic feed information, all of which are mandatory.
 * description: basically anything you want, may appear in feed reader
 * link: dummy or real URL
 * title: your feed title as it appears in the feed reader
 */
$conf = array(
    'description' => 'Personal Audiobook Feed',
    'link'        => 'http://www.example.com',
    'title'       => 'Audiobook Podcast'
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

/******************************************************************************
 * Configuration End
 *****************************************************************************/

/**
 * Output correct content type
 */
header('Content-type: application/rss+xml; charset=utf-8');

/**
 * Check PHP version
 */
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50400) :
    die('You need PHP 5.4+. You have ' . PHP_VERSION . '.');
endif;

/**
 * Format date according to RFC 822
 */
$date_fmt = 'D, d M Y H:i:s e';

/**
 * Check for HTTPS
 */
$prot = ($_SERVER['HTTPS'] != "on") ? 'http://' : 'https://';

/**
 * Determine base URL
 */
$base_url = str_replace("index.php", "", $prot . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]);

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title><?= $conf['title'] ?></title>
        <link><?= $conf['link'] ?></link>
        <description><?= $conf['description'] ?></description>
        <pubDate><?= date($date_fmt) ?></pubDate>
        <lastBuildDate><?= date($date_fmt) ?></lastBuildDate>
        <atom:link href="<?= $base_url ?>" rel="self" type="application/rss+xml" />
<?php
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
        if (array_key_exists(pathinfo($entry, PATHINFO_EXTENSION), $exts)) :
?>
        <item>
            <title><?php $p = pathinfo($entry); echo $p['filename'] ?></title>
            <guid><?= $base_url . rawurlencode($entry) ?></guid>
            <enclosure url="<?= $base_url . rawurlencode($entry) ?>" length="<?= filesize($entry) ?>" type="<?= $exts[$p['extension']] ?>"/>
            <pubDate><?= date($date_fmt, filemtime($entry)) ?></pubDate>
        </item>
<?php
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
?>
    </channel>
</rss>
