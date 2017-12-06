<?php

namespace Swoole\Thrift;

use Thrift\Protocol\Protocol;

trait TTokenReader
{
    protected function _read()
    {
        if (count($this->token) < 1) {
            // TODO
            throw new ProtocolException("token insufficient");
        }
        return array_unshift($this->tokens);
    }

    public function _readBool() : bool
    {
        return $this->_read();
    }

    public function _readByte() : int
    {
        return $this->_read();
    }

    public function _readI16() : int
    {
        return $this->_read();
    }

    public function _readI32() : int
    {
        return $this->_read();
    }

    public function _readI64(): int
    {
        return $this->_read();
    }

    public function _readDouble() : float
    {
        return $this->_read();
    }

    public function _readString() : string
    {
        return $this->_read();
    }

    public function readBool(&$v)
    {
        $v = $this->_readBool();
    }

    public function readByte(&$v)
    {
        $v = $this->_readByte();
    }

    public function readI16(&$v)
    {
        $v = $this->_readI16();
    }

    public function readI32(&$v)
    {
        $v = $this->_readI32();
    }

    public function readI64(&$v)
    {
        {
        $v = $this->_readI64();
        }
    }

    public function readDouble(&$v)
    {
        $v = $this->_readDouble();
    }

    public function readString(&$v)
    {
        $v = $this->_readString();
    }

    public function readStructBegin(&$name)
    {
        return;
    }

    public function readStructEnd()
    {
        return;
    }

    public function readFieldBegin(&$fname, &$ftype, &$fid)
    {
        return;
    }

    public function readFieldEnd()
    {
        return;
    }

    public function readFieldStop()
    {
        return;
    }

    public function readMapBegin(&$ktype, &$vtype, &$size)
    {
        return;
    }

    public function readMapEnd()
    {
        return;
    }

    public function readListBegin(&$etype, &$size)
    {
        return;
    }

    public function readListEnd()
    {
        return;
    }

    public function readSetBegin(&$etype, &$size)
    {
        return;
    }

    public function readSetEnd()
    {
        return;
    }

    public function readMessageBegin(&$name, &$type, &$seqid)
    {
        return;
    }

    public function readMessageEnd()
    {
        return;
    }
}
