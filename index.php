<?php
/******************************************************************************
 * Screaming Liquid Tiger
 *
 * Minimalistic podcast feed generator script for audiobooks
 *
 * @author     Marcel Bischoff
 * @copyright  2015 Marcel Bischoff
 * @license    http://opensource.org/licenses/MIT The MIT Licence
 * @version    0.2.2
 * @link       https://github.com/herrbischoff/screaming-liquid-tiger
 * @since      File available since Release 0.1.0
 *****************************************************************************/

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
 * Check PHP version
 */
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50400) :
    die('You need PHP 5.4+. You have ' . PHP_VERSION . '.');
endif;

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
$prot = ($_SERVER['HTTPS'] != "on") ? 'http://' : 'https://';

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

printf("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
printf("<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:itunes=\"http://www.itunes.com/dtds/podcast-1.0.dtd\">\n");
printf("\t<channel>\n");
printf("\t\t<title>%s</title>\n", $conf['title']);
printf("\t\t<link>%s</link>\n", $conf['link']);
printf("\t\t<description>%s</description>\n", $conf['description']);
printf("\t\t<pubDate>%s</pubDate>\n", date($date_fmt));
printf("\t\t<lastBuildDate>%s</lastBuildDate>\n", date($date_fmt));
printf("\t\t<atom:link href=\"%s\" rel=\"self\" type=\"application/rss+xml\"/>\n", $base_url);
if ($castimg_url) :
    printf("\t\t<itunes:image href=\"%s\"/>\n", $castimg_url);
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
        if (array_key_exists(pathinfo($entry, PATHINFO_EXTENSION), $exts)) :
            $p = pathinfo($entry);
            $filename = $p['filename'];
            $fileimg_path = escapeshellarg('./tmp/' . $filename . '.jpg');
            $fileimg_url = $base_url . 'tmp/' . rawurlencode($filename . '.jpg');

            ob_start();
            $cmd = $mediainfo . ' --Inform=\'General;%Duration/String3%#####%Performer% — %Album%\' ' . escapeshellarg($entry) . ' 2>&1';
            passthru($cmd);
            $mediainfo_out = ob_get_contents();
            ob_end_clean();

            if ($mediainfo) :
                preg_match('/(\d{2}:\d{2}:\d{2}.\d+)#####(.+)/', $mediainfo_out, $matches);
                $duration = $matches[1];
                $title = $matches[2];
            else :
                $title = $p['filename'];
            endif;
            printf("\t\t<item>\n");
            printf("\t\t\t<title>%s</title>\n", $title);
            printf("\t\t\t<guid isPermalink=\"false\">%s</guid>\n", $base_url . rawurlencode($entry));
            printf("\t\t\t<enclosure url=\"%s\" length=\"%s\" type=\"%s\"/>\n",
                $base_url . rawurlencode($entry),
                filesize($entry),
                $exts[$p['extension']]
            );
            printf("\t\t\t<pubDate>%s</pubDate>\n", date($date_fmt, filemtime($entry)));
            if ($mediainfo) :
                printf("\t\t\t<itunes:duration>%s</itunes:duration>\n", $duration);
            endif;
            printf("\t\t</item>\n");
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

printf("\t</channel>\n");
printf("</rss>");
