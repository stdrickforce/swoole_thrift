<?php
//jixunzhen@baixing.com

namespace Swoole\Thrift;

use Thrift\Protocol\TProtocol;
use Thrift\Protocol\TBinaryProtocol;

use Thrift\Transport\TMemoryBuffer;

use Thrift\Exception\TApplicationException;

use Thrift\Type\TMessageType;

class Server
{
    protected $sw;
    protected $host, $port;
    protected $processor;

    public function __construct($processor, $host, $port)
    {
        $this->processor = $processor;
        $this->sw = new \swoole_server($host, $port);
        $this->sw->set([
            'dispatch_mode' => 2,
            'reactor_num' => 1,
            'worker_num' => 1,
            'task_worker_num' => 2,
        ]);
        $this->sw->on('receive', [$this, 'onReceive']);
        $this->sw->on('task', [$this, 'onTask']);
        $this->sw->on('finish', [$this, 'onFinish']);
        $this->sw->on('start', [$this, 'onStart']);
        $this->sw->on('workerstart', [$this, 'onWorkerStart']);
        $this->sw->on('workerstop', [$this, 'onWorkerStop']);
    }

    public function setOptions(array $options)
    {
        $this->sw->set($options);
    }

    public function onStart($sw)
    {
        $pid = getmypid();
        echo "[$pid] server start..." . PHP_EOL;
    }

    public function onWorkerStart($sw, $wid)
    {
        $pid = getmypid();
        if ($sw->taskworker) {
            echo "[$pid] task worker $wid start..." . PHP_EOL;
        } else {
            echo "[$pid] worker $wid start..." . PHP_EOL;
        }
    }

    public function onWorkerStop($sw, $wid)
    {
        $pid = getmypid();
        echo "[$pid] worker $wid stop..." . PHP_EOL;
    }

    public function start()
    {
        $host = $this->sw->host;
        $port = $this->sw->port;
        echo "\033[32mSwoole socket server start at " . date("Y-m-d H:i:s") . " \033[0m\n";
        echo "\033[32mListen on " . $host . ":" . $port . ". \033[0m\n";
        echo "\033[32mPress Ctrl-C to quit. \033[0m\n";

        $this->sw->start();
    }

    protected function sendException(TProtocol $proto, string $message)
    {
        $ae = new TApplicationException($message);
        $proto->writeMessageBegin("unknown", TMessageType::EXCEPTION, 0);
        $ae->write($proto);
        $proto->writeMessageEnd();
        $proto->getTransport()->flush();
    }

    public function onReceive(\swoole_server $sw, int $fd, int $rid, $data)
    {
        static $parsers;

        if (!isset($parsers[$fd])) {
            $parsers[$fd] = new TBinaryParser;
        }
        $parser = $parsers[$fd];

        if ($parser->parse($data)) {
            $tid = $sw->task([
                'fd' => $fd,
                'iprot' => $parser->getProtocol(),
            ]);
            if ($tid === false) {
                throw new \Exception("task delivery failed");
            }
        }
    }

    public function onTask(\swoole_server $sw, int $tid, int $wid, $data)
    {
        $fd = $data['fd'];
        $iprot = $data['iprot'];

        $trans = new TMemoryBuffer;
        $proto = new TBinaryProtocol($trans);

        $this->processor->process($iprot, $proto);

        $sw->send($fd, $trans->getBuffer());
        return "";
    }

    public function onFinish(\swoole_server $sw, int $tid, $data)
    {
    }
}
