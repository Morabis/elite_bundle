<?php
/**
 *
 * Elite Apps extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eff\elite_bundle\core;

class tools
{
    public $server;
    protected $wiki_db;
    protected $secret_key;
    protected $secret_iv;

	public function __construct(\eff\elite_bundle\core\server_query $server, \phpbb\db\driver\driver_interface $wiki_db, $secret_key, $secret_iv)
	{
        $this->server = $server;
        $this->wiki_db = $wiki_db;
        $this->secret_key = $secret_key;
        $this->secret_iv = $secret_iv;
	}

	public function text_encryption($action, $text)
	{
        $output = false;

        $encrypt_method = "AES-256-CBC";

        //hash
        $key = hash('sha256',$this->secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256',$this->secret_iv),0,16);

        switch($action)
        {
            case encrypt:

                $output = openssl_encrypt($text,$encrypt_method,$key,0,$iv);
                $output = base64_encode($output);

                break;
            case decrypt:

                $output = openssl_decrypt(base64_decode($text),$encrypt_method,$key,0,$iv);

                break;
        }

        return $output;
	}

    public function numberToEventname($number)
    {
        //i refuse to explain this - read!
        switch ($number)
        {
            case 1:
                return 'InitGame';
                break;
            case 2:
                return 'SERVER';
                break;
            case 3:
                return 'ClientConnect';
                break;
            case 4:
                return 'ClientUserinfoChanged';
                break;
            case 5:
                return 'ClientBegin';
                break;
            case 6:
                return 'say';
                break;
            case 7:
                return 'say_clan';
                break;
            case 8:
                return 'say_admin';
                break;
            case 9:
                return 'say_team';
                break;
            case 10:
                return 'Kill';
                break;
            case 11:
                return 'Item';
                break;
            case 12:
                return 'ClientDisconnect';
                break;
            case 13:
                return 'Exit';
                break;
            case 14:
                return 'tell';
                break;
            case 15:
                return 'ADMIN CMD EXECUTED';
                break;
            case  16:
                return 'DUEL END';
                break;
            case 17:
                return 'ShutdownGame';
                break;
            case 18:
                return 'setteam';
                break;
            case 19:
                return 'connected under IP';
                break;
            case 20:
                return 'is logged as an';
                break;
            case 21:
                return 'Player with same IP';
                break;
            case 22:
                return 'USES plugin';
                break;
        }
    }

    public function buildQuery($formdata,$start=0,$limit=null)
    {
        //build query, simple aint it?
        $sql = 'SELECT entry_ts, entry_event_id, entry_content FROM '
            . 'year_2014' . ' WHERE (entry_ts >= "'
            . $formdata['start'] . '" AND entry_ts <="'
            . $formdata['end'] . '")';

        //got data?
        if (isset($formdata['data']))
        {
            $first = true;
            $sql .= 'AND (';
            foreach ($formdata['data'] as $single)
            {
                if (!$first)
                {
                    $sql .= ' OR ';
                }
                else
                {
                    $first = false;
                }
                $sql .= 'entry_event_id = ' . $single;
            }
            $sql .= ')';

        }
        //order by ts, cuz we want it nice! :)
        $sql .= ' ORDER BY entry_ts';
        if($limit!=null)
        {
            //add limit (for dl);
            $sql.= ' LIMIT '.$start.','.$limit;
        }
        return $sql;
    }

    public function activityCronTask()
    {
        if($this->server->online)
        {
            $this->server->send_cmd('getstatus');
            $response = $this->server->process_data($this->server->get_response(15));
            $Players = array();

            //get online player names
            foreach($response['players'] as $player)
            {
                //remove color tags
                $player['name'] = preg_replace('/\^[0-9]/', '',$player["name"]);

                //exclude bots and players with padawan names
                if($player['ping']>0 && preg_match('/[Pp]adawan\(?\d*\)?/',$player['name'])==false)
                    $Players[] = $this->wiki_db->sql_escape($player['name']);
            }

            if(sizeof($Players))
            {

             //select those players who are stored in db on the given day
                $sql = "SELECT player
		        FROM activity
		        WHERE " . $this->wiki_db->sql_in_set('player', $Players) ."
		        AND date = CURDATE()";

                $result = $this->wiki_db->sql_query($sql);

                if($result)
                {
                    $dbPlayers = array();

                    while($row = $this->wiki_db->sql_fetchrow($result))
                    {
                        array_push($dbPlayers,$row[0]);
                    }
                }
                $this->wiki_db->sql_freeresult($result);

                $addPlayers = $alrPlayers = array();

                //if player wasn't stored in db put to $addPlayers else put to $alrPlayers
                foreach($Players as $player)
                {
                    if(!in_array($player,$dbPlayers))
                    {
                        $addPlayers[] = array(
                        'player'=>utf8_encode($player),
                        'status'=>1,
                        'time'=>-1,
                        'date'=>'CURDATE()',
                        'datetime'=>'CURTIME()',
                        );
                    }
                    else
                        array_push($alrPlayers,$player);
                }

                //add new players to db
                if(!empty($addPlayers))
                    $this->wiki_db->sql_multi_insert('activity',$addPlayers);

                //update status of players who were in db before and now are on server
                if(!empty($alrPlayers))
                {
                    $sql = "UPDATE activity
                          SET status='1', datetime=CURTIME()".
                         "WHERE ".$this->wiki_db->sql_in_set('player',$alrPlayers)."
                          AND date=CURDATE()";

                    $this->wiki_db->sql_query($sql);
                }

                unset($addPlayers, $alrPlayers);

            }
            //get players who were online on previous check

            $onPlayers = $offPlayers = $plusPlayers =  array();

            $sql = "SELECT player FROM activity WHERE status='1' AND date=CURDATE()";

            $result = $this->wiki_db->sql_query($sql);

            if($result)
            {
                while($row = $this->wiki_db->sql_fetchrow($result))
                {
                    array_push($onPlayers,$row[0]);
                }

                foreach($onPlayers as $player)
                {
                    if(sizeof($Players) && in_array($player,$Players))
                        array_push($plusPlayers,$player);
                    else
                        array_push($offPlayers,$player);

                }
            }
            $this->wiki_db->sql_freeresult($result);

            //set status to 0 for players who aren't on server anymore
            if(!empty($offPlayers))
            {
                $sql = "UPDATE activity
                          SET status='0'".
                    "WHERE ".$this->wiki_db->sql_in_set('player',$offPlayers)."
                          AND date=CURDATE()";

                $this->wiki_db->sql_query($sql);
            }

            //+1 minute for players who are still on server
            if(!empty($plusPlayers))
            {
                $sql = "UPDATE activity
                          SET time=time+1, datetime=CURTIME()".
                    "WHERE ".$this->wiki_db->sql_in_set('player',$plusPlayers)."
                          AND date=CURDATE()";

                $this->wiki_db->sql_query($sql);
            }
        }
    }
}
