<?php


namespace App\Repositories;


use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

abstract class EloquentRepository
{

    /**
     * Query Builder Class.
     *
     * @var
     */
    protected $query;

    /**
     * Model fields that are sortable. The
     * first given field will be the
     * default sort
     *
     * @var
     */
    protected $sortableFields = [];

    /**
     * Model fields that we can perform searches on, Accepts
     *  a string to search relationship table fields:
     * 'parent_table_name.child_table_name.child_table_column'
     * @var array
     */
    protected $searchableFields = [];

    /**
     * Holds the parameters used to fetch the results.
     *
     * @var
     */
    protected $queryParameters;

    /**
     * Wrapper for method on Query Builder that lets
     * us eager load our database relationships.
     *
     * @return $this
     */
    public function with()
    {
        $arg = func_get_args()[0];
        if (is_string($arg) || is_array($arg)) $this->query->with($arg);
        return $this;
    }

    /**
     * Apply a sort to the Purchase Requests
     *
     * @param null $sort
     * @param null $order
     * @return $this
     */
    public function sortOn($sort = null, $order = null)
    {
        $this->{'order'} = ($order === 'desc') ? 'desc' : 'asc';
        $this->{'sort'} = in_array($sort, $this->sortableFields) ? $sort : $this->sortableFields[0];
        $this->query->orderBy($this->sort, $this->order);
        return $this;
    }

    /**
     * Wrapper - Used to only select certain
     * fields
     *
     * @param $fields
     * @return $this
     */
    public function select($fields)
    {
        $this->query->select($fields);
        return $this;
    }

    /**
     * Wrapper - just in case we don't
     * want to paginate and just retrieve it
     * in one go
     *
     * @return mixed
     */
    public function get()
    {
        $data = $this->query->get();
        $this->addPropertiesToResults($data);
        return $data;
    }

    /**
     * Wrapper - for having() on Query Builder. having() can be used
     * on aggregates (SUM, COUNT, etc...) -- WHERE cannot be used.
     *
     * @param $column
     * @param $operator
     * @param $value
     * @return $this
     */
    public function having($column, $operator, $value)
    {
        $this->query->having($column, $operator, $value);
        return $this;
    }

    /**
     * Search function that searches a target table's fields
     * as well as any directly related tables
     *
     * @param $term
     * @return $this
     */
    public function searchFor($term, $searchFieldsArray = null)
    {
        if ($term) {
            $this->{'search'} = $term;
            // If one-time search fields defined, use them
            if($searchFieldsArray) $this->searchableFields = $searchFieldsArray;
            // perform search
            $this->query->where(function ($query) use ($term) {
                foreach ($this->searchableFields as $index => $field) {
                    $fieldsArray = explode('.', $field);

                    if(count($fieldsArray) === 1 ) {
                        $this->searchQueryDirect($query, $fieldsArray[0], $term, $index);
                    } else {
                        $this->searchQueryRelated($query, $fieldsArray, $term, $index);
                    }
                }
                return $query;

            });
        }
        return $this;
    }

    /**
     * Performs a search query on a field directly related
     * to the Model
     *
     * @param Builder $query
     * @param $field
     * @param $term
     * @param $index
     * @return mixed
     */
    protected function searchQueryDirect(Builder $query, $field, $term, $index)
    {
        $funcName = $index === 0 ? 'where' : 'orWhere';
        return call_user_func([$query, $funcName], $field, 'LIKE', '%' . $term . '%');
    }

    /**
     * Performs search on field that is on a related table. As
     * defined in the $searchableFields[] on the model.
     *
     * @param Builder $query
     * @param $fieldArray
     * @param $term
     * @param $index
     * @return mixed
     */
    protected function searchQueryRelated(Builder $query, $fieldArray, $term, $index)
    {
        $funcName = $index === 0 ? 'whereExists' : 'orWhereExists';
        $callback = function ($q) use ($fieldArray, $term) {
            $q->select(DB::raw(1))
              ->from(($fieldArray[2]))
              ->whereRaw($fieldArray[0] . '.' . $fieldArray[1] . '=' . $fieldArray[2] . '.id')
              ->where($fieldArray[3], 'LIKE', '%' . $term . '%');
        };
        return call_user_func([$query, $funcName], $callback);
    }


    /**
     * Attaches this object's properties
     * to the results object that is
     * returned to Client
     *
     * @param $object
     */
    protected function addPropertiesToResults($object)
    {
        // Whether our results are paginated or just a collection (ie. using get())
        if ($object instanceof LengthAwarePaginator || $object instanceof Collection) {
            // Transfer object properties onto it
            foreach (get_object_vars($this) as $key => $value) {
                if (!($value instanceof LengthAwarePaginator) && !($value instanceof Builder)) {
                    if ($key !== 'sortableFields' && $key !== 'searchableFields' && $key !== 'queryParameters') $this->queryParameters[$key] = $value;
                }
            }
            $object['query_parameters'] = $this->queryParameters;
        }
    }

    /**
     * Finally: Fetch Results and paginate it
     * by set number of items per Page
     * (untested)
     * @param $itemsPerPage
     * @return $this
     */
    public function paginate($itemsPerPage = 20)
    {
        // Set paginated property to hold our paginated results
        $paginatedObject = $this->{'paginated'} = $this->query->paginate($itemsPerPage);
        // add our custom properties
        $this->addPropertiesToResults($paginatedObject);
        return $this->paginated;
    }

    /**
     * Wrapper - Method on Query Builder that removes
     * duplicates from retrieved results set.
     *
     * @return $this
     */
    public function distinct()
    {
        $this->query->distinct();
        return $this;
    }

    /**
     * Wrapper - limit number of results for the
     * query
     *
     * @param $limit
     * @return $this
     */
    public function take($limit)
    {
        $this->query->take($limit);
        return $this;
    }

    /**
     * Just a get() wrapper for the Query Builder. This is
     * used for testing because we don't need to know the
     * Query Properties used (for client).
     *
     * @return mixed
     */
    public function getWithoutQueryProperties()
    {
        return $this->query->get();
    }
}