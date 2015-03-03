phplogger
=========
Logging classes for PHP, client server approch to limit I/O and CPU.  Currently used to write to log files, but could be expanded write to other back ends.  We also use this for other non PHP software with the PHP log_receiver.php

### Problem to Solve
We had some projects in PHP that needed to write to log files, we tried several methods.  

1. Use error_log, this writes to webserver errorlog, seems to be fine except you have to parse the data from the errorlog, and could not write different log files for different modules.  

2. Open a file, append a line, close the file; this works well and gives you flexablity of writing multiple logs, and cycling logs.  The downside is that this is I/O intensive and can cause performance problems in heavy used servers.  You also have to deal with file locking and waiting for other processes.

### This Solution
This solution uses 2 pieces, a client side writer component that is included in a project, and a reader component that is run standalong as a background process.   The client component communicates to the reader component via UDP over localhost (though remote hosts could be possible).  The reader component batches up the writes for a particular file or resource and flushes them out at a settable interval.  The client side also has some control on when the logs are flushed.

Sending the logs over UDP has several interesting advantages. First there is little impact on the client side, UDP sockets should be setup to not block, if the buffer or the network is not ready, the logs just get tossed, no waits are put on the sender. Second, on the reciever side, you can dynamically filter, or not log at all until you need to. If you choose not to listen to the UDP traffic, it just goes away.  If you are receiving the UDP traffic, you can filter what you want to log, no need to change the running client code.

### How it Works

The client side sends log messages to the reader component via UDP datagrams by default on port 1025.  The datagrams are in the following format:

`[$cmd] [$filename] [$timestamp] [rest of line message to log]`

where:
`
  $cmd is L for log and F for flush.
  $filename is the filename to log to or flush, if flush * is acceptable
  $timestamp to use for log
`
The rest of the line is the message to log.

### How to Use

For now see the example test_log_server.php for the reader component and test_send_log.php for the writer component.


### License


  




