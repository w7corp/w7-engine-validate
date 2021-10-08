<?php

/**
 * WeEngine System
 *
 * (c) We7Team 2021 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Validate\Providers\Think;

use think\facade\Db;
use Itwmw\Validation\Support\Interfaces\PresenceVerifierInterface;

class PresenceVerifier implements PresenceVerifierInterface
{
    protected $db;

    public function __construct()
    {
        $this->db = Db::newQuery();
    }

    public function setConnection(?string $connection)
    {
        if (!empty($connection)) {
            $this->db->connect($connection);
        }
    }

    public function table(string $table): PresenceVerifierInterface
    {
        $this->db->table($table);
        return $this;
    }

    public function where(string $column, $operator = null, $value = null, string $boolean = 'and'): PresenceVerifierInterface
    {
        switch (strtolower($boolean)) {
            case 'and':
                $this->db->where($column, $operator, $value);
                break;
            case 'or':
                $this->db->whereOr($column, $operator, $value);
                break;
            case 'xor':
                $this->db->whereXor($column, $operator, $value);
                break;
            default:
                throw new \RuntimeException('操作错误');
        }

        return $this;
    }

    public function whereIn(string $column, $values, string $boolean = 'and'): PresenceVerifierInterface
    {
        $this->db->whereIn($column, $values, $boolean);
        return $this;
    }

    public function count(string $columns = '*'): int
    {
        return $this->db->count($columns);
    }
}
