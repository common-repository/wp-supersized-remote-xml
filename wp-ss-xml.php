<?php
/*
Plugin Name: WP-Supersized remote XML
Plugin URI: http://www.jitsc.co.uk/
Description: This plugin exposes an API for WP-SuperSized and creates XML files for displaying galleries remotely
Version: 1.1
Author: Martin Proffitt
Author URI: http://www.jitsc.co.uk/
License: GPLv2 or later

Copyright (c) 2012 Martin Proffitt

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Class WPSupersizedXml
 *
 * This class handles creation of XML files on a remote URL for
 * publishing WP-Supersized galleries to remote sites.
 *
 * In order for this class to work, both the host and the client must
 * have this plugin installed.
 *
 * @package WPSuperSized
 * @author  Martin Proffitt <martin@jitsc.co.uk>
 * @link    http://www.jitsc.co.uk/
 */
class WPSupersizedXml
{
    const MIN_WP_SUPERSIZED = '3.1.0';

    const MIN_PHP_VERSION   = '5.3';
    /**
     * Holder for the imported XML file
     *
     * @var simplexml object
     */
    protected static $xml;

    /**
     * An array of options loaded from resource
     *
     * @var array
     */
    protected static $arrOptions;

    /**
     * Creates a new xml file
     *
     * @return void
     */
    public function __construct()
    {
        register_activation_hook(__FILE__, array(&$this, 'install'));
        add_action('plugins_loaded', array(&$this, 'initialise'));
    }

    /**
     * install the plugin
     *
     * @return void
     */
    public function install()
    {
        if (!$this->versionMatches('php')) {
            die('WPSuperizedXml needs at least PHP version ' . self::MIN_PHP_VERSION);
        }
        if (!class_exists('WPSupersized') || !$this->versionMatches('wp-supersized')) {
            die('WPSuperizedXml needs at least WP-SuperSized version ' . self::MIN_WP_SUPERSIZED);
        }
    }

    /**
     * Compares versions to ensure compatability.
     *
     * @param string $type The object to compare the version of.
     *                     Currently one of 'php', 'wp-supersized'
     *
     * @return bool
     */
    protected function versionMatches($type)
    {
        switch($type) {
            case 'wp-supersized':
                return version_compare(WPSupersized::plugin_version, self::MIN_WP_SUPERSIZED, '>=');
            case 'php':
                return version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>=');
        }
    }

    /**
     * initialises the object when triggered via wordpress plugin loader
     *
     * @return void;
     */
    public function initialise()
    {
        if (self::hasParam('wp-ss-xml')) {
            $this->write($this->getXml());
            $this->quit();
        } else {
            self::replaceSuperSizedHeader();
            self::replaceSuperSizedFooter();
        }
    }

    /**
     * Gets an XML string for output
     *
     * @return string
     */
    public function getXml()
    {
        $arrOptions = self::getOptions();

        $arrSlides = $this->getSlideList();

        $strXML = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<supersized>\n    <options>\n";
        foreach ($arrOptions as $key => $value) {
            // protect flickr, picasa and smugmug details;
            if (!is_array($value)
                && !in_array(
                    array_shift(
                        explode('_', $key)
                    ),
                    array('flickr', 'picasa', 'smugmug')
                )
            ) {
                $strXML .= "        <$key>{$value}</$key>\n";
            }
        }

        $strXML .= "    </options>\n";
        if (!empty($arrSlides)) {
            $strXML .= "    <slides>\n";
            foreach ($arrSlides as $slide) {
                $strXML .= "        <slide>\n";
                $strXML .= "            <slide_link>{$slide['slide_link']}</slide_link>\n";
                $strXML .= "            <thumb>{$slide['thumb']}</thumb>\n";
                $strXML .= "            <title>{$slide['title']}</title>\n";
                $strXML .= "        </slide>\n";
            }
            $strXML .= "    </slides>\n";
        }

        return trim($strXML . "</supersized>");
    }

