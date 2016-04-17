<?php
/**
*
* @package Inactive Users
* @copyright (c) 2014 ForumHulp.com
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace eff\elite_bundle\migrations;

class install_elite_bundle extends \phpbb\db\migration\migration
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
			array('config.add', array('elite_bundle_gc', 60)),
			array('config.add', array('elite_bundle_last_gc', '0', 1)),
			array('config.add', array('elite_bundle_minutes', 3)),
			array('config.add', array('elite_bundle_version', '1.0.0-a1')),
			//add permission
			array('permission.add', array('a_elite_lgntracker', true))
		);
	}
}