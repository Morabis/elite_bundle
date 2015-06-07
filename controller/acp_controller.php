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


class acp_controller implements acp_interface
{
    protected $main_db;
    protected $wiki_db;
    protected $glogs_db;
	protected $request;
	protected $template;
    protected $pagination;
	protected $user;
    protected $tools;
    protected $log;
    protected $path_helper;
    protected $extension_manager;
	protected $phpbb_root_path;
	protected $php_ext;
	protected $u_action;

	public function __construct(\phpbb\db\driver\driver_interface $main_db, \phpbb\db\driver\driver_interface $wiki_db, \phpbb\db\driver\driver_interface $glogs_db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\pagination $pagination, \phpbb\user $user,\eff\elite_bundle\core\tools $tools,  \phpbb\log\log_interface $log,\phpbb\path_helper $path_helper,\phpbb\extension\manager $extension_manager, $phpbb_root_path, $php_ext)
	{
		$this->main_db = $main_db;
        $this->wiki_db = $wiki_db;
        $this->glogs_db = $glogs_db;
		$this->request = $request;
		$this->template = $template;
        $this->pagination = $pagination;
		$this->user = $user;
        $this->tools = $tools;
        $this->log = $log;
        $this->extension_manager = $extension_manager;
        $this->path_helper = $path_helper;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

    private function ext_path()
    {
        $extension_path = $this->path_helper->update_web_root_path($this->extension_manager->get_extension_path('eff/elite_bundle',true));

        return $extension_path;
    }

    public function display_game_logs()
    {
        $this->user->add_lang_ext('eff/elite_bundle','elite_bundle');

        $action = $this->request->variable('action','',true);
        switch($action)
        {

            default:
                $this->template->assign_vars(array(
                    'S_SUBMIT' => $this->u_action,
                    'ADM_PATH' => $this->phpbb_admin_path,
                    'S_APP_TITLE' => 'Log Files',
                    'S_APP_DESC' => "Department of Defense application for accessing server log files.<br/><br/>Provide a date/time range, select filter options and click on 'Show' to see logs or 'Download File' to download logs as a txt file.<br/><br/>A single request is limited to 5000 lines!",
                    'EXT_PATH' => $this->ext_path(),
                ));

                $sql = 'SELECT entry_ts
							FROM year_2014
							ORDER BY id DESC LIMIT 1';
                $result = $this->glogs_db->sql_query($sql);
                $last_record_date = (string) $this->glogs_db->sql_fetchfield('entry_ts');
                $this->glogs_db->sql_freeresult($result);
                $this->template->assign_var('LAST_RECORD_DATE',$last_record_date);

                break;

            case 'getdata':
                //decode post
                $dataString = htmlspecialchars_decode($this->request->variable('formdata','', false));

                //post string > array
                parse_str($dataString, $formdata);

                //build the query......
                $sql = $this->tools->buildQuery($formdata, $this->request->variable('idStart',0,true), 5000 );

                //output all entry
                $result = $this->glogs_db->sql_query($sql);

                //arrr, im a pirate
                $data = array();

                //loop data, parse eevents, put in pirate to hide in treasure chest
                while ($name = $this->glogs_db->sql_fetchrow($result))
                {
                    $name['entry_event_id'] = $this->tools->numberToEventname($name['entry_event_id']);
                    $data[] = $name;
                }
                //hand out tressure chest 'locked' *arrr*
                echo json_encode($data);

                //u know
                $this->glogs_db->sql_freeresult($result);
                //prevent any other output (error output in this case, cuz i didnt load tpl)
                die();
                break;

            case 'downloaddata':
                //txt header;
                header('Content-type: text/plain');
                header('Content-Disposition: attachment; filename="logfile.txt"');

                //GET the vars (url)
                @$formdata['start'] = $this->request->variable('start','');
                @$formdata['end'] = $this->request->variable('end','');
                @$formdata['data'] = $this->request->variable('data','');

                //loggings fun
                //add_log('admin', 'GET_GAMES_LOG_FILE', 'start: ' . $formdata['start'] . ' || end: ' . $formdata['end']);
                $this->log->add('admin',$this->user->data['user_id'],$this->user->data['user_ip'],'GET_GAMES_LOG_FILE',time(),array($formdata['start'],$formdata['end']));

                //build qry
                $sql=$this->tools->buildQuery($formdata);

                //output all entry
                $result = $this->glogs_db->sql_query($sql);

                //loop uiiiii
                while ($name = $this->glogs_db->sql_fetchrow($result))
                {
                    //event parsing
                    $name['entry_event_id'] = $this->tools->numberToEventname($name['entry_event_id']);

                    //unleash hell on earth (string to file)
                    echo $name['entry_ts'] . "\t" . $name['entry_event_id'] . ":\t" . $name['entry_content'] . "\r\n";
                }
                //db stuff
                $this->glogs_db->sql_freeresult($result);
                //no other output6
                die();
                break;

            case 'dataExists':
                //decode post
                $dataString = htmlspecialchars_decode($this->request->variable('formdata', '', false));

                //post string > array
                parse_str($dataString, $formdata);


                //build qry
                $sql=$this->tools->buildQuery($formdata);

                //output all entry
                $result = $this->glogs_db->sql_query($sql);

                $found = false;
                //loop uiiiii
                while ($name = $this->glogs_db->sql_fetchrow($result) && $found==false)
                {
                    $found=true;
                }
                echo $found?'strtrue':'strfalse';

                //db stuff
                $this->glogs_db->sql_freeresult($result);
                //no other output6
                die();
                break;
        }
    }

    public function display_server_logins()
    {
        $this->user->add_lang_ext('eff/elite_bundle','elite_bundle');

        $submit_user = $this->request->is_set_post('submit_user');
        $submit_ip = $this->request->is_set_post('submit_ip');
        $submit_password = $this->request->is_set_post('submit_pwd');
        //$update = $this->request->is_set_post('update');
        $search = $this->request->variable('search','all',true);

        $count = 0;
        $limit = 20;
        $start = $this->request->variable('start',0);
        $base_url = $this->u_action.'&amp;search='.$search;

        $this->template->assign_vars(array(
            'S_APP_TITLE'   => 'Server Logins',
            'S_APP_DESC'	=> 'SERVER_LOGINS_DESC',
            'S_FOUNDER' => $this->user->data['user_type'] == USER_FOUNDER ? true : false ,
            'EXT_PATH' => $this->ext_path(),
        ));

        $sql =
            'SELECT usr, pwd, date, ip
            FROM ' . 'phpbb_tcl_dump';

        if($submit_user || $search == 'user')
        {
            $by_user = $this->request->variable('user','',true);
            $search = 'user';
            if($by_user == ''){trigger_error('No results!'.adm_back_link($this->u_action),E_USER_WARNING);}
            $base_url = $this->u_action.'&amp;search='.$search.'&amp;user='.$by_user;
            $sql = $sql." WHERE usr LIKE '%" . $by_user . "%'";
        }

        if($submit_ip || $search == 'ip')
        {
            $by_ip = $this->request->variable('ip','',true);
            $search = 'ip';
            if($by_ip == ''){trigger_error('No results!'.adm_back_link($this->u_action),E_USER_WARNING);}
            $base_url = $this->u_action.'&amp;'.$search.'&amp;ip='.$by_ip;
            $sql = $sql." WHERE ip LIKE '%" . $by_ip . "%'";
        }

        if($submit_password || $search == 'password')
        {
            $by_password = $this->request->variable('pwd','',true);
            $search = 'password';
            if($by_password == ''){trigger_error('No results!'.adm_back_link($this->u_action),E_USER_WARNING);}
            $base_url = $this->u_action.'&amp;'.$search.'&amp;password='.$by_password;
            $sql = $sql." WHERE pwd LIKE '%" . $this->tools->text_encryption('encrypt',$by_password) . "%'";

        }
        /*if($update)
        {
            $id = array();
            $pwd = array();
            $sql =
                'SELECT ID, pwd
                FROM ' . 'phpbb_tcl_dump';

            $result = $this->db->sql_query($sql);

            while($row = $this->db->sql_fetchrow($result))
            {
                $id[]=$row['ID'];
                $pwd[]=$row['pwd'];
            }

            //trigger_error($id[1]);

            for($i=0; $i<count($id); $i++)
            {
                $sql_update = "UPDATE phpbb_tcl_dump SET pwd ='".$this->tools->encryption('encrypt',$pwd[$i])."' WHERE ID=".$id[$i];
                $this->db->sql_query($sql_update);
            }

        }*/

        $sql = $sql. ' ORDER BY date DESC';
        $result = $this->main_db->sql_query($sql);

        while($row = $this->main_db->sql_fetchrow($result))
        {
            if ($count>=$start && $count< $start+$limit)
            {
                $this->template->assign_block_vars('server_logins', array(
                    'USERNAME'		=> $row['usr'],
                    'PASSWORD'		=> $this->tools->text_encryption('decrypt',$row['pwd']),
                    'DATE'		=> $this->user->format_date($row['date'],'d M Y, H:i'),
                    'IP'		=> $row['ip']));
            }
            $count++;
        }

        $this->main_db->sql_freeresult($result);

        if($count == 0){trigger_error('No results!'.adm_back_link($this->u_action),E_USER_WARNING);}

        $this->pagination->generate_template_pagination($base_url,'pagination','start',$count,$limit,$start);

    }

    public function display_player_search()
    {
        $this->user->add_lang_ext('eff/elite_bundle','elite_bundle');

        $submit_user = $this->request->is_set_post('submit_user');
        $submit_user_ip = $this->request->is_set_post('submit_user_ip');
        $submit_user_id = $this->request->is_set_post('submit_user_id');

        $submit_player = $this->request->is_set_post('submit_player');
        $submit_player_ip = $this->request->is_set_post('submit_player_ip');

        $search = $this->request->variable('search','no',true);

        $sql = '';
        $start = $this->request->variable('start',0);
        $base_url = $this->u_action.'&amp;search='.$search;

        $this->template->assign_vars(array(
            'S_APP_TITLE'   => 'Player Tracker',
            'S_APP_DESC'	=> "Department of Defense application to search for players and users.",
            'EXT_PATH' => $this->ext_path(),
        ));

        if($submit_user || $search == 'user')
        {
            $user_name = $this->request->variable('user_name','',true);
            if($user_name == ''){trigger_error('No name was specified!'.adm_back_link($this->u_action),E_USER_WARNING);}
            $search = 'user';
            $tpl_ary = 'user';
            $base_url = $this->u_action.'&amp;search='.$search.'&amp;user_name='.$user_name;
            $this->template->assign_var('S_ID',true);

            $sql = 'SELECT user_id, user_ip, username
				FROM ' . USERS_TABLE . "
				WHERE LOWER(username) LIKE '%" . $user_name . "%'
				AND user_ip!=''
				ORDER BY username ASC";
        }

        if($submit_user_ip || $search == 'ip')
        {
            $user_ip = $this->request->variable('user_ip','',true);
            if($user_ip == ''){trigger_error('No ip address was given!'.adm_back_link($this->u_action),E_USER_WARNING);}
            $search = 'ip';
            $tpl_ary = 'user';
            $base_url = $this->u_action.'&amp;search='.$search.'&amp;user_ip='.$user_ip;
            $this->template->assign_var('S_ID',true);

            $sql = 'SELECT DISTINCT u.username, u.user_id, p.poster_ip as user_ip
				FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . " p
				WHERE p.poster_ip LIKE '%" .$user_ip. "%'" . '
				AND u.user_id=p.poster_id'. "
				AND u.username!='Anonymous'". '
				ORDER BY user_ip ASC';
        }

        if($submit_user_id || $search == 'id')
        {
            $user_id = $this->request->variable('user_id','',true);
            if($user_id == ''){trigger_error('No user ID was given!'.adm_back_link($this->u_action),E_USER_WARNING);}
            $search = 'id';
            $tpl_ary = 'user';
            $base_url = $this->u_action.'&amp;search='.$search.'&amp;user_id='.$user_id;
            $this->template->assign_var('S_ID',false);

            $sql = 'SELECT DISTINCT u.username, u.user_id, p.poster_ip as user_ip
				FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . " p
				WHERE p.poster_id = " .$user_id. '
				AND u.user_id=p.poster_id'. "
				AND u.username!='Anonymous'". '
				ORDER BY p.poster_ip ASC';
        }

        if($submit_player || $search == 'player')
        {
            $player_name = $this->request->variable('player_name','',true);
            if($player_name == ''){trigger_error('No player name was given!'.adm_back_link($this->u_action),E_USER_WARNING);}
            $search = 'player';
            $tpl_ary = 'player';
            $base_url = $this->u_action.'&amp;search='.$search.'&amp;player_name='.$player_name;

            $sql = 'SELECT player, ip, date
				FROM ' . 'server_players' . "
				WHERE player LIKE '%" . $player_name . "%'" . "
				ORDER BY date DESC";
        }

        if($submit_player_ip || $search == 'p_ip')
        {
            $player_ip = $this->request->variable('player_ip','',true);
            if($player_ip == ''){trigger_error('No ip address was given!'.adm_back_link($this->u_action),E_USER_WARNING);}
            $search = 'p_ip';
            $tpl_ary = 'player';
            $base_url = $this->u_action.'&amp;search='.$search.'&amp;player_ip='.$player_ip;

            $sql = 'SELECT player, ip, date
				FROM ' . 'server_players' . "
				WHERE ip LIKE '%" . $player_ip . "%'" . "
				ORDER BY date DESC";
        }

        if($tpl_ary == 'user')
        {
            $count = 0;
            $limit = 20;

            $result = $this->main_db->sql_query($sql);

            while($row = $this->main_db->sql_fetchrow($result))
            {
                if ($count>=$start && $count< $start+$limit)
                {
                        $this->template->assign_block_vars('user_search', array(
                        'USER_NAME'		=> $row['username'],
                        'USER_IP'		=> $row['user_ip'],
                        'USER_ID'		=> $row['user_id']));
                }
                $count++;
            }

            $this->main_db->sql_freeresult($result);

            if($count == 0){trigger_error('No results!'.adm_back_link($this->u_action),E_USER_WARNING);}

            $this->pagination->generate_template_pagination($base_url,'pagination','start',$count,$limit,$start);
        }

        if($tpl_ary == 'player')
        {
            $count = 0;
            $limit = 20;

            $result = $this->wiki_db->sql_query($sql);

            while($row = $this->wiki_db->sql_fetchrow($result))
            {
                if ($count>=$start && $count< $start+$limit)
                {
                        $this->template->assign_block_vars('player_search', array(
                            'PLAYER_NAME'		=> $row['player'],
                            'PLAYER_IP'		    => $row['ip'],
                            'PLAYER_DATE'		=> $row['date']));
                }
                $count++;
            }

            $this->wiki_db->sql_freeresult($result);

            if($count == 0){trigger_error('No results!'.adm_back_link($this->u_action),E_USER_WARNING);}

            $this->pagination->generate_template_pagination($base_url,'pagination','start',$count,$limit,$start);
        }
    }

    public function display_activity_graphs()
    {
        $this->user->add_lang_ext('eff/elite_bundle','elite_bundle');

        $search = $this->request->variable('search','no',true);
        $start = $this->request->variable('start',0);
        $count = 0;
        $limit = 25;
        $action = $this->request->variable('action','',true);

        $submit_full = $this->request->is_set_post('submit_full');
        $submit_members = $this->request->is_set_post('submit_members');
        $submit_recruits = $this->request->is_set_post('submit_recruits');

        if($submit_full || $submit_members || $submit_recruits || $search == 'full' || $search == 'members' || $search == 'recruits')
        {
            if($submit_full || $search == 'full') {
                $search = 'full';

                $sql = 'SELECT player, time, date
				FROM ' . 'activity'. '
				ORDER BY date DESC';
            }

            if($submit_members || $search == 'members') {
                $search = 'members';

                $sql = 'SELECT DISTINCT player, time, date
				FROM ' . 'activity'. "
				WHERE player LIKE '%»|EFF|«%'". "
				OR player LIKE '%eff.%'".'
				ORDER BY player ASC';
            }

            if($submit_recruits || $search == 'recruits') {
                $search = 'recruits';

                $sql = 'SELECT DISTINCT player, time, date
				FROM ' . 'activity'. "
				WHERE player LIKE '%(EFF)%'".'
				ORDER BY date DESC';
            }

            $result = $this->wiki_db->sql_query($sql);

            while($row = $this->wiki_db->sql_fetchrow($result))
            {
                if ($count>=$start && $count< $start+$limit)
                {
                    $this->template->assign_block_vars('players', array(
                        'NAME'		=> $row['player'],
                        'TIME'		=> $row['time'],
                        'DATE'		=> $row['date']));
                }
                $count++;
            }

            $this->wiki_db->sql_freeresult($result);

            if($count == 0){trigger_error('No results!'.adm_back_link($this->u_action),E_USER_WARNING);}

            $base_url = $this->u_action.'&amp;search='.$search;

            $this->pagination->generate_template_pagination($base_url,'pagination','start',$count,$limit,$start);

        }

        switch($action)
        {

            default:
                $this->template->assign_vars(array(
                    'S_SUBMIT' => $this->u_action,
                    'S_APP_TITLE' => 'title',
                    'S_APP_DESC' => "desc",
                    'EXT_PATH' => $this->ext_path(),
                ));
                break;

            case 'getdata':

                $sql = "SELECT date,time
				  FROM activity
				  WHERE player LIKE 123
			    AND date >= DATE_SUB( NOW( ) , INTERVAL 60 DAY )";

                $result = $this->wiki_db->sql_query($sql);

                while ($row = $this->wiki_db->sql_fetchrow($result))
                {
                    $dates[] = date('d.m.',strtotime($row['date']));
                    $times[] = (int)$row['time'];
                }

                for ($i=0; $i<60; $i++) {
                    $days[] = date('d.m.',time()-$i*24*60*60);
                    $minutes[] = 0;
                }

                $days = array_reverse($days);

                $combined_ary = array_combine($dates,$times);

                for ($i=0; $i<60 ;$i++)
                {
                    foreach($combined_ary as $date=>$time)
                    {
                        if ($date == $days[$i])
                            $minutes[$i] = (int)$time;
                    }
                }


                $graph = array(
                    'days' => $days,
                    'minutes' => $minutes,
                );

                echo json_encode($graph);

                //prevent any other output (error output in this case, cuz i didnt load tpl)
                die();
                break;

        }
    }

}


