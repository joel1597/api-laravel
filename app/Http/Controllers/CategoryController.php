<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

	public function __construct(){
		$this->middleware('apiauth', ['except' => ['index', 'show'] ]);
	}

    public function index(){
    	$category = Category::all();

    	if ( is_object($category) ) {    			
    		$data = [
	    		'code' => 200,
	    		'status' => 'true',
	    		'data' => $category
    		];
    	}    	

    	return response()->json($data);
    }

    public function show($id){

    	$category = Category::find($id);

    	if ( is_object($category) && isset($category->id) ) {
    		$data = [
	    		'code' => 200,
	    		'status' => 'true',
	    		'data' => $category
    		];
    	}else{
    		$data = [
	    		'code' => 404,
	    		'status' => 'false',
	    		'mensaje' => 'la categoria no existe'
    		];
    	}

    	return response()->json($data);
    }

    public function store(Request $request){
    	if( !empty($request) ){
    		$json_array = json_decode($request->input('json'), true);

    		if(is_array($json_array)){

    			$validation = Validator::make($json_array, [
    				'name' => 'required'
    			]);

    			if ( $validation->fails()) {
		    		$data = [
			    		'code' => 404,
			    		'status' => 'false',
			    		'mensaje' => $validation->errors()
	    			];
    			}else{

    				$category = new Category();
    				$category->name = $json_array['name'];
    				$category->save();

    				$data = [
			    		'code' => 200,
			    		'status' => 'true',
			    		'mensaje' => 'se han guardado los datos',
			    		'data' => $category
	    			];	
    			}
    		}

    	}

    	return response()->json($data);
    }

    public function update(Request $request, $id){

		$json_array = json_decode($request->input('json'), true);

		if ( !empty($id) && !empty($json_array) ) {

			$validation = Validator::make($json_array, [
				'name' => 'required'
			]);

			unset($json_array['id']);
			unset($json_array['created_at']);

			$category = Category::where('id', $id)->update($json_array);

			$data = [
				    	'code' => 200,
				    	'status' => 'true',
				    	'mensaje' => 'Se han actualizado los datos',
				    	'data' => $json_array
	    			];
			
		}else{
			$data = [
				    	'code' => 404,
				    	'status' => 'false',
				    	'mensaje' => 'Error al ejecutar el metodo',
				    	'data' => "null"
	    			];
		}

		return response()->json($data);
    }



}
