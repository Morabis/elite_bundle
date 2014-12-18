<?php
/**
*
* @package EFF Wiki Extension
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace eff\elite_bundle\controller;

class cron_controller implements cron_interface
{
    protected $log;
    protected $user;
    protected $bot_id;
    protected $bot_ip;

	public function __construct(\phpbb\log\log_interface $log, \phpbb\user $user, $bot_id, $bot_ip)
    {
        $this->log = $log;
        $this->user = $user;
        $this->bot_id = $bot_id;
        $this->bot_ip = $bot_ip;
	}

	public function task()
	{
        if($this->user->data['user_id']==$this->bot_id && $this->user->ip==$this->bot_ip)
        {
            $this->log->add('admin',$this->bot_id,$this->bot_ip,'TASK_RUN');
        }
	}
}
