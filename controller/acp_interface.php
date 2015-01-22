<?php
/**
*
* Elite Apps extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace eff\elite_bundle\controller;

interface acp_interface
{
	public function set_page_url($u_action);

	public function display_game_logs();

    public function display_server_logins();

    public function display_player_search();

    public function display_activity_graphs();

}
