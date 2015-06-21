<?php
/**
*
* Elite Apps extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace eff\elite_bundle\acp;

class elite_bundle_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		// Define acp controller
		$acp_controller = $phpbb_container->get('eff.elite_bundle.acp.controller');

		// Send url to acp controller
		$acp_controller->set_page_url($this->u_action);

		switch ($mode)
		{
            case game_logs:

				$this->tpl_name = 'game_logs';
				$this->page_title = 'Game Logs';

                $acp_controller->display_game_logs();

			break;

            case server_logins:

                $this->tpl_name = 'server_logins';
                $this->page_title = 'Server Logins';

                $acp_controller->display_server_logins();

            break;

            case player_search:

                $this->tpl_name = 'player_search';
                $this->page_title = 'Player Search';

                $acp_controller->display_player_search();
            break;

            case activity_graphs:

                $this->tpl_name = 'activity_graphs';
                $this->page_title = 'Activity Graphs';

                $acp_controller->display_activity_graphs();

            break;

            case system_messages:

                $this->tpl_name = 'system_messages';
                $this->page_title = 'System Messages';

                $acp_controller->display_system_messages();
                break;
		}
	}
}
