<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class PostController extends Controller
{

    public function __construct(){
        $this->middleware('apiauth', ['except' => 
                        ['index', 'show', 'getImagen', 'getPostsByCategory', 'getPostsByUser'] 
                    ]);
    }

    public function index(){
    	$posts = Post::all()->load('category');

    	if ( is_object($posts) && !empty($posts) ) {
    		$data = [
    			'code' => 200,
    			'status' => 'success',
    			'data' => $posts
    		];	
    	}else{
    		$data = [
    			'code' => 404,
    			'status' => 'false',
    			'message' => 'No se han encontrado datos'
    		];
    	}

    	return response()->json($data, $data['code']);    	
    }

    public function show($id){
    	$posts = Post::find($id)->load('category');

    	if ( is_object($posts) && !empty($posts) ) {
    		$data = [
    			'code' => 200,
    			'status' => 'success',
    			'data' => $posts
    		];
    	}else{
    		$data = [
    			'code' => 404,
    			'status' => 'false',
    			'message' => 'No se han encontrado datos'
    		];
    	}

    	return response()->json($data, $data['code']);
    }

    public function store(Request $request){

        $json_array = json_decode( $request->input('json'), true );

        if ( is_array($json_array) && !empty($json_array) ) {

            $user = $this->getIdentity($request);

            $validation = Validator::make($json_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);

            if ( $validation->fails() ) {

                $data = [
                    'code' => 404,
                    'status' => 'false',
                    'mensaje' => 'se requieren campos',
                    'error' => $validation->errors()
                ];

            }else{

                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $json_array['category_id'];
                $post->title = $json_array['title'];
                $post->content = $json_array['content'];
                $post->image = $json_array['image'];
                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'true',
                    'mensaje' => 'se han actualizado los datos',
                    'post' => $json_array
                ];

            }
        }

        return response()->json($data);
    }

    public function update(Request $request, $id){

        $json_array = json_decode( $request->input('json'), true );

        if ( is_array($json_array) && !empty($json_array) && !empty($id) ) {
            
            $validation = Validator::make( $json_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'                
            ]);

            if ( $validation->fails() ) {

                $data['error'] = $validation->errors();
                return response()->json($data, 404); 
            }

                unset($json_array['id']);
                unset($json_array['user_id']);
                unset($json_array['created_at']);
                unset($json_array['users']);

                $user = $this->getIdentity($request);

                $post = Post::where('id',$id)->where('user_id', $user->sub)->first();

                if( !empty($post) && is_object($post) ){

                    $post->update($json_array);

                    $data = [
                        'status' => 'true',
                        'code' => 200,
                        'mensaje' => 'Se han actualizado los campos',
                        'post' => $post,
                        'data' => $json_array
                    ];
                }else{
                     $data = [
                        'status' => 'false',
                        'code' => 404,
                        'mensaje' => 'no se ah podido realizar la accion',                        
                    ];
                }
                //$post = Post::where('id', $id)->updateOrCreate($json_array);                
        }else{
            
            $data = [
                'status' => 'false',
                'code' => 404,
                'mensaje' => 'No ha llegado los datos correctamente',
                'data' => 'null'
            ];            
        }


        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request){

        if ( !empty($id) ) {
            
            $user = $this->getIdentity($request);

            $post = Post::where('id', $id)
                        ->where('user_id', $user->sub)
                        ->first();

            if ( !empty($post) && is_object($post) ) {
                
                $post->delete();

                $data = [
                    'status' => 'true',
                    'code' => 200,
                    'mensaje' => 'se ah eliminado el POST',
                    'data' => $post
                ];

            }else{

                $data = [
                    'status' => 'false',
                    'code' => 404,
                    'mensaje' => 'No se ah podido elimar el registro'                    
                ]; 

            }                        

        }

        return response()->json($data, $data['code']);

    }

    private function getIdentity($request){

        $token = $request->header('Authorization');
        $jwt = new \JwtAuth();
        $user = $jwt->checkToken($token, true);

        return $user;
    }


    public function upload(Request $request){

        $image = $request->file('file01');

        $validation = Validator::make($request->all(),[
            'file01' => 'required|image:jpg,jpeg,png,gif'
        ]);

        if ( !$image || $validation->fails() ) {
            
            $data = [
                    'status' => 'false',
                    'code' => 404,
                    'mensaje' => 'Error al subir la imagen'                    
                ]; 

        }else{

            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = [
                    'status' => 'true',
                    'code' => 200,
                    'mensaje' => $image_name                   
                ]; 

        }

        return response()->json($data, $data['code']);
    }

    public function getImagen($filename){

        $isset = \Storage::disk('images')->exists($filename);

        if ( $isset ) {
            $file = \Storage::disk('images')->get($filename);
            return new Response($file,200);

        }else{
            $data = [
                    'status' => 'false',
                    'code' => 404,
                    'mensaje' => 'Error al subir la imagen'                    
                ];
        }

        return response()->json($data, $data['code']);
    }

    public function getPostsByCategory($id){
        $post = Post::where('category_id', $id)->get();

        return response()->json([
                    'status' => 'success',
                    'posts' => $post
                ], 200);
    }

    public function getPostsByUser($id){
        $post = Post::where('user_id', $id)->get();

        return response()->json([
                    'status' => 'success',
                    'posts' => $post
                ], 200);

    }

}
