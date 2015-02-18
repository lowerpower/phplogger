<?php                                                       //github.com/lowerpower/phplogger (C)mycal.net
//  
//
//  UDP Logger class sends a log line over UDP to be written to a file later 
//
//  The UDP datagram log format is as follows:
//  [$cmd] [$filename] [$timestamp] [rest of line message to log]
//
//  Currently there are only 2 commands L and F for Log and Flush.
//
//  The log daemon will read these log messages, queue them up and then bulk write them to the specified
//  file.  This allows the loging program to log and not have to worry about log file write
//  times impacting the software package performance while stil allowing logs.
//
//  To use:
//
//  $logme= new UDPLogger("localhost",1024);
//
//
//
//
//define('LOGGERVERBOSE', 1);  

class UDPLogger
{
    private $host;
    private $port;
    private $socket;

    function __construct($target_host="localhost", $target_port=1025)
    {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if($this->socket!==FALSE)
            socket_bind($this->socket,0,0);
        else
        {
            echo("failed to get socket for UDPLogger\n");
            return -1;
        }
        $this->host=$target_host;
        $this->port=$target_port;
        if(defined('LOGGERVERBOSE')) echo("Target Log Port $this->host:$this->port\n");
    }

    public function logit($file,$msg)
    {
        
        // Build Log Packet [$filename][$timestamp][$msg]
        $packet="L ".$file." ".$this->get_timestamp()." ".$msg;

        // Send the packet
        socket_sendto($this->socket, $packet, strlen($packet), 0 /* MSG_EOF */, $this->host, $this->port);

    }

    public function flush($file="*")
    {
        // Filename or * for all
        // Build Log Packet [$filename] L [$timestamp][$msg]
        $packet="F ".$file." ".$this->get_timestamp();

        // Send the packet
        if(defined('LOGGERVERBOSE')) echo("Sending to $this->host:$this->port --> $packet\n\n");
        socket_sendto($this->socket, $packet, strlen($packet), 0 /* MSG_EOF */, $this->host, $this->port);
    }

    function get_timestamp()
    {
        $today = getdate();
        $stamp=str_pad($today["mon"],2,"0",STR_PAD_LEFT)."/".str_pad($today["mday"],2,"0",STR_PAD_LEFT)."-".str_pad($today["hours"],2,"0",STR_PAD_LEFT).":".str_pad($today["minutes"],2,"0",STR_PAD_LEFT).":".str_pad($today["seconds"],2,"0",STR_PAD_LEFT);

        $stamp = date( 'd/M/Y_H:i:s');

        return($stamp);
    }
}









