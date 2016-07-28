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
			'title'		=> 'ACP_CAT_ELITE_BUNDLE',
			'modes'		=> array(
                'server_logins' => array('title' => 'ACP_SERVER_LOGINS', 'auth' => 'ext_eff/elite_bundle && acl_a_elite_lgntracker', 'cat' => array('ACP_ADMIN_TOOLS')),
                'player_search' => array('title' => 'ACP_PLAYER_SEARCH', 'auth' => 'ext_eff/elite_bundle && acl_a_elite_iptracker', 'cat' => array('ACP_ADMIN_TOOLS')),
                'activity_graphs' => array('title' => 'ACP_ACT_GRAPHS', 'auth' => 'ext_eff/elite_bundle && acl_a_elite_actracker', 'cat' => array('ACP_ADMIN_TOOLS')),
                'system_messages' => array('title' => 'ACP_SYS_MSGS', 'auth' => 'ext_eff/elite_bundle && acl_a_elite_mstracker', 'cat' => array('ACP_ADMIN_TOOLS')),
			),
		);
	}
}
