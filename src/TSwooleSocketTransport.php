<?php

namespace Alaya\Server;

use Thrift\Transport\TTransport;
use Thrift\Transport\TMemoryBuffer;
use Thrift\Exception\TTransportException;

class TSwooleSocketTransport extends TTransport
{
    protected $mb;
    protected $sw;
    protected $fd;

    public function __construct(string $data, \swoole_server $sw, int $fd)
    {
        $this->mb = new TMemoryBuffer($data);
        $this->sw = $sw;
        $this->fd = $fd;
    }

    public function isOpen()
    {
        return $this->sw->exist($this->fd);
    }

    public function open()
    {
    }

    public function close()
    {
    }

    public function read($len)
    {
        return $this->mb->read($len);
    }

    public function write($buf)
    {
        $info = $this->sw->connection_info($this->fd);
        if ($info === false) {
            throw new TTransportException(
                "connection reset by peer",
                TTransportException::NOT_OPEN
            );
        } else {
            $this->sw->send($this->fd, $buf);
        }
    }

    public function flush()
    {
    }
}
