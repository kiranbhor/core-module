<?php

namespace Modules\Core\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Repositories\BaseRepository;

/**
 * Class EloquentCoreRepository
 *
 * @package Modules\Core\Repositories\Eloquent
 */
abstract class EloquentBaseRepository implements BaseRepository
{
    /**
     * @var \Illuminate\Database\Eloquent\Model An instance of the Eloquent Model
     */
    protected $model;

    /**
     * @param Model $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @inheritdoc
     */
    public function find($id)
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations')->find($id);
        }

        return $this->model->find($id);
    }

    /**
     * @inheritdoc
     */
    public function all()
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations')->orderBy('created_at', 'DESC')->get();
        }

        return $this->model->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @inheritdoc
     */
    public function allWithBuilder()
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations');
        }

        return $this->model;
    }

    /**
     * @inheritdoc
     */
    public function paginate($perPage = 15)
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations')->orderBy('created_at', 'DESC')->paginate($perPage);
        }

        return $this->model->orderBy('created_at', 'DESC')->paginate($perPage);
    }

    /**
     * @inheritdoc
     */
    public function create($data)
    {
        return $this->model->create($data);
    }

    /**
     * @inheritdoc
     */
    public function update($model, $data)
    {
        $model->update($data);

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function destroy($model)
    {
        return $model->delete();
    }

    /**
     * @inheritdoc
     */
    public function allTranslatedIn($lang)
    {
        return $this->model->whereHas('translations', function (Builder $q) use ($lang) {
            $q->where('locale', "$lang");
        })->with('translations')->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @inheritdoc
     */
    public function findBySlug($slug)
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->whereHas('translations', function (Builder $q) use ($slug) {
                $q->where('slug', $slug);
            })->with('translations')->first();
        }

        return $this->model->where('slug', $slug)->first();
    }

    /**
     * @inheritdoc
     */
    public function findByAttributes(array $attributes)
    {
        $query = $this->buildQueryByAttributes($attributes);

        return $query->first();
    }

    /**
     * @inheritdoc
     */
    public function getByAttributes(array $attributes, $orderBy = null, $sortOrder = 'asc')
    {
        $query = $this->buildQueryByAttributes($attributes, $orderBy, $sortOrder);

        return $query->get();
    }

    /**
     * Build Query to catch resources by an array of attributes and params
     * @param  array $attributes
     * @param  null|string $orderBy
     * @param  string $sortOrder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQueryByAttributes(array $attributes, $orderBy = null, $sortOrder = 'asc')
    {
        $query = $this->model->query();

        if (method_exists($this->model, 'translations')) {
            $query = $query->with('translations');
        }

        foreach ($attributes as $field => $value) {
            $query = $query->where($field, $value);
        }

        if (null !== $orderBy) {
            $query->orderBy($orderBy, $sortOrder);
        }

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function findByMany(array $ids)
    {
        $query = $this->model->query();

        if (method_exists($this->model, 'translations')) {
            $query = $query->with('translations');
        }

        return $query->whereIn("id", $ids)->get();
    }

    /**
     * @inheritdoc
     */
    public function clearCache()
    {
        return true;
    }

    /**
     * This method will return only sepecified columns from table satisfying the conditions
     * @param array $attributes
     * @param array $columns
     * @param null $orderBy
     * @param string $sortOrder
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getByAttributesWithColumns(array $attributes,array $columns=null, $orderBy = null, $sortOrder = 'asc')
    {
        $query = $this->buildQueryByAttributes($attributes, $orderBy, $sortOrder);

        return $query->get($columns);
    }

   

    /**
     * Get data as Name and Id for select
     * @param string $nameColumn
     * @param string $idColumn
     * @return mixed
     */
    public function getNameValue($nameColumn = 'name',$idColumn = 'id'){
        return $this->getQuery()->pluck($nameColumn, $idColumn);
    }

    

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWith(array $with,$order = 'desc', $sortOrder = 'created_at' ) {
	
        if ($with != null) {
            return $this->model->with($with)->orderBy($sortOrder, $order)->get();
        }
	
	

        return $this->model->orderBy($sortOrder, $order)->get();
    }

    /**
     * @param $columns
     * @return mixed
     */
    public function allWithColumns(array $columns,$orderBy = null, $sortOrder = 'asc')
    {
        return $this->model->orderBy($orderBy, $sortOrder)->get($columns);
    }

    /**
     * @param array $modelIds
     * @return int
     */
    public function deleteAll(array $modelIds){
        return $this->model->destroy($modelIds);
    }


    /** Inserts multiple records to table
     * @param array $record
     * @return mixed
     */
    public function insert(array $record){
        return $this->model->insert($record);
    }


    /**
     * Returns the query instance of the model   *
     * @return object
     */
    public function getQuery() {
        return $this->model->query();
    }
    /**
     * Get the model associated with repo
     */
    public function getModel(){
        return $this->model;
    }
    


    /**
     * Finds model and return with given association
     * @param $id
     * @param $with
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public function findWith($id,$with)
    {
        return $this->model->with($with)->find($id);
    }

    /**
     * @param array $attributes
     * @param $with
     * @param string $orderBy
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findManyByWith(array $attributes, $with, $orderBy = 'created_at',$direction = 'desc') {
        $query = $this->model->query()->with($with);

        foreach ($attributes as $field => $value) {
            $query = $query->where($field, $value);
        }

        $query->orderBy($orderBy, $direction);


        return $query->get();
    }

    /**
     * @param array $attributes
     * @param null $orderBy
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findManyByAttributes(array $attributes, $orderBy = null) {
        $query = $this->model->query();

        foreach ($attributes as $field => $value) {
            $query = $query->where($field, $value);
        }

        if ($orderBy != null) {
            $query->orderBy($orderBy, 'desc');
        }

        return $query->get();
    }

    /**
     * @param array $attributes
     * @param array $columns
     * @param null $orderBy
     * @param string $sortOrder
     * @return mixed
     */
    public function findByAttributesWithColumns(array $attributes,array $columns, $orderBy = null, $sortOrder = 'asc'){
        $query = $this->model->query();

        foreach ($attributes as $field => $value) {
            $query = $query->where($field, $value);
        }

        if ($orderBy != null) {
            $query->orderBy($orderBy, 'desc');
        }

        return $query->first();
    }

    /**
     * @param array $attributes
     * @param array $columns
     * @param null $orderBy
     * @param string $sortOrder
     * @return Model|null|static
     */
    public function findByAttributesWith(array $attributes,array $with, $orderBy = null, $sortOrder = 'asc'){
        $query = $this->model->query()->with($with);

        foreach ($attributes as $field => $value) {
            $query = $query->where($field, $value);
        }

        if ($orderBy != null) {
            $query->orderBy($orderBy, 'desc');
        }

        return $query->first();
    }

    public function getAllWith(array $with, array $attributes) {

        $query = $this->model->with($with)->get();

        foreach($query as $subQuery) {

                if ($subQuery->from_Date > $attributes[0] && $subQuery->to_Date < $attributes[1] ) {
                    var_dump($subQuery);
                    return'hgfh';
                }
            return'ghg';
        }



        return $query->get();

    }

    /**
     * @param array $attributes
     * @param array $columns
     * @param null $orderBy
     * @param string $sortOrder
     * @return Model|null|static
     */
    public function getByAttributesWith(array $attributes,array $with, $orderBy = null, $sortOrder = 'asc'){

        $query = $this->model->query()->with($with);

        foreach ($attributes as $field => $value) {
            $query = $query->where($field, $value);
        }

        if ($orderBy != null) {
            $query->orderBy($orderBy, 'desc');
        }

        return $query->get();
    }

    public function deleteById($id)
    {
        return $this->model->destroy([$id]);
    }
}
