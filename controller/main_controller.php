<?php
/**
*
* @package EFF Wiki Extension
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace eff\elite_bundle\controller;

class main_controller implements main_interface
{
    protected $log;
    protected $user;

	public function __construct(\phpbb\log\log_interface $log, \phpbb\user $user)
    {
        $this->log = $log;
        $this->user = $user;
	}

	public function display()
	{

	}
}
