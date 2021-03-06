<?php

namespace ScoutElastic\Builders;

use Laravel\Scout\Builder;

class FilterBuilder extends Builder
{
    public $wheres = [
        'must' => [],
        'must_not' => []
    ];

    public function __construct($model, $callback = null)
    {
        $this->model = $model;
        $this->callback = $callback;
    }

    /**
     * Supported operators are =, &gt;, &lt;, &gt;=, &lt;=, &lt;&gt;
     * @param string $field Field name
     * @param mixed $value Scalar value or an array
     * @return $this
     */
    public function where($field, $value)
    {
        $args = func_get_args();

        if (count($args) == 3) {
            list($field, $operator, $value) = $args;
        } else {
            $operator = '=';
        }

        switch ($operator) {
            case '=':
                $this->wheres['must'][] = ['term' => [$field => $value]];
                break;

            case '>':
                $this->wheres['must'][] = ['range' => [$field => ['gt' => $value]]];
                break;

            case '<';
                $this->wheres['must'][] = ['range' => [$field => ['lt' => $value]]];
                break;

            case '>=':
                $this->wheres['must'][] = ['range' => [$field => ['gte' => $value]]];
                break;

            case '<=':
                $this->wheres['must'][] = ['range' => [$field => ['lte' => $value]]];
                break;

            case '!=':
            case '<>':
                $this->wheres['must_not'][] = ['term' => [$field => $value]];
                break;
        }

        return $this;
    }

    public function whereIn($field, array $value)
    {
        $this->wheres['must'][] = ['terms' => [$field => $value]];

        return $this;
    }

    public function whereNotIn($field, array $value)
    {
        $this->wheres['must_not'][] = ['terms' => [$field => $value]];

        return $this;
    }

    public function whereBetween($field, array $value)
    {
        $this->wheres['must'][] = ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]];

        return $this;
    }

    public function whereNotBetween($field, array $value)
    {
        $this->wheres['must_not'][] = ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]];

        return $this;
    }

    public function whereExists($field)
    {
        $this->wheres['must'][] = ['exists' => ['field' => $field]];

        return $this;
    }

    public function whereNotExists($field)
    {
        $this->wheres['must_not'][] = [
            'exists' => [
                'field' => $field
            ]
        ];

        return $this;
    }

    public function whereRegexp($field, $value, $flags = 'ALL')
    {
        $this->wheres['must'][] = [
            'regexp' => [
                $field => [
                    'value' => $value,
                    'flags' => $flags
                ]
            ]
        ];

        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->orders[] = [$column => strtolower($direction) == 'asc' ? 'asc' : 'desc'];

        return $this;
    }

    public function explain()
    {
        return $this->engine()->explain($this);
    }

    public function profile()
    {
        return $this->engine()->profile($this);
    }

    public function buildPayload()
    {
        return $this->engine()->buildSearchQueryPayloadCollection($this);
    }
}