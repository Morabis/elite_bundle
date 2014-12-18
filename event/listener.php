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
		);
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
}
