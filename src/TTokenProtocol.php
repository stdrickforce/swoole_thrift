<?php

namespace Swoole\Thrift;

use Thrift\Protocol\TProtocol;

class TTokenProtocol extends TProtocol
{
    protected $tokens = [];

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
