<?php
/**
 * ZipFile class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <didier.corbiere@opale-concept.com>
 */

/**
 * ZipFile class allows to open files inside a zip file with the standard php zip functions
 */
class ZipFile
{
    private $mZip;
    private $mEntries;

    public function __construct()
    {
        $this->mZip = null;
        $this->mEntries = null;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->Close();
    }

    /**
     * Open a zip file and read it's entries
     *
     * @param string $inFileName
     * @return boolean True if zip file has been correctly opended, else false
     */
    public function Open($inFileName)
    {
        $this->Close();

        $this->mZip = new ZipArchive();
        $result = $this->mZip->open($inFileName, ZipArchive::RDONLY);
        if ($result !== true) {
            return false;
        }

        $this->mEntries = [];

        for ($i = 0; $i <  $this->mZip->numFiles; $i++) {
            //$fileName =  $this->mZip->getNameIndex($i);
            $entry =  $this->mZip->statIndex($i);
            $fileName = $entry['name'];
            $this->mEntries[$fileName] = $entry;
        }

        return true;
    }

    /**
     * Check if a file exist in the zip entries
     *
     * @param string $inFileName File to search
     *
     * @return boolean True if the file exist, else false
     */
    public function FileExists($inFileName)
    {
        if (!isset($this->mZip)) {
            return false;
        }

        if (!isset($this->mEntries[$inFileName])) {
            return false;
        }

        return true;
    }

    /**
     * Read the content of a file in the zip entries
     *
     * @param string $inFileName File to search
     *
     * @return mixed File content the file exist, else false
     */
    public function FileRead($inFileName)
    {
        if (!isset($this->mZip)) {
            return false;
        }

        if (!isset($this->mEntries[$inFileName])) {
            return false;
        }

        //$entry = $this->mEntries[$inFileName];
        $data = $this->mZip->getFromName($inFileName);

        return $data;
    }

    /**
     * Close the zip file
     *
     * @return void
     */
    public function Close()
    {
        if (!isset($this->mZip)) {
            return;
        }

        $this->mZip->close();
        $this->mZip = null;
    }
}
