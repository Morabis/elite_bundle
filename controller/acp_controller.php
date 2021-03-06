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
        $player_name = $this->request->variable('player_name','',true);

        $submit_full = $this->request->is_set_post('submit_full');
        $submit_members = $this->request->is_set_post('submit_members');
        $submit_recruits = $this->request->is_set_post('submit_recruits');
        $submit_player = $this->request->is_set_post('submit_player');

        if($submit_full || $submit_members || $submit_recruits || $submit_player || $search == 'full' || $search == 'members' || $search == 'recruits' || $search == 'player' )
        {
            if($submit_full || $search == 'full') {
                $search = 'full';

                $sql = 'SELECT DISTINCT player, time, date
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

            if($submit_player || $search == 'player')
            {
                $search = 'player';
                $player = $this->request->variable('player','',true);

                $sql = 'SELECT DISTINCT player, time, date
				FROM ' . 'activity'. "
				WHERE player LIKE _latin1'%".$player."%'".'
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
                    'S_APP_TITLE' => 'Activity Graphs',
                    'S_APP_DESC' => "Department of Recruiting tool to gather data about players' activity on server.",
                    'EXT_PATH' => $this->ext_path(),
                ));
                break;

            case 'getdata':

                $sql = "SELECT date,time
				  FROM activity
				  WHERE player ='".$player_name."'
			    AND date >= DATE_SUB( NOW( ) , INTERVAL 60 DAY )";

                $result = $this->wiki_db->sql_query($sql);
                $count = 0;

                while ($row = $this->wiki_db->sql_fetchrow($result))
                {
                    $dates[] = date('d.m.',strtotime($row['date']));
                    $times[] = (int)$row['time'];
                    $count++;
                }
                if($count < 1){trigger_error('No results!'.adm_back_link($this->u_action),E_USER_WARNING);}

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

    public function display_system_messages()
    {
        $this->user->add_lang_ext('eff/elite_bundle','elite_bundle');

        $this->template->assign_vars(array(
            'S_APP_TITLE'   => 'System Messages',
            'S_APP_DESC'	=> 'Administrative tool',
            'EXT_PATH' => $this->ext_path(),
        ));

        $submit_user = $this->request->is_set_post('submit_user');
        $submit_message = $this->request->is_set_post('submit_message');

        $search = $this->request->variable('search','no',true);
        $start = $this->request->variable('start',0);
        $count = 0;
        $limit = 15;

        $sql = 'SELECT user_id, username
				FROM ' . USERS_TABLE ."
				WHERE user_type != 2"."
				ORDER BY UPPER(username)";

        $result = $this->main_db->sql_query($sql);
        while ($row = $this->main_db->sql_fetchrow($result))
        {
            $this->template->assign_block_vars('users', array(
                'ID'		=> $row['user_id'],
                'NAME'		=> $row['username'],));
        }
        $this->main_db->sql_freeresult($result);

        if($submit_user || $search=='no' || $search=='full' || $search=='user')
        {
            if($search == 'no' || $search == 'full') {
                $search = 'full';

                $base_url = $this->u_action.'&amp;search='.$search;

                $sql = 'SELECT msg_id, author_id,message_time,message_subject,to_address
				FROM ' . PRIVMSGS_TABLE . '
				ORDER BY message_time DESC';
            }

            if($submit_user || $search == 'user') {
                $search = 'user';
                $user = $this->request->variable('user',0,true);
                list ($id, $sender) = explode(';',$this->request->variable('submit_user','',true));

                if($user == ''){$user = $id;}

                $base_url = $this->u_action.'&amp;search='.$search.'&amp;user='.$user;

                $sql = 'SELECT msg_id, author_id,message_time,message_subject,to_address
				FROM ' . PRIVMSGS_TABLE . "
				WHERE author_id=$user".'
				ORDER BY message_time DESC';
            }

                $result = $this->main_db->sql_query($sql);

                while($row = $this->main_db->sql_fetchrow($result))
                {
                    if ($count>=$start && $count< $start+$limit)
                    {
                        $msg_ids[] = $row['msg_id'];
                        $msg_from[] = $row['author_id'];
                        $msg_to[] = $row['to_address'];
                        $msg_title[] = $row['message_subject'];
                        $msg_time[] = $this->user->format_date($row['message_time'], 'd M Y, H:i');
                    }
                    $count++;
                }
                $this->main_db->sql_freeresult($result);

                if($count == 0){trigger_error('No results!'.adm_back_link($this->u_action),E_USER_WARNING);}

                for ($i=0;$i<count($msg_from);$i++)
                {
                    $sql = 'SELECT username
						FROM ' . USERS_TABLE . "
						WHERE user_id=$msg_from[$i]";
                    $result = $this->main_db->sql_query($sql);
                    $row = $this->main_db->sql_fetchrow($result);
                    $this->main_db->sql_freeresult($result);
                    $msg_from_names[] = $row['username'];
                }

                for ($i=0;$i<count($msg_to);$i++)
                {
                    $name = '';
                    $msg_to_ary = explode(':',$msg_to[$i]);
                    for ($j=0;$j<count($msg_to_ary);$j++)
                    {
                        if(preg_match('/^u_(\d)*$/',$msg_to_ary[$j],$matches))
                        {
                            $to_id = str_replace('u_', '', $matches[0]);
                            $sql = 'SELECT username
								FROM ' . USERS_TABLE . "
								WHERE user_id=$to_id";

                            $result = $this->main_db->sql_query($sql);
                            $row = $this->main_db->sql_fetchrow($result);
                            $this->main_db->sql_freeresult($result);

                            if($j > 0){$name = $name .','. $row['username'];}
                            else{$name = $row['username'];}
                        }
                        if(preg_match('/^g_(\d)*$/',$msg_to_ary[$j],$matches))
                        {
                            $group_id = str_replace('g_', '', $matches[0]);
                            $sql = 'SELECT group_name
								FROM ' . GROUPS_TABLE . "
								WHERE group_id=$group_id";

                            $result = $this->main_db->sql_query($sql);
                            $row = $this->main_db->sql_fetchrow($result);
                            $this->main_db->sql_freeresult($result);

                            if($j > 0){$name = $name .','. $row['group_name'];}
                            else{$name = $row['group_name'];}
                        }
                    }
                    $msg_to_names[] = $name;
                }

                for ($i=0;$i<count($msg_ids);$i++) {
                    $this->template->assign_block_vars('message', array(
                        'ID' => $msg_ids[$i],
                        'FROM' => $msg_from_names[$i],
                        'FROM_ID' => $msg_from[$i],
                        'TO' => $msg_to_names[$i],
                        'TITLE' => $msg_title[$i],
                        'TIME' => $msg_time[$i],));
                }

                $this->pagination->generate_template_pagination($base_url,'pagination','start',$count,$limit,$start);
        }

        if($submit_message)
        {
            $msg_opt = $this->request->variable('msg_opt','',true);
            list($msg_from, $msg_to, $msg_id) = explode(';',$msg_opt);

            $sql = 'SELECT message_text,bbcode_bitfield,bbcode_uid,enable_bbcode,enable_smilies,enable_magic_url,author_ip
				FROM ' . PRIVMSGS_TABLE . "
				WHERE msg_id=$msg_id";

            $result = $this->main_db->sql_query($sql);
            $row = $this->main_db->sql_fetchrow($result);
            $this->main_db->sql_freeresult($result);

            $bbcode_options = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
                (($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) +
                (($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);

            $message = generate_text_for_display($row['message_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $bbcode_options);

            $this->template->assign_var('S_MESSAGE',$message);
            $this->template->assign_var('S_FROM',$msg_from);
            $this->template->assign_var('S_TO',$msg_to);
            $this->template->assign_var('S_MID',$msg_id);
            $this->template->assign_var('S_IP',$row['author_ip']);
        }

    }
    

}


