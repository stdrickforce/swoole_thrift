<?php

namespace Swoole\Thrift;

use Thrift\Protocol\TProtocol;

class TTokenProtocol extends TProtocol
{
    protected $tokens = [];

    const VERSION_MASK = 0xffff0000;
    const VERSION_1 = 0x80010000;

    use TTokenWriter;
    use TTokenReader;

    public function __construct()
    {
        $this->tokens = [];
    }

    public function getTokens()
    {
        return $this->tokens;
    }
}
