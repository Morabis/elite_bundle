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

class server_query
{
    protected $ip;
    protected $port;
    protected $rcon;
    private $fp;
    private $cmd;
    public $online;
    public $error;

    public function __construct($ip,$port,$rcon) {
        $this->rcon = $rcon;
        $this->cmd = str_repeat(chr(255), 4);
        $this->online = true;
        $this->fp = fsockopen("udp://$ip", $port, $errno, $errstr, 30);
        if (!$this->fp)
        {
            $this->error = "$errstr ($errno)<br />\n";
            $this->online = false;
        }

    }

    /**
     *Set rconpassword
     *
     *@param $p string password to set
     */
    public function set_rconpassword($p) {
        $this->rconpassword = $p;
    }

    /**
     * Send rcon command to server
     * @param $s string the command to send
     */
    public function send_rcon_cmd($s) {
        sleep(1);
        $this->send('rcon '.$this->rcon.' '.$s);
    }

    /**
     * Send simple command to server eg.: getstatus/getinfo
     * @param $s string the command the send
     */
    public function send_cmd($s)
    {
        sleep(1);
        $this->send($s);
    }

    /**
     * Get response from server
     * @param int $timeout  timelimit while wait for response
     * @return string       response from server
     */
    public function get_response($timeout=5) {
        $s = '';
        $bang = time() + $timeout;
        while (!strlen($s) and time() < $bang) {
            $s = $this->recv();
        }
        if (substr($s, 0, 4) != $this->cmd) {
        }
        return substr($s, 4);
    }

    /**
     * Send/write input string to socket
     * @param $string   string to send
     */
    private function send($string) {
        fwrite($this->fp, $this->cmd . $string . "\n");
    }

    /**
     * Recieve response from socket
     * @return string   returns with response
     */
    private function recv() {
        return fread($this->fp, 9999);
    }

    /**
     * Removing header from getstatus response
     * @param $data             the response from server
     * @return array|string     returns with modified response
     */
    private function preprocess($data)
    {
        $buf = new server_query_buffer($data);

        $buf->read(20);
        $this->error.=$buf->error();

        return $buf->getBuffer();
    }

    /**
     * Create formatted result of response
     * @param $data         response
     * @return mixed        returns with multi-dim array containing server and player info
     */
    public function process_data($data)
    {
        $result = new server_query_result();

        $data = $this->preprocess($data);

        $buf = new server_query_buffer($data);

        $server_info = $buf->readString('\x0A');

        list ($string, $player_info) = explode('eae4fc6ce15f63d94086b4e5a22e2a8d', $server_info);
        unset($buf, $string);

        $buf_server = new server_query_buffer($server_info);

        while ($buf_server->getLength())
        {
            $result->add(
                $buf_server->readString('\\'),
                $buf_server->readStringMulti(array('\\',"\x0a"),$delimfound)
            );

            if($delimfound === "\x0a")
            {
                break;
            }
        }

        $this->parse_players($result,$player_info);

        unset($server_info,$player_info,$delimfound,$buf_server,$data);

        //print_r($result);
        return $result->fetch();
    }

    /**
     * Add players array to result
     * @param server_query_result $result   result object
     * @param $players_info                 string of players, their score and ping
     * @return server_query_result          returns with players array
     */
    private function parse_players(server_query_result $result, $players_info)
    {

        $players = explode("\x0A",$players_info);

        array_pop($players);

        $result->add('num_players',count($players));

        foreach($players AS $player_info)
        {
            $buf = new server_query_buffer($player_info);

            $result->addPlayer('frags',$buf->readString("\x20"));
            $result->addPlayer('ping',$buf->readString("\x20"));


            $buf->skip(1);

            $name = trim($buf->readString('"'));
            $result->addPlayer('name',$name);
        }

        return $result;
    }

}