    /**
     * Creates an image list from a nextgen gallery
     *
     * @return array
     */
    protected function getNextGenGallery()
    {
        $arrSlides = array();
        if (class_exists('nggdb')) {
            $arrImages = nggdb::get_gallery(self::getParam('nggallery'), 'sortorder', 'ASC');
            if ($arrImages) {
                foreach ($arrImages as $objImage) {
                    $arrSlides[] = array(
                        'slide_link' => $objImage->imageURL,
                        'thumb'      => $objImage->thumbURL,
                        'title'      => (self::$arrOptions['slide_captions'] ? $objImage->alttext : '')
                    );
                }
            }
        }

        if (empty($arrSlides)) {
            $arrSlides[] = $this->getErrorSlide('nextgen');
        }

        return $arrSlides;
    }

    /**
     * Creates an image list from a custom gallery
     *
     * @return array
     */
    protected function getCustomGallery()
    {
        $arrSlides = array();

        $GLOBALS['post'] = get_post(self::getParam('customgallery'));
        if (substr(self::$arrOptions['default_dir'], 0, 11) != 'ngg-gallery_') {
            $strDir = WPSupersized::build_wpcontent_dir()
                . "/"
                . WPSupersized::get_custom_dir(self::$arrOptions['default_dir'])
                . "/" ;
        } else {
            $strDir = WPSupersized::build_wpcontent_dir()
                . "/"
                . self::$arrOptions['default_dir']
                . "/";
        }

        $strThumbDir = $strDir."thumbs/";
        $arrFiles = glob($strDir . "*.{jpg,JPG,png,PNG,gif,GIF,jpeg,JPEG}", GLOB_BRACE);

        if (empty($arrFiles)) {
            $arrFiles = array_merge(
                (array)glob($strDir."*.jpg"),
                (array)glob($strDir."*.JPG"),
                (array)glob($strDir."*.png"),
                (array)glob($strDir."*.PNG"),
                (array)glob($strDir."*.gif"),
                (array)glob($strDir."*.GIF"),
                (array)glob($strDir."*.jpeg"),
                (array)glob($strDir."*.JPEG")
            );
        }
        asort($arrFiles);

        if (file_exists($strThumbDir) && !WPSupersized::is_empty_dir($strThumbDir)) {
            $arrThumbs = array_merge(
                (array)glob($strThumbDir."*.jpg"),
                (array)glob($strThumbDir."*.JPG"),
                (array)glob($strThumbDir."*.png"),
                (array)glob($strThumbDir."*.PNG"),
                (array)glob($strThumbDir."*.gif"),
                (array)glob($strThumbDir."*.GIF"),
                (array)glob($strThumbDir."*.jpeg"),
                (array)glob($strThumbDir."*.JPEG")
            );
            if (empty($arrThumbs)) {
                $arrThumbs = glob($strThumbDir . "*.{jpg,JPG,png,PNG,gif,GIF,jpeg,JPEG}", GLOB_BRACE);
            }

            asort($arrThumbs);
            foreach ($arrThumbs as $key => $strPath) {
                $arrThumbsDir[$key] = str_ireplace(WPSupersized::build_wpcontent_dir(), '', $strPath);
            }
        } else {
            $arrThumbsDir = $thumbsdirArray = $strThumbDir = '';
        }

        foreach ($arrFiles as $key => $strPath) {
            $short_dirArray[$key] = str_ireplace(WPSupersized::build_wpcontent_dir(), '', $strPath);
        }

        $indexCount = count($arrFiles);

        if ($indexCount >= 1 && $strDir) {

            for ($index=0; $index < $indexCount-1; $index++) {
                $strCaption
                    = WPSupersized::asciitothmlcode(
                        WPSupersized::_show_iptc_caption(
                            $arrFiles[$index]
                        )
                    );

                if ($strThumbDir && file_exists($strThumbDir)) {
                    $strThumbnailLink = content_url().$arrThumbsDir[$index];
                } else {
                    $strThumbnailLink = '';
                }

                $arrSlides[] = array(
                    'slide_link' => content_url().$short_dirArray[$index],
                    'thumb'      => $strThumbnailLink,
                    'title'      => (self::$arrOptions['slide_captions'] ? $strCaption : '')
                );
            }
        }

        if (empty($arrSlides)) {
            $arrSlides[] = $this->getErrorSlide('custom');
        }

        return $arrSlides;
    }

