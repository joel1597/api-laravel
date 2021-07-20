<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function prueba(){
        return "este es un metodo";
    }

    public function registro(Request $request){

        $json_object = json_decode($request->input('json'));
        $json_array  = json_decode($request->input('json'), true);

        if( !empty($json_array) ){

            $data = [
                'status' => 'true',
                'code' => 200,
                'message' => 'la peticion se ah enviado',
                'data' => $json_array
            ];

            $validation = Validator::make($json_array,[
                'name' => 'required',
                'surname' => 'required',
                'role' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required'                            
            ]);    

            if( $validation->fails() ){
                $data = [
                    'status' => 'false',
                    'code' => 404,
                    'message' => 'faltan datos',
                    'erros' => $validation->errors()
                ];
            }else{
                
                $pwd = hash('sha256',$json_array['password']);

                $users = new User();
                $users->name = $json_array['name'];
                $users->surname = $json_array['surname'];
                $users->role = $json_array['role'];
                $users->email = $json_array['email'];
                $users->password = $pwd;
                $users->save();

                $data = [
                    'status' => 'true',
                    'code' => 200,
                    'message' => 'se han guardado los datos',
                    'user' => $users
                ];
                
            }

        }else{
            
            $data = [
                'status' => 'false',
                'code' => 404,
                'message' => 'habido un error al momento de enviar los datos'                
            ];
        }
        
        return response()->json($data);
    }

    public function login(Request $request){
        // \JwtAuth es un alias del archivo config/app.php/ que llama a la clase jwtAuth
        $jwt = new \JwtAuth();

        $json_object = json_decode($request->input('json'));
        $json_array = json_decode($request->input('json'), true);

        $validation = Validator::make($json_array,[            
            'email' => 'required|email',
            'password' => 'required'                            
        ]);    

        if( $validation->fails() ){
            $signup = [
                'status' => 'false',
                'code' => 404,
                'message' => 'el usuario no se ah podido logear',
                'erros' => $validation->errors()
            ];
        }else{

            $pwd = hash('sha256',$json_array['password']);
            $signup = $jwt->signup($json_array['email'], $pwd);

            if( !empty($json_array['getoken']) ){
                $signup = $jwt->signup($json_array['email'], $pwd, true);
            }

        }

        return response()->json($signup);
    }

    public function update(Request $request){
        $token = $request->header('Authorization');
        $jwt = new \JwtAuth();
        $checktoken = $jwt->checkToken($token);

        $json_object = json_decode($request->input('json'));
        $json_array = json_decode($request->input('json'), true);

        if( $checktoken==true && !empty($json_array)){            
                    
            $user = $jwt->checktoken($token,true);            

            $validation = Validator::make($json_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',                
                'email' => 'required|email|unique:users,'.$user->sub                
            ]);

            unset($json_array['id']);
            unset($json_array['role']);
            unset($json_array['password']);
            unset($json_array['getoken']);
            unset($json_array['created_at']);
            unset($json_array['remenber_token']);

            $user_update = User::where('id',$user->sub)->update($json_array);

            $data = [
                'status' => 'true',
                'code' => 200,
                'message' => 'se ah actualizado',
                'data' => $json_array
            ];

        }else{

            $data = [
                'status' => 'false',
                'code' => 404,
                'message' => 'peticion incompleta',
                'data' => ""
            ];
        }
        return response()->json($data);
    }

    public function upload(Request $request){

        $imagen = $request->file('image01');

        $validation = Validator::make( $request->all(), [
                    'image01' => 'require|image|mines:jpg,jpegm,png,gif'                
        ] );

        if( $imagen || $validation->fails() ){            

            $data = [
                'status' => 'false',
                'code' => 404,
                'message' => 'Error al subir imagen'                
            ];

        }else{

            $imagen_name = time().$imagen->getClientOriginalName();             
            \Storage::disk('users')->put($imagen_name, \File::get($imagen));

            $data = [
                'status' => 'true',
                'code' => 200,
                'image' => $imagen_name
            ];
                
        }
        
        return response()->json($data);    
    }

    public function getimagen($filename){
        $isset = \Storage::disk('users')->exists($filename);

        if ( $isset ) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file,200);
        }else{

            $data = [
                'status' => 'false',
                'code' => 404,
                'message' => 'no hay imagen'                
            ];

            return response()->json($data);
        }

    }


}
