<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Like;

class LikeController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
        $user = \Auth::user();
        $likes = Like::where('user_id', $user->id)->orderBy('id', 'desc')->paginate(5);
       
        return view('like.index',[
           'likes' => $likes 
        ]);
    }
    
    public function like($image_id){
        // Recoger datos del usuario y la imagen
        $user = \Auth::user();
        
        // Condicion para ver si  ya existe el like  y no duplicarlo
        $iisset_like = Like::where('user_id', $user->id)
                           ->where('image_id', $image_id)
                           // con get traemos todos los datos del objeto
                           //->get()  
                           ->count();
        //var_dump($iisset_like);
        //die();
        
        if($iisset_like == 0){
        
            $like = new Like();
            $like->user_id = $user->id;
            $like->image_id = (int)$image_id;

            // Guardar
            $like->save();
            //var_dump($like);  
            
            // como es por AJAX devolvemos un Objeto JSON:
            return response()->json([
               'like' => $like 
            ]);
            
        } else {
             // como es por AJAX devolvemos un Objeto JSON:
            return response()->json([
               'like' => "El like ya existe con tus datos!!!" 
            ]);            
        }
    }
    
    public function dislike($image_id){
        // Recoger datos del usuario y la imagen
        $user = \Auth::user();
        
        // Condicion para ver si  ya existe el like  y no duplicarlo
        $like = Like::where('user_id', $user->id)
                           ->where('image_id', $image_id)
                           // con get traemos todos los datos del objeto
                           //->get()  
                           ->first();
        //var_dump($iisset_like);
        //die();
        
        if($like){
        
            // Eliminar like
            $like->delete();
            //var_dump($like);  
            
            // como es por AJAX devolvemos un Objeto JSON:
            return response()->json([
               'like' => $like, 
                    'message' => 'Has dado Dislike correctamente'
            ]);
            
        } else {
             // como es por AJAX devolvemos un Objeto JSON:
            return response()->json([
               'like' => "El like No Existe!!!" 
            ]);            
        }
        
    }
    
   
    
}
