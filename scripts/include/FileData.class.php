<?php

/**
 * Fetch meta and translation data of a given file
 *
 * If you did not receive a copy of this file's license and are unable
 * to obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @author    Carola Sammy Kummert <sammywg@php.net>
 * @license   http://www.php.net/license/3_0.txt PHP License 3.0
 * @copyright (c) 1997-2011 The PHP Group
 *
 * @package   TranTools
 *
 * @version   $Id$
 * @filesource
 *
 *
 * @property-read int    $size         Size of the file in kB (rounded)
 * @property-read int    $mtime        Timestamp of last modify
 * @property-read string $revision     EN-Revision number
 * @property-read string $maintainer   Translation maintainer
 * @property-read string $status       Status of translation file
 * @property-read string $credits      Credits to other file contributors
 * @property-read string $rev-revision Review revision number
 * @property-read string $reviewer     Translation reviewer
 *
 */
class FileData {

    /**
     * @var bool
     * @internal defines if object contains en or translation file data
     */
    protected $isTranslation;

    /**
     * @var array
     * @internal stores file meta and translation data
     *  . size
     *  . mtime
     *  . revision
     *  . en-revision
     *  . maintainer
     *  . status
     *  . credits
     *  . rev-revision
     *  . reviewer
     */
    protected $data;

    /**
     * @var string
     * @internal includes concatenated search tags
     */
    protected static $tags;



    /**
     * Set base meta data and read the comment lines from the file head
     *
     * Usage example:
     * <code>
     * <?php
     * $file = '/phpdoc/en/reference/array/functions/next.xml';
     * $FDEn = new FileData($file, false);
     * $file = '/phpdoc/de/reference/array/functions/next.xml';
     * $FDTrans = new FileData($file, true);
     * ?>
     * </code>
     *
     * @param  string $file     File name to inspect
     * @param  bool   $isTrans  Define if the specified file is a translation
     * @return void
     */
    public function __construct($file, $isTrans) {
        self::$tags = implode('|',
            array(
                'EN-Revision',
                'Revision',
                'Maintainer',
                'Status',
                'Credits',
                'Rev-Revision',
                'Reviewer',
            )
        );
        $this->isTranslation = (bool) $isTrans;
        $this->data = array();

        $handle = fopen($file, 'r');
        for ($i=0; $i<=9 || feof($handle); ++$i) {
            $rawFile = trim(fgets($handle));
            switch (substr($rawFile, 0, 4)) {
                case '<!--':
                    $this->setMetaData($rawFile);
                    break;
                case '<?xm':
                    break;
                default:
                    $i = 9;
                    break;
            }
        }
        fclose($handle);

        $this->data['size'] = round(filesize($file)/1024, 0);
        $this->data['mtime'] = filemtime($file);
        clearstatcache();

        return;
    } // end method __construct



    /**
     * Return file's meta and translation data
     *
     * Possible data key specifiers are described in the class header
     *
     * Usage example:
     * <code>
     * <?php
     * $fileData = new FileData($filename, true);
     * print $fileData->mtime;
     * ?>
     * </code>
     *
     * @param  string  $name    Data key specifier
     * @return int|string|false Data value or on error false
     */
    public function __get($name) {
        if ($name == 'revision' && $this->isTranslation) {
            $name = 'en-revision';
        }
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return false;
    } // end method __get



    /**
     * Fetch  translation notices from file's comment header
     *
     * @param  string  $rawData  Raw data as read from file
     * @return void
     */
    protected function setMetaData($rawData) {
        $matches = null;
        if (0 < preg_match_all(
                '/('.self::$tags.'): (.*)\s+/iU',
                $rawData, $matches, PREG_PATTERN_ORDER)) {
            for($i=0, $j=count($matches[0]); $i < $j; ++$i) {
                $key = strtolower($matches[1][$i]);
                $this->data[$key] = $matches[2][$i];
            }
        }
        return;
    } // end method setMetaData

} // end class FileData
