<?php
/**
*
* @package EFF Wiki Extension
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
**
*/

namespace eff\elite_bundle\controller;

use eff\elite_bundle\core\member;
use phpbb\db\driver\driver_interface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class main_controller implements main_interface
{
    protected $log;
    protected $user;
    protected $container;
    protected $template;
    protected $helper;
    protected $wiki_db;
    protected $db;

	public function __construct(\phpbb\log\log_interface $log, \phpbb\user $user, ContainerInterface $container, \phpbb\template\template $template, \phpbb\controller\helper $helper, driver_interface $wiki_db, driver_interface $db)
    {
        $this->log = $log;
        $this->user = $user;
        $this->container = $container;
        $this->template = $template;
        $this->helper = $helper;
        $this->wiki_db = $wiki_db;
        $this->db = $db;
	}

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function display()
	{
        $member = new member($this->wiki_db,$this->db);
        $member->load(242);
        $this->log->add('admin',$member->info->user_id,$this->user->ip,'Executed test task');
        return $this->helper->message("Member: ".$member->info->name);
	}

    public function portal()
    {
        return $this->helper->render('profile.html','Portal');
    }

    public function member($id)
    {
        if($id > 0)
        {
            $member = new member($this->wiki_db,$this->db);
            $member->load($id);
            return $this->helper->message("Member name: ".$member->info->name.", member id: ".$member->info->user_id);
        }
        else
        {
            return $this->helper->message('No route found for "GET /member/'.$id.'"',array(),"Information",404);
        }
    }
}
