<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use DispatchesJobs;
    use ValidatesRequests;
    use AuthorizesRequests {
        resourceAbilityMap as protected resourceAbilityMapTrait;
    }

    protected $model = null;
    protected $relationships = [];

    public function __construct()
    {
        if ($this->model) {
            // $this->authorizeResource($this->model);
        }
    }

    /**
     * Get the map of resource methods to ability names.
     *
     * @return array
     */
    protected function resourceAbilityMap()
    {
        // Map the "index" ability to the "list" function in our policies
        return array_merge($this->resourceAbilityMapTrait(), ['index' => 'list']);
    }

    public function loadRelationships($model)
    {
        // dd($this->relationships);
        $relationships = [];
        
        if (request()->has('load')) {
            $relationships = request()->load;

            if (!is_array($relationships)) {
                $relationships = [$relationships];
            }
        }

        $relationships = array_unique(array_merge($relationships, $this->relationships));
        // dd($relationships);
        // dd(instanceof $model);
        if ($model instanceof Model) {
            // dd("model");
            $model->load($relationships);
        } else{
            $model->with($relationships);
        }
    }
}