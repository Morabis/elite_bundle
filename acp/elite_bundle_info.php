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

class elite_bundle_info
{
	function module()
	{
		return array(
			'filename'	=> '\eff\elite_bundle\acp\elite_bundle_module',
			'title'		=> 'ACP_elite_bundle',
			'modes'		=> array(
				'game_logs'	=> array('title' => 'ACP_GAME_LOGS', 'auth' => 'ext_eff/elite_bundle && acl_a_gamelog_download', 'cat' => array('ACP_elite_bundle')),
                'server_logins' => array('title' => 'ACP_SERVER_LOGINS', 'auth' => 'ext_eff/elite_bundle && acl_a_elite_lgntracker', 'cat' => array('ACP_elite_bundle')),
                'player_search' => array('title' => 'ACP_PLAYER_SEARCH', 'auth' => 'ext_eff/elite_bundle && acl_a_elite_iptracker', 'cat' => array('ACP_elite_bundle')),
                'activity_graphs' => array('title' => 'ACP_ACT_GRAPHS', 'auth' => 'ext_eff/elite_bundle && acl_a_elite_actracker', 'cat' => array('ACP_elite_bundle')),
                'system_messages' => array('title' => 'ACP_SYS_MSGS', 'auth' => 'ext_eff/elite_bundle && acl_a_elite_mstracker', 'cat' => array('ACP_elite_bundle')),
                'member_profile' => array('title' => 'ACP_WIKI_MEMBER_PROFILE', 'auth' => 'ext_eff/elite_bundle && acl_a_elite_wiki_member_profile', 'cat' => array('ACP_elite_bundle')),
			),
		);
	}
}
