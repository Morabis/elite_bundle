<?php
/**
*
* @package Advanced BBCode Box 3.1
* @copyright (c) 2013 Matt Friedman
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace eff\elite_bundle\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class listener implements EventSubscriberInterface
{
    protected $config;

	public function __construct(\phpbb\config\config $config)
	{
        $this->config = $config;
	}

	static public function getSubscribedEvents()
	{
		return array(
            'core.acp_board_config_edit_add'	=> 'load_config_on_setup',
            'core.user_setup'               => 'load_language_on_setup',
            'core.add_log'                  => 'add_custom_logs',
            'core.get_logs_modify_type'     => 'get_custom_logs',
            'core.common'                   => 'common_settings',
			'core.permissions'				=> 'add_permissions',
		);
	}

    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'eff/elite_bundle',
            'lang_set' => 'elite_bundle',
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function load_config_on_setup($event)
    {
        if ($event['mode'] == 'features')
        {
            $config_set_ext = $event['display_vars'];
            $config_set_vars = array_slice($config_set_ext['vars'], 0, 16, true);

            $config_set_vars['elite_bundle_minutes'] =
                array(
                    'lang' 		=> 'Minutes for Elite Apps cron',
                    'validate'	=> 'int',
                    'type'		=> 'number:0:99',
                    'explain'	=> true
                );
            $config_set_vars += array_slice($config_set_ext['vars'], 16, count($config_set_ext['vars']) - 1, true);
            $event['display_vars'] = array('title' => $config_set_ext['title'], 'vars' => $config_set_vars);
        }
    }

    public function add_custom_logs($event)
    {
        if($event['mode'] == 'wiki')
        {
            $sql_ary = $event['sql_ary'];
            $sql_ary['log_type'] = 4;
            $sql_ary['log_data'] = (!empty($event['additional_data'])) ? serialize($event['additional_data']) : '';
            $event['sql_ary'] = $sql_ary;
        }
    }

    public function get_custom_logs($event)
    {
        if($event['mode'] == 'wiki')
        {
            $log_type = $event['log_type'];
            $log_type = 4;
            $event['log_type'] = $log_type;
        }
    }

    public function common_settings($event)
    {
        define('LOG_WIKI', 4);
    }
	
	public function add_permissions($event)
	{
		// Create reputation category
		$categories = $event['categories'];
		$categories['elite_bundle'] = 'ACL_CAT_ELITE_BUNDLE';
		$event['categories'] = $categories;

		// Assign permissions to categories
		$permissions = $event['permissions'];
		$permissions = array_merge($permissions, array(
			// Admin permissions
			'a_elite_lgntracker'		=> array('lang' => 'ACL_A_ELITE_LGNTRACKER', 'cat' => 'elite_bundle'),
		));
		$event['permissions'] = $permissions;
	}
}
