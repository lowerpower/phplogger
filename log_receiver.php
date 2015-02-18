<?php                                                       //github.com/lowerpower/phplogger (C)mycal.net
//
//  UDP LogRX Receives UDP log events send by UDPLogger and at some interval flushes them
//  out to a file.
//
//  The UDP datagram log format is as follows:
//  [$cmd] [$filename] [$timestamp] [rest of line message to log]
//
//  Currently there are only 2 commands L and F for Log and Flush.
//
//  The log daemon will read these log messages, queue them up and then bulk write them at a predefined interval
//  to the specified file/database/resource.  This allows the loging program to log and not have to worry 
//  about log file write times impacting the software package performance while stil allowing logs.
//
//  By flushing at an interval (default 30 seconds) we try to have a low impact on FileSystem, increasing the interval
//  should lower the logging impact on the system.
//
//  To use:
//
//  $rx_log= new UDPLogger("localhost",1024);
//
//

class UDPLogRX
{
    const   LOG_ROOT_DEFAULT = "/tmp/";
    private $host;
    private $port;
    private $log_root;
    private $socket;
    private $queue=array();
    private $write_function;
    private $timestamp;
    private $timeout_value;

    function __construct($target_host="localhost", $listen_port=1025, $flush_time=30)
    {
        $this->timestamp=time();
        
        $this->set_log_root_dir(self::LOG_ROOT_DEFAULT);
        $this->log_flush_time($flush_time);

        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if(socket_bind($this->socket,$target_host,$listen_port))
        {
            socket_set_option($this->socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>2, "usec"=>0));
            socket_set_option($this->socket, SOL_SOCKET, SO_RCVBUF, 512*1024);
        }
    }

    //
    public function set_log_root_dir($dir)
    {
        // Check if directory is there

        // Create Directory

        $this->log_root=$dir;
        if(defined('LOGGERVERBOSE')) echo("Setting log base to $this->log_root\n");
        // Return
    
    }

    public function log_flush_time($time_in_seconds)
    {
        $this->timeout_val=$time_in_seconds;
    }

    // Time is array("sec"=>1, "usec"=>0)
    public function log_rx_socket_timeout($time)
    {
        socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, $time);
    }

    public function log_insert($resource,$message)
    {
        if(defined('LOGGERVERBOSE')) echo("Inserting Log Entry to $resource --> $message\n");
        $this->queue[$resource][]=$message;
    }

    public function write_to_file($file, $data_array)
    {
        // write all data into buffer before file write, least I/O as possible
        $write_buffer="";
        foreach( $data_array as $line ) {
            $write_buffer.="$line\n";
        }
        if(defined('LOGGERVERBOSE')) echo("Writing to log file $this->log_root.$file --> $write_buffer\n");
        
        // Append to the log
        if(strlen($write_buffer))
            file_put_contents ( $this->log_root.$file, $write_buffer, FILE_APPEND);
    }

    // Single log check, returns when a log was recieved over UDP or timeout
    public function log_check()
    {
       if(@socket_recvfrom($this->socket, $buffer, 2048, 0, $from, $port))
       {
            //echo("got one from $from, $port -->$buffer\n");
            // parse buffer int [type] [filename] [timestamp] [data]
            $packet=explode(" ",$buffer,3);
           
            if("F"===$packet[0])
            {
                // Flush
                if(defined('LOGGERVERBOSE')) echo("Flush Request for $packet[1]\n");
                flush($packet[1]);
            }
            else if("L"===$packet[0])
            {
                // Got a log entry, queue it up if all data is there.
                if( isset($packet[1]) && isset($packet[2]) )
                {
                    // Call Insert to insert this entry
                    $this->log_insert($packet[1],$packet[2]);
                }
            }
       }
    }

    public function flush($resource="*")
    {

        // Flush the logs
        if("*"===$resource)
        {
            if(defined('LOGGERVERBOSE')) echo("flush all\n");
            // Flush them all, itterate through the array
            foreach( $this->queue as $key=> $element ) {
                // Need to dump this
                if(defined('LOGGERVERBOSE')) echo("flush $key\n");
                $this->write_to_file($key,$this->queue[$key]);
                // delete the written data
                unset($this->queue[$key]);
            }
        }
        else
        {
            if(defined('LOGGERVERBOSE')) echo("flush $resource\n");
            // Flush just one resorce, itterate through the specific element
            if(isset($this->queue[$resource]))
            {
                // Need to dump this
                $this->write_to_file($resource,$this->queue[$resource]);
                // delete the written data
                unset($this->queue[$resource]);
            }
        }
    }

    public function log_forever()
    {
        //loop forver processing packets
        while(1)
        {
            $this->log_check();
            // See if log interval has expired
            if( (time()-$this->timestamp) > $this->timeout_val)
            {
                if(defined('LOGGERVERBOSE')) echo("timer flush\n");
                //
                $this->flush();
                //
                // reset timestamp
                $this->timestamp=time();
                
            }
        }
    }
}









