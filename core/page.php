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

class page
{
    private $id;
    private  $title;
    private $content;
    private $type;
    protected $wiki_db;

    public function __construct(\phpbb\db\driver\driver_interface $wiki_db)
    {
        $this->wiki_db = $wiki_db;

    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        if(is_numeric($type))
            $this->type = $type;
    }

    public function load($id = 0, $route = '')
    {
        if($id != 0)
            $where = "WHERE ID=$id";
        else
            $where = "WHERE title LIKE '$route''";

        $sql = 'SELECT ID, text, title, type, file, bbcode_uid, bbcode_bitfield, bbcode_options
				FROM ' . 'wiki_pages'.$where;

        $result = $this->wiki_db->sql_query($sql);
        $data = $this->wiki_db->sql_fetchrow($result);
        $this->wiki_db->sql_freeresult($result);

        if($data != false)
        {
            
        }
    }
}