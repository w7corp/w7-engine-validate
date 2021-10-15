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

namespace W7\Validate\Providers\Laravel;

use Illuminate\Database\ConnectionResolverInterface;
use Itwmw\Validation\Support\Interfaces\PresenceVerifierInterface;

class PresenceVerifier implements PresenceVerifierInterface
{
    protected $db;

    protected $connectionResolver;

    public function __construct(ConnectionResolverInterface $db)
    {
        $this->connectionResolver = $db;
    }

    public function table(string $table): PresenceVerifierInterface
    {
        $this->db = $this->connectionResolver->table($table)->useWritePdo();
        return $this;
    }

    public function setConnection($connection)
    {
        $this->connectionResolver->connection($connection);
        return $this;
    }

    public function where(string $column, $operator = null, $value = null, string $boolean = 'and'): PresenceVerifierInterface
    {
        $this->db->where($column, $operator, $value, $boolean);
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
