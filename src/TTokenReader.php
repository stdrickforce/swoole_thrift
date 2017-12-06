<?php

namespace Swoole\Thrift;

use Thrift\Type\TType;

use Thrift\Protocol\Protocol;

use Thrift\Exception\TProtocolException;

trait TTokenReader
{
    protected function _read()
    {
        if (count($this->tokens) < 1) {
            // TODO
            throw new TProtocolException("token insufficient");
        }
        return array_shift($this->tokens);
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
        if (TType::STOP == $ftype = $this->_readByte()) {
            return;
        }
        $fid = $this->_readI16();
    }

    public function readFieldEnd()
    {
        return;
    }

    public function readMapBegin(&$ktype, &$vtype, &$size)
    {
        $ktype = $this->_readByte();
        $vtype = $this->_readByte();
        $size = $this->_readI16();
        return;
    }

    public function readMapEnd()
    {
        return;
    }

    public function readListBegin(&$etype, &$size)
    {
        $etype = $this->_readByte();
        $size = $this->_readI16();
        return;
    }

    public function readListEnd()
    {
        return;
    }

    public function readSetBegin(&$etype, &$size)
    {
        $etype = $this->_readByte();
        $size = $this->_readI16();
        return;
    }

    public function readSetEnd()
    {
        return;
    }

    public function readMessageBegin(&$name, &$type, &$seqid)
    {
        $result = $this->readI32($sz);
        if ($sz >= 0) {
            throw new TProtocolException(
                'No version identifier, old protocol client?',
                TProtocolException::BAD_VERSION
            );
        }
        $version = (int) ($sz & self::VERSION_MASK);
        if ($version != (int) self::VERSION_1) {
            throw new TProtocolException(
                'Bad version identifier: '.$sz,
                TProtocolException::BAD_VERSION
            );
        }
        $type = $sz & 0x000000ff;
        $this->readString($name);
        $this->readI32($seqid);
    }

    public function readMessageEnd()
    {
        return;
    }
}