    /**
     * Creates an image list from a wordpress gallery
     *
     * @return array
     */
    protected function getWordpressGallery()
    {
        $arrSlides = array();
        $images = get_children(
            array(
                'post_parent'    => self::getParam('wpgallery'),
                'post_type'      => 'attachment',
                'numberposts'    => -1,
                'post_status'    => null,
                'post_mime_type' => 'image',
            )
        );

        if ($images) {
            $images = WPSupersized::sort_wpgallery_array($images);

            foreach ($images as $image) {
                $strCaption  = apply_filters('post_excerpt', $image->post_excerpt);
                $strTitle    = !empty($strCaption)
                    ? $strCaption : apply_filters('the_title', $image->post_title);

                $arrSlides[] = array(
                    'slide_link' => wp_get_attachment_url($image->ID),
                    'thumb'      => wp_get_attachment_thumb_url($image->ID),
                    'title'      => (self::$arrOptions['slide_captions'] ? $strTitle : '')
                );
            }
        }

        if (empty($arrSlides)) {
            $arrSlides[] = $this->getErrorSlide('wordpress');
        }

        return $arrSlides;
    }

    /**
     * Gets a list of slides for the current active gallery type
     *
     * @return array
     */
    public function getSlideList()
    {

        if (self::hasParam('nggallery')) {
            return $this->getNextGenGallery();
        } elseif (self::hasParam('wpgallery')) {
            return $this->getWordpressGallery();
        } elseif (self::hasParam('customgallery')) {
            return $this->getCustomGallery();
        }
        return array();
    }

    /**
     * echos an xml string
     *
     * @param string $strXml The XML string to print
     *
     * @return void
     */
    public function write($strXml)
    {
        header('Content-Type: text/xml');
        echo $strXml;
    }

    /**
     * Terminates the output
     *
     * @return void
     */
    protected function quit()
    {
        exit(0);
    }

    /**
     * Returns an error image for when a gallery / plugin cannot be found.
     *
     * @param string $type The type of gallery to return an error for
     *
     * @return array
     */
    protected function getErrorSlide($type)
    {
        switch($type) {
            case 'nextgen':
                return array(
                    'slide_link'
                        => plugins_url()
                        . '/wp-supersized/img/error_img/cannot_find_nextgen_gallery_images_requested.jpg',
                    'title'      => 'Cannot find NextGen Gallery images. Is NextGen installed?'
                );
            case 'wordpress':
                return array(
                    'slide_link'
                        => plugins_url()
                        . '/wp-supersized/img/error_img/cannot_find_wp_media_gallery_images.jpg',
                    'title'      => 'Cannot find WP Gallery images attached to this post/page'
                );
            case 'custom':
                return array(
                    'slide_link'
                        => plugins_url() .
                        '/wp-supersized/img/error_img/cannot_find_your_dir_or_file_please_check_that_it_exists.jpg',
                    'title'      => 'Cannot find any images attached to this post/page'
                );
        }
    }

    /**
     * Checks to see if a given parameter exists in GET or POST
     *
     * @param string $strName The name of the variable to look for
     *
     * @return bool
     */
    public static function hasParam($strName)
    {
        return isset($_GET[$strName]) || isset($_POST[$strName]);
    }

    /**
     * Gets the value of a given parameter
     *
     * @param string $strName The name of the parameter to return
     *
     * @return mixed The parameter value or null if the parameter doesn't exist.
     *
     * The script will look for _POST paramaters first and then _GET.
     */
    public static function getParam($strName)
    {
        return isset($_POST[$strName])
            ? $_POST[$strName]
            : (isset($_GET[$strName])
                ? $_GET[$strName]
                : null
            );
    }

