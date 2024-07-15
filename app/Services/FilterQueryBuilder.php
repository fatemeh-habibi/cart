<?php
namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * sample codes with this filter :
 * 
 * sort=[{"selector":"name","desc":false}]
 * filter=["name","contains","name"]
 * filter=[["name","contains","test"],"and",["sort_order","=",3]]
 * filter=["name","notcontains","test"]
 * filter=["roles.id","=",2]  with relations : must scoperelation in model has roles
 * operators : =,
 *             contain,
 *             notcontains,
 *             isblank,
 *             isnotblank,
 *             startswith
 *             endswith
 */
class FilterQueryBuilder
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
        $this->default_limit = config('settings.get_limit');
    }

    public function buildQuery($query,$alias = null ,$aliasSort = null) {
        //filters include search
        $filters = $this->request->query('filter');
        $this->buildFilterQuery($query,$filters,$alias);
        //sort
        $sort = $this->request->query('sort');
        $this->buildSortQuery($query,$sort,$aliasSort);
        $total = $query;
        $total_count = $total->count();
        //pagination
        $skip = $this->request->query('skip');
        $take = $this->request->query('take');
        $this->buildPaginationQuery($query,$skip,$take);

        $query->count = $total_count;
        return $query;
    }

    //Filter
    private function createRelationTree(string $relationKeys, $operator, $value,$alias = null) {
        if($alias) {
            $relationKeys = isset($alias[$relationKeys]) ? $alias[$relationKeys] : $relationKeys;
        }
        $relations = explode('.', $relationKeys);
        $lastKey = array_key_last($relations);
        $field = $relations[$lastKey];
        unset($relations[$lastKey]);
        $result = [];
        if(count($relations) > 0 ) {
            $result[$relations[count($relations)-1]] = [
                'field' => $field,
                'operator' => $operator,
                'value' => $value
            ];
            for($i=count($relations)-2; $i>-1; $i--)
            {
                $result[$relations[$i]] = $result;
                unset($result[$relations[$i+1]]);
            }
        } else {
            $result = [
                'field' => $field,
                'operator' => $operator,
                'value' => $value
            ];
        }
        return $result;
    }

    private function parseFilters($filters,$alias = null) {
        if(empty($filters)) {
            return [];
        }
        $result = [];
        if(is_array($filters[0])) {
            foreach ($filters as $filter) {
                if (empty($filter)) {
                    continue;
                }
                $operator = $filter[1];
                $value = $filter[2];
                $keys = $filter[0];
                $relationTree = $this->createRelationTree($keys, $operator, $value, $alias);
                array_push($result, $relationTree);
            }
        }else{
            $operator = $filters[1];
            $value = $filters[2];
            $keys = $filters[0];
            $relationTree = $this->createRelationTree($keys, $operator, $value, $alias);
            array_push($result, $relationTree);
        }
        return $result;
    }

    private function addFiltersToQuery($query, $filters,$where,$or_where) {
        if(count($filters) === 3) {
            if($where) {
                switch ($filters['operator']) {
                    case 'isblank':
                        return $query->whereNull($filters['field']);
                    case 'isnotblank':
                        return $query->whereNotNull($filters['field']);
                    case 'contains':
                        return $query->where($filters['field'], 'LIKE', '%' . $filters['value'] . '%');
                    case 'notcontains':
                        return $query->where($filters['field'], 'not like', '%' . $filters['value'] . '%');
                    case 'startswith':
                        return $query->where($filters['field'], 'LIKE', '%' . $filters['value']);
                    case 'endswith':
                        return $query->where($filters['field'], 'LIKE', $filters['value'] . '%');
                    case '=':
                        return $query->where($filters['field'], $filters['value']);
                    default:
                        return $query->where($filters['field'], $filters['operator'], $filters['value']);
                }
            }elseif($or_where){
                switch ($filters['operator']) {
                    case 'isblank':
                        return $query->orWhere($filters['field'],NULL);
                    case 'isnotblank':
                        return $query->orWhere($filters['field'],'!=',NULL);
                    case 'contains':
                        return $query->orWhere($filters['field'], 'LIKE', '%' . $filters['value'] . '%');
                    case 'notcontains':
                        return $query->orWhere($filters['field'], 'not like', '%' . $filters['value'] . '%');
                    case 'startswith':
                        return $query->orWhere($filters['field'], 'LIKE', '%' . $filters['value']);
                    case 'endswith':
                        return $query->orWhere($filters['field'], 'LIKE', $filters['value'] . '%');
                    case '=':
                        return $query->orWhere($filters['field'], $filters['value']);
                    default:
                        return $query->orWhere($filters['field'], $filters['operator'], $filters['value']);
                }
            }
        }
        $relation = array_key_first($filters);
        if($relation === "field") {
            return $query;
        }
        return $query->whereHas($relation, function(Builder $query) use($relation, $filters, $where, $or_where) {
            $this->addFiltersToQuery($query, $filters[$relation],$where,$or_where);

        });
    }

    private function buildFilterQuery($query,$filters,$alias = null) {
        if(!empty($filters)) {
            $where = Str::contains($filters, 'and');
            $or_where = Str::contains($filters, 'or');
            $filters = $where ? str_replace(',"and",', ',', $filters) : $filters;
            $filters = $or_where ? str_replace(',"or",', ',', $filters) : $filters;
            $filters = json_decode($filters);
            $filters = $this->parseFilters($filters, $alias);
            if(is_array($filters[0])){
                $where = 1;
            }
            foreach ($filters as $filter) {
                $query = $this->addFiltersToQuery($query, $filter, $where, $or_where);
            }
        }

        return $query;
    }

    //Sort
    private function buildSortQuery($query,$sort,$alias = null) {
        if(empty($sort)) {
            return $query->orderBy('id', 'desc');
        }
        $QueryArray = array();
        foreach (json_decode($sort , true) as $key => $val) {
             $QueryArray[$key] = $alias[$val['selector']];
             $QueryArray[$key][5] = ($val['desc'] == 1 ? 'DESC' : 'ASC');
         }
        return $query->sortable($QueryArray);
    }
    //pagination
    private function buildPaginationQuery($query,$skip = null,$take = null) {
        if(($skip || $skip == 0 ) && $take) {
            $query = $query->skip($skip)->take($take);
            return $query;
        }else{
            $query = $query->take($this->default_limit);
            return $query;
        }
    }
}
