<?php
/**
 *
 * Elite Bundle extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eff\elite_bundle\core;


use phpbb\db\driver\driver_interface;

class member
{
    private $wiki_db;
    private $db;

    public $info;

    public function  __construct(driver_interface $wiki_db, driver_interface $db)
    {
        $this->wiki_db = $wiki_db;
        $this->db = $db;
        $this->info = new info();
    }


    public function load($id)
    {
        $sql = "SELECT name, rank, user_id
				FROM " . "wiki_members"."
				WHERE user_id=".$id;

        $result=$this->wiki_db->sql_query($sql);

        while($row = $this->wiki_db->sql_fetchrow($result))
        {
            $this->info->name = $row['name'];
            $this->info->user_id = $row['user_id'];
        }
    }
}

class info
{
    public $name;
    public $user_id;
}