    /**
     * Precents the default wp-supersized header from loading
     *
     * @return void
     */
    public static function replaceSuperSizedHeader()
    {
        foreach (array_keys($GLOBALS['wp_filter']['wp_head']) as $key) {
            if (isset($GLOBALS['wp_filter']['wp_head'][$key]['WPSupersizedaddHeaderCode'])) {
                $GLOBALS['wp_filter']['wp_head'][$key]['WPSupersizedaddHeaderCode']
                    = array(
                        'function' => array ( 0 => 'WPSupersizedXml', 1 => 'addHeaderCode'),
                        'accepted_args' => 1
                    );
            }
        }
    }

    /**
     * Precents the default wp-supersized footer from loading
     *
     * @return void
     */
    public static function replaceSuperSizedFooter()
    {
        foreach (array_keys($GLOBALS['wp_filter']['wp_footer']) as $key) {
            if (isset($GLOBALS['wp_filter']['wp_footer'][$key]['WPSupersizedaddFooterCode'])) {
                $GLOBALS['wp_filter']['wp_footer'][$key]['WPSupersizedaddHeaderCode']
                    = array(
                        'function' => array ('WPSupersizedXml', 'addFooterCode'),
                        'accepted_args' => 1
                    );
            }
        }
    }

    /**
     * Adds the wp-supersized header code to the current page
     *
     * @return void
     */
    public static function addHeaderCode()
    {
        if (!self::isRemoteXmlFile()) {
            @ob_start();
            WPSupersized::addHeaderCode();
            echo @ob_get_clean();
            return;
        }

        $arrOptions = self::getOptions();
        include_once dirname(realpath(__FILE__)) . '/templates/ss-options.php';
    }

    /**
     * Adds the wp-supersized footer code to the current page
     *
     * @return void
     */
    public static function addFooterCode()
    {
        if (!self::isRemoteXmlFile()) {
            @ob_start();
            WPSupersized::addFooterCode();
            echo @ob_get_clean();
            return;
        }

        $arrOptions = self::getOptions();
        include_once dirname(realpath(__FILE__)) . '/templates/ss-footer.php';
    }

    /**
     * Gets a list of slides as a JSON string
     *
     * @global array $xmlSlidesArray An array of slides from wp-supersized
     *
     * @return string
     */
    protected static function getSlides()
    {
        global $xmlSlidesArray;
        $xmlSlidesArray = self::$xml->xpath('//slide');
        if (is_array($xmlSlidesArray) && count($xmlSlidesArray) > 0) {
            @ob_start();
            WPSupersized::slides_list_from_xml();
            return @ob_get_clean();
        }
    }

