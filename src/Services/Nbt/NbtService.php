<?php

namespace R0B0TB0SS\GamePlayerManager\Services\Nbt;

use Illuminate\Support\Facades\Log;

class NbtService
{
    const TAG_END = 0;
    const TAG_BYTE = 1;
    const TAG_SHORT = 2;
    const TAG_INT = 3;
    const TAG_LONG = 4;
    const TAG_FLOAT = 5;
    const TAG_DOUBLE = 6;
    const TAG_BYTE_ARRAY = 7;
    const TAG_STRING = 8;
    const TAG_LIST = 9;
    const TAG_COMPOUND = 10;
    const TAG_INT_ARRAY = 11;
    const TAG_LONG_ARRAY = 12;

    private $data;
    private $pos = 0;
    private $len = 0;

    /**
     * Parse raw NBT content (potentially GZipped)
     */
    public function parseString(string $content): array
    {
         // Check for GZip header (1f 8b)
        if (str_starts_with($content, "\x1f\x8b")) {
            $decoded = @gzdecode($content);
            if ($decoded === false) {
                Log::error("Failed to gzdecode NBT content");
                return [];
            }
            $this->data = $decoded;
        } else {
            // Uncompressed?
            $this->data = $content;
        }

        $this->len = strlen($this->data);
        $this->pos = 0;

        try {
            // Root tag is usually named compound
            // Read Type
            $type = $this->readByte();
            if ($type !== self::TAG_COMPOUND) {
                 // Some partial files or weird formats?
                 // But valid player.dat starts with Tag_Compound (0x0A)
                 Log::warning("NBT Root is not Compound: " . sprintf("0x%02X", $type));
                 return [];
            }
            // Read Name
            $name = $this->readString(); 
            
            // Read Content
            return $this->readCompound();

        } catch (\Exception $e) {
            Log::error("NBT Parse Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse a GZipped NBT file (file system path)
     */
    public function parseFile(string $path): array
    {
        if (!file_exists($path)) {
            Log::warning("NBT File not found: $path");
            return [];
        }

        $content = @file_get_contents($path);
        if ($content === false) {
             Log::error("Failed to read NBT file: $path");
             return [];
        }
        
        return $this->parseString($content);
    }

    private function readByte()
    {
        if ($this->pos >= $this->len) throw new \Exception("EOF at " . $this->pos);
        $v = ord($this->data[$this->pos]);
        $this->pos++;
        // Signed byte conversion if needed, but for Tags it is unsigned check usually
        return $v;
    }
    
    private function readSignedByte()
    {
        $v = $this->readByte();
        if ($v >= 128) $v -= 256;
        return $v;
    }

    private function readShort()
    {
        if ($this->pos + 2 > $this->len) throw new \Exception("EOF Short");
        $v = unpack('n', substr($this->data, $this->pos, 2)); // Big Endian unsigned short
        $this->pos += 2;
        $val = $v[1];
        if ($val >= 32768) $val -= 65536; // Signed
        return $val;
    }

    private function readInt()
    {
        if ($this->pos + 4 > $this->len) throw new \Exception("EOF Int");
        $v = unpack('N', substr($this->data, $this->pos, 4)); // Big Endian unsigned long (32bit)
        $this->pos += 4;
        $val = $v[1];
        // PHP integers are signed 64bit usually, so this unpack unsigned is fine unless > 2^31
        // Convert to signed 32-bit int logic
        if (PHP_INT_SIZE === 8) {
            if ($val >= 2147483648) $val -= 4294967296;
        }
        return $val;
    }

    private function readLong()
    {
         if ($this->pos + 8 > $this->len) throw new \Exception("EOF Long");
         $raw = substr($this->data, $this->pos, 8);
         $this->pos += 8;
         $v = unpack('J', $raw); // PHP 5.6+ Big Endian 64 bit
         return $v[1];
    }

    private function readFloat()
    {
         if ($this->pos + 4 > $this->len) throw new \Exception("EOF Float");
         $raw = substr($this->data, $this->pos, 4);
         $this->pos += 4;
         $v = unpack('G', $raw); // Big Endian float
         return $v[1];
    }

    private function readDouble()
    {
         if ($this->pos + 8 > $this->len) throw new \Exception("EOF Double");
         $raw = substr($this->data, $this->pos, 8);
         $this->pos += 8;
         $v = unpack('E', $raw); // Big Endian double
         return $v[1];
    }

    private function readString()
    {
        $len = $this->readShort(); // Unsigned short length? actually readShort gives signed...
        // Length is stored as unsigned short
        // Re-read strictly as unsigned short for length
        $this->pos -= 2;
        $v = unpack('n', substr($this->data, $this->pos, 2));
        $this->pos += 2;
        $len = $v[1];

        if ($len == 0) return "";
        if ($this->pos + $len > $this->len) throw new \Exception("EOF String ($len)");
        $str = substr($this->data, $this->pos, $len);
        $this->pos += $len;
        return $str;
    }

    private function readPayload($type)
    {
        switch ($type) {
            case self::TAG_BYTE: return $this->readSignedByte(); // Tag_Byte is signed
            case self::TAG_SHORT: return $this->readShort();
            case self::TAG_INT: return $this->readInt();
            case self::TAG_LONG: return $this->readLong();
            case self::TAG_FLOAT: return $this->readFloat();
            case self::TAG_DOUBLE: return $this->readDouble();
            case self::TAG_BYTE_ARRAY:
                $len = $this->readInt();
                $bytes = [];
                for($i=0; $i<$len; $i++) $bytes[] = $this->readSignedByte();
                return $bytes;
            case self::TAG_STRING: return $this->readString();
            case self::TAG_LIST:
                $subType = $this->readByte();
                $len = $this->readInt();
                $list = [];
                for($i=0; $i<$len; $i++) {
                    $list[] = $this->readPayload($subType);
                }
                return $list;
            case self::TAG_COMPOUND: return $this->readCompound();
            case self::TAG_INT_ARRAY:
                 $len = $this->readInt();
                 $ints = [];
                 for($i=0; $i<$len; $i++) $ints[] = $this->readInt();
                 return $ints;
            case self::TAG_LONG_ARRAY:
                 $len = $this->readInt();
                 $longs = [];
                 for($i=0; $i<$len; $i++) $longs[] = $this->readLong();
                 return $longs;
            default:
                throw new \Exception("Unknown Tag Type: $type");
        }
    }

    private function readCompound()
    {
        $data = [];
        while (true) {
            $type = $this->readByte();
            if ($type === self::TAG_END) break;
            
            $name = $this->readString();
            $value = $this->readPayload($type);
            
            $data[$name] = $value;
        }
        return $data;
    }
}
