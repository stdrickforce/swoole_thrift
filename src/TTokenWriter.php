<?php

namespace Swoole\Thrift;

use Thrift\Protocol\Protocol;

trait TTokenWriter
{
    public function _writeBool(bool $bool)
    {
        $this->tokens[] = $bool;
    }

    public function _writeByte(int $byte)
    {
        $this->tokens[] = $byte;
    }

    public function _writeI16(int $i16)
    {
        $this->tokens[] = $i16;
    }

    public function _writeI32(int $i32)
    {
        $this->tokens[] = $i32;
    }

    public function _writeI64(int $i64)
    {
        $this->tokens[] = $i64;
    }

    public function _writeDouble(float $double)
    {
        $this->tokens[] = $double;
    }

    public function _writeString(string $string)
    {
        $this->tokens[] = $string;
    }

    public function writeBool($bool)
    {
        $this->_writeBool($bool);
    }

    public function writeByte($byte)
    {
        $this->_writeByte($byte);
    }

    public function writeI16($i16)
    {
        $this->_writeI16($i16);
    }

    public function writeI32($i32)
    {
        $this->_writeI32($i32);
    }

    public function writeI64($i64)
    {
        $this->_writeI64($i64);
    }

    public function writeDouble($double)
    {
        $this->_writeDouble($double);
    }

    public function writeString($string)
    {
        $this->_writeString($string);
    }

    public function writeStructBegin($name)
    {
        return;
    }

    public function writeStructEnd()
    {
        return;
    }

    public function writeFieldBegin($fname, $ftype, $fid)
    {
        return;
    }

    public function writeFieldEnd()
    {
        return;
    }

    public function writeFieldStop()
    {
        return;
    }

    public function writeMapBegin($ktype, $vtype, $size)
    {
        return;
    }

    public function writeMapEnd()
    {
        return;
    }

    public function writeListBegin($etype, $size)
    {
        return;
    }

    public function writeListEnd()
    {
        return;
    }

    public function writeSetBegin($etype, $size)
    {
        return;
    }

    public function writeSetEnd()
    {
        return;
    }

    public function writeMessageBegin($name, $type, $seqid)
    {
        return;
    }

    public function writeMessageEnd()
    {
        return;
    }
}
