<?php
/**
 *
 * Elite Apps extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eff\elite_bundle\core;

class server_query_buffer
{
    /**
     * The original data
     *
     * @var        string
     * @access     public
     */
    private $data;

    /**
     * The original data
     *
     * @var        string
     * @access     public
     */
    private $length;


    /**
     * Position of pointer
     *
     * @var        string
     * @access     public
     */
    private $index = 0;

    private $error = '';


    /**
     * Constructor
     *
     * @param   string|array    $response   The data
     */
    public function __construct($data)
    {
        $this->data   = $data;
        $this->length = strlen($data);
    }

    /**
     * Return data currently in the buffer
     *
     * @return  string|array    The data currently in the buffer
     */
    public function getBuffer()
    {
        return substr($this->data, $this->index);
    }

    /**
     * Returns the number of bytes in the buffer
     *
     * @return  int  Length of the buffer
     */
    public function getLength()
    {
        return max($this->length - $this->index, 0);
    }

    /**
     * Read from the buffer
     *
     * @param   int             $length     Length of data to read
     * @return  string          The data read
     */
    public function read($length = 1)
    {
        if (($length + $this->index) > $this->length) {
            $this->error .=',length OOB';
        }

        $string = substr($this->data, $this->index, $length);
        $this->index += $length;

        return $string;
    }

    /**
     * Skip forward in the buffer
     *
     * @param   int             $length     Length of data to skip
     * @return  void
     */
    public function skip($length = 1)
    {
        $this->index += $length;
    }

    /**
     * Read from buffer until delimiter is reached
     *
     * If not found, return everything
     *
     * @param   string          $delim      Read until this character is reached
     * @return  string          The data read
     */
    public function readString($delim = "\x00")
    {
        // Get position of delimiter
        $len = strpos($this->data, $delim, min($this->index, $this->length));

        // If it is not found then return whole buffer
        if ($len === false) {
            return $this->read(strlen($this->data) - $this->index);
        }

        // Read the string and remove the delimiter
        $string = $this->read($len - $this->index);
        ++$this->index;

        return $string;
    }

    /**
     * Read from buffer until any of the delimiters is reached
     *
     * If not found, return everything
     *
     * @param   array           $delims      Read until these characters are reached
     * @return  string          The data read
     */
    public function readStringMulti($delims, &$delimfound = null)
    {
        // Get position of delimiters
        $pos = array();
        foreach ($delims as $delim) {
            if ($p = strpos($this->data, $delim, min($this->index, $this->length))) {
                $pos[] = $p;
            }
        }

        // If none are found then return whole buffer
        if (empty($pos)) {
            return $this->read(strlen($this->data) - $this->index);
        }

        // Read the string and remove the delimiter
        sort($pos);
        $string = $this->read($pos[0] - $this->index);
        $delimfound = $this->read();

        return $string;
    }

    public function error()
    {
        return $this->error;
    }
}
