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
        $sql = "SELECT user_id, name, rank, rank_2, side, status, promo_date, promo_rank, mastery_1, mastery_2, mastery_3, name_real, gender, skin_1, skin_2, g_chat, msn, skype, steam
				FROM " . "wiki_members"."
				WHERE user_id=".$id;

        $result=$this->wiki_db->sql_query($sql);

        while($row = $this->wiki_db->sql_fetchrow($result))
        {
            foreach ($this->info as $key => $value) {
                if (empty($value)) {
                    $value = $row[$key];
                    $this->info->$key = $value;
                }
            }
        }
    }

    public function promoted($user_id,$array_insert,$array_update)
    {
        $sql_insert = 'INSERT INTO ' . 'wiki_promotions' . ' ' .$this->wiki_db->sql_build_array('INSERT',$array_insert);

        if(sizeof($array_update) != 0)
        {
            $sql_update = 'UPDATE ' . 'wiki_members' . ' SET ' . $this->wiki_db->sql_build_array('UPDATE', $array_update) . "
					WHERE user_id=".$user_id;
        }
    }

    public function step_down_from_rank($user_id,$array_insert,$array_update)
    {

    }
}

class info
{
    public $user_id;
    public $name;
    public $rank;
    public $rank_2;
    public $side;
    public $status;
    public $promo_date;
    public $promo_rank;
    public $mastery_1;
    public $mastery_2;
    public $mastery_3;
    public $name_real;
    public $gender;
    public $skin_1;
    public $skin_2;
    public $gchat;
    public $skype;
    public $msn;
    public $steam;
}