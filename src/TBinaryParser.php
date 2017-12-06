<?php

namespace Swoole\Thrift;

use Thrift\Type\TType;
use Thrift\Transport\TMemoryBuffer;

class TBinaryParser
{
    const T_SIZE = 31;
    const T_HEAEDER = 32;

    protected $tokens = [];
    protected $buf;
    protected $proto;

    public function getProtocol()
    {
        return $this->proto;
    }

    public function getBuffer()
    {
        return $this->buf->getBuffer();
    }

    protected function convertI64($hi, $lo)
    {
        if (PHP_INT_SIZE == 4) {
            $isNeg = $hi  < 0;

            // Check for a negative
            if ($isNeg) {
                $hi = ~$hi & (int) 0xffffffff;
                $lo = ~$lo & (int) 0xffffffff;

                if ($lo == (int) 0xffffffff) {
                    $hi++;
                    $lo = 0;
                } else {
                    $lo++;
                }
            }

            // Force 32bit words in excess of 2G to pe positive - we deal wigh sign
            // explicitly below

            if ($hi & (int) 0x80000000) {
                $hi &= (int) 0x7fffffff;
                $hi += 0x80000000;
            }

            if ($lo & (int) 0x80000000) {
                $lo &= (int) 0x7fffffff;
                $lo += 0x80000000;
            }

            $value = $hi * 4294967296 + $lo;

            if ($isNeg) {
                $value = 0 - $value;
            }
        } else {
            // Upcast negatives in LSB bit
            if ($lo & 0x80000000) {
                $lo = $lo & 0xffffffff;
            }

            // Check for a negative
            if ($hi & 0x80000000) {
                $hi = $hi & 0xffffffff;
                $hi = $hi ^ 0xffffffff;
                $lo = $lo ^ 0xffffffff;
                $value = 0 - $hi * 4294967296 - $lo - 1;
            } else {
                $value = $hi * 4294967296 + $lo;
            }
        }
        return $value;
    }

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->buf = new TMemoryBuffer;
        $this->proto = new TTokenProtocol;
        $this->stack = [
            // message body
            TType::STRUCT,
            // message header
            TType::I32,     // seqid
            TType::STRING,  // name
            TType::I32,     // version
        ];
    }

    public function parse($data)
    {
        $this->buf->write($data);

        while (count($this->stack) != 0) {
            $state = array_pop($this->stack);
            if (!$this->eat($state)) {
                $this->stack[] = $state;
                break;
            }
        }

        if (count($this->stack) == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function eat($state)
    {
        switch ($state) {
            case TType::BOOL:
                return $this->bool();
            case TType::BYTE:
                return $this->byte();
            case TType::DOUBLE:
                return $this->double();
            case TType::I16:
                return $this->i16();
            case TType::I32:
                return $this->i32();
            case TType::I64:
                return $this->i64();
            case TType::STRING:
                return $this->string();
            case TType::STRUCT:
                return $this->struct();
            case TType::LST:
            case TType::SET:
                return $this->list();
            case TType::MAP:
                return $this->map();
                return $this->header();
            case self::T_SIZE;
                return $this->size();
            default:
                throw new \Exception("你这什么玩意啊？");
        }
    }

    protected function bool()
    {
        if ($this->buf->available() < 1) {
            return false;
        }
        list(,$v) = unpack('c', $this->buf->read(1));
        $this->proto->writeBool($v == 1);
        return true;
    }

    protected function byte()
    {
        if ($this->buf->available() < 1) {
            return false;
        }
        $x = $this->buf->read(1);
        list(,$v) = unpack('c', $this->buf->read(1));
        $this->proto->writeByte($v);
        return true;
    }

    protected function double()
    {
        if ($this->buf->available() < 8) {
            return false;
        }
        list(,$v) = unpack('d', strrev($this->buf->read(8)));
        $this->proto->writeDouble($v);
        return true;
    }

    protected function i16()
    {
        if ($this->buf->available() < 2) {
            return false;
        }
        list(,$v) = unpack('n', $this->buf->read(2));
        if ($v > 0x7fff) {
            $v = 0 - (($v - 1) ^ 0xffff);
        }
        $this->proto->writeI16($v);
        return true;
    }

    protected function i32()
    {
        if ($this->buf->available() < 4) {
            return false;
        }
        list(,$v) = unpack('N', $this->buf->read(4));
        if ($v > 0x7fffffff) {
            $v = 0 - (($v - 1) ^ 0xffffffff);
        }
        $this->proto->writeI32($v);
        return true;
    }

    protected function i64()
    {
        if ($this->buf->available() < 8) {
            return false;
        }
        list(,$hi, $lo) = unpack('N2', $this->buf->read(8));
        $v = $this->convertI64($hi, $lo);
        $this->proto->writeI64($v);
        return true;
    }

    protected function string()
    {
        if ($this->buf->available() < 4) {
            return false;
        }
        list(,$l) = unpack('N', $this->buf->read(4));
        $this->size = $l;
        $this->stack[] = self::T_SIZE;
        return true;
    }

    protected function struct()
    {
        if ($this->buf->available() < 1) {
            return false;
        }
        $arr = unpack('c', $this->buf->read(1));
        $this->proto->writeByte($ftype = $arr[1]);
        if ($ftype == TType::STOP) {
            return true;
        }
        $this->stack[] = TType::STRUCT;
        $this->stack[] = $ftype;
        $this->stack[] = TType::I16;
        return true;
    }

    protected function list()
    {
        if ($this->buf->available() < 1 + 4) {
            return false;
        }
        $arr = unpack('ce/Ns', $this->buf->read(1 + 4));
        $this->proto->writeByte($etype = $arr['e']);
        $this->proto->writeI32($size = $arr['s']);
        for ($i=0; $i<$size; $i++) {
            $this->stack[] = $etype;
        }
        return true;
    }

    protected function map()
    {
        if ($this->buf->available() < 1 + 1 + 4) {
            return false;
        }
        $arr = unpack('ck/cv/Ns', $this->buf->read(1 + 1 + 4));
        $this->proto->writeByte($ktype = $arr['k']);
        $this->proto->writeByte($vtype = $arr['v']);
        $this->proto->writeI32($size = $arr['s']);
        for ($i=0; $i<$size; $i++) {
            $this->stack[] = $vtype;
            $this->stack[] = $ktype;
        }
        return true;
    }

    protected function size()
    {
        if ($this->buf->available() < $this->size) {
            return false;
        }
        $this->proto->writeString($this->buf->read($this->size));
        return true;
    }
}
