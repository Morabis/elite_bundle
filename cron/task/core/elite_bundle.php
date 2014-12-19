<?php

namespace eff\elite_bundle\cron\task\core;

class elite_bundle extends \phpbb\cron\task\base
{
	protected $config;
    protected $log;
    protected $user;

	public function __construct(\phpbb\config\config $config, \phpbb\log\log_interface $log, \phpbb\user $user)
	{
		$this->config = $config;
        $this->log = $log;
        $this->user = $user;
	}

	public function run()
	{
        $this->user->add_lang_ext('eff/elite_bundle','elite_bundle');
        $this->log->add('admin','2','127.0.0.1','RUN_TASK',time());
        $this->config->set('elite_bundle_last_gc',time());
	}

	public function is_runnable()
	{
        return (bool) $this->config['elite_bundle_minutes'];
	}

	public function should_run()
	{
		return $this->config['elite_bundle_last_gc'] < time() - $this->config['elite_bundle_gc'];
	}
}
