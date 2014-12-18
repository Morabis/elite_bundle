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

class server_query_result
{
    /**
     * Formatted server response
     *
     * @var        array
     */
    protected $result = array();

    /**
     * Adds variable to results
     *
     * @param      string    $name      Variable name
     * @param      string    $value     Variable value
     */
    public function add($name, $value)
    {
        $this->result[$name] = $value;
    }

    /**
     * Adds player variable to output
     *
     * @param       string   $name      Variable name
     * @param       string   $value     Variable value
     */
    public function addPlayer($name, $value)
    {
        $this->addSub('players', $name, $value);
    }

    /**
     * Adds player variable to output
     *
     * @param       string   $name      Variable name
     * @param       string   $value     Variable value
     */
    public function addTeam($name, $value)
    {
        $this->addSub('teams', $name, $value);
    }

    /**
     * Add a variable to a category
     *
     * @param  $sub    string  The category
     * @param  $key    string  The variable name
     * @param  $value  string  The variable value
     */
    public function addSub($sub, $key, $value)
    {
        // Nothing of this type yet, set an empty array
        if (!isset($this->result[$sub]) or !is_array($this->result[$sub])) {
            $this->result[$sub] = array();
        }

        // Find the first entry that doesn't have this variable
        $found = false;
        for ($i = 0; $i != count($this->result[$sub]); $i++) {
            if (!isset($this->result[$sub][$i][$key])) {
                $this->result[$sub][$i][$key] = $value;
                $found = true;
                break;
            }
        }

        // Not found, create a new entry
        if (!$found) {
            $this->result[$sub][][$key] = $value;
        }
    }

    /**
     * Return all stored results
     *
     * @return  mixed  All results
     */
    public function fetch()
    {
        return $this->result;
    }
}