    /**
     * Checks to see if a given path is an XML file, if so loads it into memory
     *
     * @global type $post The current post
     *
     * @return bool
     */
    protected static function isRemoteXmlFile()
    {
        if (self::$xml) {
            return true;
        } else {
            global $post;
            $strUrl = get_post_meta($post->ID, 'SupersizedDir', true);
            $bolIsUrl = preg_match('#^http[s]*:\/\/#', $strUrl);

            if (!$bolIsUrl
                && in_array($strUrl, array('none', 'wp-gallery'))
                && get_query_var('gallery')
            ) {
                $strUrl
                    = ($_SERVER['SERVER_PORT'] == 80
                        ? 'http://'
                        : 'https://'
                    )
                    . $_SERVER['SERVER_NAME'] . '/?wp-ss-xml';
                $bolIsUrl = true;
            }

            if ($bolIsUrl) {

                if (get_query_var('gallery') && !strstr($strUrl, 'nggallery')) {
                    $strUrl .= '&nggallery=' . get_query_var('gallery');
                }

                $strData = self::getXmlContents($strUrl);
                if (!empty($strData)
                    && false !== (self::$xml = simplexml_load_string($strData))
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Loads XML from a given resource
     *
     * @param type $strUrl The URL from which to load XML
     *
     * @return string
     *
     * This method retrieves XML from a given URL by first trying to
     * use fopen and then falling back to fsocketopen.
     * This has caused a few issues as the results come back raw, hence
     * the convoluted cleanup at the end of this method.
     *
     * The reason for using fsocketopen and not cURL or fopen (as was
     * implemented previously) is that my host doesn't have routings
     * available for a server to connect to itself easily.
     */
    protected static function getXmlContents($strUrl)
    {
        $strData = '';
        $bolUseFsocket = false;
        if (ini_get('allow_url_fopen') == '1') {
            try {
                $strData = self::getContentFromFopen($strUrl);
            } catch (ErrorException $e) {
                $bolUseFsocket = true;
            }
        }

        if ($bolUseFsocket) {
            try {
                $strData = self::getContentFromFsocket($strUrl);
            } catch (ErrorException $e) {
                $strData = '';
            }
        }
        return $strData;
    }

    /**
     * Gets content from a resource using fopen
     *
     * @param string $strUrl The URL of the resource to retrieve
     *
     * @return string
     */
    protected static function getContentFromFopen($strUrl)
    {
        $strData        = '';
        $objFilePointer = @fopen($strUrl, 'r');
        if (!$objFilePointer) {
            throw new ErrorException('Failed to open resource ' . $strUrl);
        }
        while (!@feof($objFilePointer)) {
            $strData .= @fread($objFilePointer, 1024);
        }
        @fclose($objFilePointer);
        return trim($strData);
    }

    /**
     * Gets content from a socket using fsocketopen
     *
     * @param string $strUrl The url to open
     *
     * @return string
     */
    protected static function getContentFromFsocket($strUrl)
    {
        $strUrl = preg_replace('#^http[s]*:\/\/#', '', $strUrl);
        list($strHost, $strOptions) = explode('?', str_replace('/', '', $strUrl));

        $strData    = $strError = '';
        $strIp      = '127.0.0.1';
        $intError   = 0;
        $strError   = '';
        $strHeader  = "GET /?{$strOptions} HTTP/1.1\r\n";
        $strHeader .= "Host: {$strHost}\r\n";
        $strHeader .= "Connection: Close\r\n\r\n";

        $objFilePointer = fsockopen($strIp, 80, $intError, $strError, 5);
        if (!$objFilePointer) {
            throw new ErrorException($strError, $intError);
        }

        fwrite($objFilePointer, $strHeader);
        while (!feof($objFilePointer)) {
            $strData .= fgets($objFilePointer, 2048);
        }
        fclose($objFilePointer);

        $strContents = array_pop(preg_split('/([\r\n][\r\n])\\1/', $strData, 2));
        return trim(preg_replace('/(^[a-z0-9]*\r\n)|(\r\n[a-z0-9]+\r\n)/i', '', $strContents));
    }

    /**
     * Loads options from different sources.
     *
     * @return array
     *
     * This method loads the global wp-supersized options then merges
     * in options from a secondary XML file and finally from the
     * request (_GET, _POST).
     *
     * With the latter, the order for loading is _POST -> _GET
     */
    protected static function getOptions()
    {
        if (!self::$arrOptions) {
            // get global supersize options
            $arrOptions = get_option('wp-supersized_options');
            $xmlFile = get_template_directory() . '/wp-ss-gallery.xml';

            if (!file_exists($xmlFile)) {
                $xmlFile = dirname(realpath(__FILE__)) . '/wp-ss-gallery.xml';
                if (!file_exists($xmlFile)) {
                    $xmlFile = false;
                }
            }

            if ($xmlFile) {
                // override with custom
                $xml = simplexml_load_file($xmlFile);
                $arrOptionsXml = $xml->xpath('//options');
                foreach (array_keys($arrOptions) as $key) {
                    if (isset($arrOptionsXml[0]->$key)) {
                        $arrOptions[$key] = (int)$arrOptionsXml[0]->$key;
                    }
                    if (self::hasParam($key)) {
                        $arrOptions[$key] = self::getParam($key);
                    }
                }

            }
            self::$arrOptions = $arrOptions;
        }
        return self::$arrOptions;
    }
}
new WPSupersizedXml();
