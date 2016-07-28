<?php
/**
*
* @package Inactive Users
* @copyright (c) 2014 ForumHulp.com
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace eff\elite_bundle\migrations;

use phpbb\db\migration\migration;

class install_elite_bundle extends migration
{
	public function effectively_installed()
	{
		return isset($this->config['elite_bundle_version']) && version_compare($this->config['elite_bundle_version'], '1.0.0-a1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_data()
	{
		return array(
			//add config
			array('config.add', array('elite_bundle_version', '1.0.0-a1')),
			//add permission
			array('permission.add', array('a_elite_lgntracker', true)),
			array('permission.add', array('a_elite_iptracker', true)),
			array('permission.add', array('a_elite_actracker', true)),
			array('permission.add', array('a_elite_mstracker', true)),
			//add modules
			array('module.add', array('acp', 0, 'ACP_CAT_ELITE_BUNDLE')),
			array('module.add', array('acp', 'ACP_CAT_ELITE_BUNDLE', 'ACP_ADMIN_TOOLS')),
			array('module.add', array(
				'acp', 'ACP_ADMIN_TOOLS', array(
					'module_basename'	=> '\eff\elite_bundle\acp\elite_bundle_module', 'modes' => array('server_logins', 'player_search', 'activity_graphs', 'system_messages'),
				),
			)),
		);
	}
}