<?php

namespace App\Core;

use App\Core\Interfaces\RestFilterable;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class Rest
{
    protected array $operatorMap = [
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
    ];
    protected array $restQuery = [
        'offset' => 0,
        'limit' => 20,
        'page' => 1,
        'appends' => [],
        'filters' => [],
    ];

    /**
     * Parses the provided GET Query array into a rest query array for indexing (returning a list).
     * @param array $query The GET Query array to parse.
     * @return array A rest query array.
     */
    public function parseIndexQuery(array $query): array
    {
        $restQuery = $this->restQuery;

        foreach ($query as $key => $value) {
            $key = ltrim($key, '?');

            // Sorting:
            if ($key == 'sort_by') {
                $restQuery['sort_by'] = [];
                foreach (explode(',', $value) as $sortDefinition) {
                    $sortEntry = explode(':', $sortDefinition);
                    $sortField = $sortEntry[0];
                    $sortDirection = $sortEntry[1] ?? 'asc';
                    $restQuery['sort_by'][$sortField] = $sortDirection;
                }
                continue;
            }

            // Paging:
            if ($key == 'offset') {
                $restQuery['offset'] = intval($value);
                continue;
            }
            if ($key == 'limit') {
                $restQuery['limit'] = intval($value);
                continue;
            }
            if ($key == 'page') {
                $restQuery['page'] = intval($value);
                continue;
            }
            if ($key == 'cursor') {
                $restQuery['cursor'] = $value ?: true;
                continue;
            }
            if ($key == 'appends') {
                $restQuery['appends'] = (array) $value ;
                continue;
            }

            // Filters:
            $restQuery['filters'][$key] = $value;
        }
        $this->restQuery = $restQuery;

        return $restQuery;
    }

    /**
     * Applies filter and sorting data from the provided rest query array onto the provided eloquent query.
     * @param Builder $queryBuilder The query builder to work with.
     * @param array $restQuery An array of parameters to parse.
     * @param array $defaultQuery An optional array of default parameters to use if not present in the rest parameters.
     * @return Builder The modified query builder.
     */
    public function applyRestQuery(Builder $queryBuilder, array $restQuery, array $defaultQuery = []): Builder
    {
        // Apply Filters:
        foreach ($this->mergeWithDefaults('filters', $restQuery, $defaultQuery) as $fieldEntry => $filterDefinition) {
            $this->appendFilterDefinition($queryBuilder, $fieldEntry, $filterDefinition);
        }

        // Apply Sorting:
        foreach ($this->mergeWithDefaults('sort_by', $restQuery, $defaultQuery) as $field => $direction) {
            $queryBuilder->orderBy($field, $direction);
        }

        return $queryBuilder;
    }

    /**
     * Appends the provided filter definition for querying on a field entry.
     * @param Builder $query The query builder to query on.
     * @param string $fieldEntry The name of the field to filter on, can take comma separated fields names to find a match on at least one.
     * @param mixed $filterDefinition The filter definition to parse, this can be a value string or an array of operators and values.
     * @return Builder The modified query builder.
     */
    public function appendFilterDefinition(Builder $query, string $fieldEntry, mixed $filterDefinition): Builder
    {
        if (!is_array($filterDefinition)) { // Converts direct equals rest queries to an array object to be consistent with other filter types.
            $filterDefinition = ['eq' => $filterDefinition];
        }
        foreach ($filterDefinition as $operator => $value) {
            if (is_a($query->getModel(), RestFilterable::class)) {
                $query = $query->getModel()->handleFilter($query, $fieldEntry, $operator, $value);
            } else {
                $query = $this->appendFilter($query, $fieldEntry, $operator, $value);
            }
        }

        return $query;
    }

    /**
     * Appends the provided filter for querying on a field entry.
     * @param Builder $query The query builder to query on.
     * @param string $fieldEntry The name of the field to filter on, can take comma separated fields names to find a match on at least one.
     * @param string $operator The operator to filter with.
     * @param mixed $value The value to filter by.
     * @return Builder The modified query builder.
     */
    public function appendFilter(Builder $query, string $fieldEntry, string $operator, mixed $value): Builder
    {
        // Parse Fields:
        $fields = explode(',', $fieldEntry);

        // Parse Operator:
        if (isset($this->operatorMap[$operator])) {
            $operator = $this->operatorMap[$operator];
        }
        if ($operator == 'like') {
            $value = '%' . $value . '%';
        }

        // Append Query:
        return $query->where(function ($query) use ($fields, $operator, $value) {
            $firstField = true;
            $lastArg = $query;
            foreach ($fields as $field) {

                // Query on Nested Fields:
                $nestedFields = explode('@', $field);
                if (count($nestedFields) > 1) {
                    $lastArg = $query->whereHas($nestedFields[0], function ($query) use ($nestedFields, $operator, $value) {
                        array_shift($nestedFields);
                        Rest::appendFilter($query, implode('@', $nestedFields), $operator, $value);
                    });
                } // Query on Field:
                else if ($operator == 'eq') {
                    $lastArg = $firstField ? $query->where($field, $value) : $lastArg->orWhere($field, $value);
                } else if ($operator == 'in') {
                    $lastArg = $firstField ? $query->whereIn($field, explode(',', $value)) : $lastArg->orWhereIn($field, explode(',', $value));
                } else {
                    $lastArg = $firstField ? $query->where($field, $operator, $value) : $lastArg->orWhere($field, $operator, $value);
                }
                $firstField = false;
            }
        });
    }

    /**
     * Merges a value within the two provided arrays based on the provided key.
     * @param string $key The key of the subarray to merge.
     * @param array $array The primary array that takes precedence.
     * @param array $defaults The defaults array, values present here are used if omitted from the primary array.
     * @return array The resulting merged array.
     */
    public function mergeWithDefaults(string $key, array $array, array $defaults): array
    {
        if (isset($array[$key]) && !isset($defaults[$key])) {
            return $array[$key];
        }
        if (!isset($array[$key]) && isset($defaults[$key])) {
            return $defaults[$key];
        }
        if (!isset($array[$key]) && !isset($defaults[$key])) {
            return [];
        }
        return array_merge($defaults[$key], $array[$key]);
    }

    /**
     * Applies pagination data from the provided rest query array onto the provided eloquent query.
     * @param Builder $queryBuilder The query builder to work with.
     * @param array $restQuery An array of parameters to parse.
     * @param array $defaultQuery An optional array of default parameters to use if not present in the rest parameters.
     * @return object|null A paginator object or null if none was created.
     */
    public function applyRestPagination(Builder $queryBuilder, array $restQuery, array $defaultQuery = []): ?object
    {
        $offset = $restQuery['offset'] ?? ($defaultQuery['offset'] ?? 0);
        $limit = $restQuery['limit'] ?? ($defaultQuery['limit'] ?? 0);
        $page = $restQuery['page'] ?? ($defaultQuery['page'] ?? 0);
        $cursor = $restQuery['cursor'] ?? ($defaultQuery['cursor'] ?? false);
        $paginator = null;

        if ($cursor) {
            $paginator = $queryBuilder->cursorPaginate($limit > 0 ? $limit : 20);
        } else if ($page > 0) {
            $paginator = $queryBuilder->paginate($limit > 0 ? $limit : 20);
        } else {
            if ($limit > 0) {
                $queryBuilder->limit($limit);
            }
            if ($offset > 0) {
                $queryBuilder->offset($offset);
            }
        }

        return $paginator;
    }

    /**
     * Generates an index info array from the provided data and query array.
     * @param array $query The query array used to filter the resource, see parseIndexQuery().
     * @param mixed $data The array or Collection of data to include.
     * @param mixed $paginator An optional paginator object used for returning paging information. Can be an AbstractPaginator or CursorPaginator.
     * @return array Returns an index array, this can be immediately returned as json or further modified.
     */
    public function indexResponse(array $query, mixed $data, mixed $paginator = null , $fillable = [], $appendable =[]): array
    {
        $limit = $query['limit'] ?? 0;
        $cursorPaginator = is_a($paginator, CursorPaginator::class) ? $paginator : null;
        $itemsTotal = $paginator && !$cursorPaginator ? $paginator->total() : count($data);

        $pagination = [
            'page_total' => $paginator && !$cursorPaginator ? $paginator->lastPage() : 0,
            'page_current' => $paginator && !$cursorPaginator ? $paginator->currentPage() : 0,
            'items_per_page' => $limit,
            'items_this_page' => min($limit, count($data)),
            'items_total' => $itemsTotal,
            'next_cursor' => $cursorPaginator && $cursorPaginator->nextCursor() ? $cursorPaginator->nextCursor()->encode() : '',
            'current_cursor' => $cursorPaginator && $cursorPaginator->cursor() ? $cursorPaginator->cursor()->encode() : '',
            'previous_cursor' => $cursorPaginator && $cursorPaginator->previousCursor() ? $cursorPaginator->previousCursor()->encode() : '',

            'limit' => intval($limit),
        ];
        $commands =[
            'attributes' =>$fillable,
            'operators' =>$this->operatorMap,
            'pagination' =>$this->restQuery,
            'appendable' =>$appendable
        ];


        return [
            'data' => $data,
            'pagination' => $pagination,
            'commands' => $commands,
        ];
    }

    /**
     * Handles a standard restul index call, this calls parseIndexQuery, applyRestQuery, applyRestPagination and indexResponse all in one method.
     * @param Request $request The http request object to read restful paramters from.
     * @param Builder $queryBuilder An eloquent query builder, this can be generated from models ex: Group::query().
     * @param array $defaultQuery An optional array of default restful filters to apply.
     * @return array Returns an index array, this can be immediately returned as json or further modified.
     */
    public function runIndexRequest(Request $request, Builder $queryBuilder, array $defaultQuery = []): array
    {
        $indexQuery = $this->parseIndexQuery($request->query());

        try {
            $queryBuilder = $this->applyRestQuery($queryBuilder, $indexQuery, $defaultQuery);
        } catch (QueryException $e) {
            abort(400, "Invalid filter or sort: " . $e->getPrevious()->getMessage());
        }

        try {
            $paginator = $this->applyRestPagination($queryBuilder, $indexQuery, $defaultQuery);
        } catch (QueryException $e) {
            abort(400, "Invalid pagination: " . $e->getPrevious()->getMessage());
        }
        if (count($this->restQuery['appends']) >0){
            $paginator->each(fn($itm) => $itm->setAppends($this->restQuery['appends']));
        }
        if ($paginator) {
            $data = $paginator->items();
        } else {
            $data = $queryBuilder->get();
        }




        return $this->indexResponse($indexQuery, $data, $paginator , $queryBuilder->getModel()->getFillable() , $queryBuilder->getModel()->appendable);
    }
}
