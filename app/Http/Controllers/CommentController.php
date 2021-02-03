<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Comment;

class CommentController extends Controller
{
     public function __construct(){
        $this->middleware('auth');
    }
    
    public function save(Request $request) {
        
        // Validacion
        $validate = $this->validate($request, [
            'image_id' => 'integer|required',
            'content' => 'string|required'
        ]);
        
        // Recoger datos
        $user = \Auth::user();
        $image_id = $request->input('image_id');
        $conten = $request->input('content');
        
        // Asigno los valores de mi nuevo objeto
        $comment = new Comment();
        $comment->user_id = $user->id;
        $comment->image_id = $image_id;
        $comment->content = $conten;
        
        // Guardar en db
        $comment->save();
        
        // Reedireccion
        return redirect()->route('image.detail',['id' => $image_id])
                         ->with([
                             'message' => 'Has pulicado tu comentario correctamente!!'
                         ]);        
       
    }
    
    public function delete($id) {
        // Conseguir datos del usuario Logueado
        $user = \Auth::user();
        
        // Conseguir objeto del comentario
        $comment = Comment::find($id);
        //var_dump($comment);
        //die();
        // Comprobar si soy el dueño del comentario o la publicacion img
        if($user && ($comment->user_id == $user->id || $comment->image->user_id == $user->id)){
            $comment->delete();
            
            // Reedireccion
            return redirect()->route('image.detail',['id' => $comment->image_id])
                         ->with([
                             'message' => 'Comentario eliminado correctamente'
                         ]);  
        }else{
            // Reedireccion
            return redirect()->route('image.detail',['id' => $comment->image_id])
                         ->with([
                             'message' => 'EL COMENTARIO NO PUDO SER ELIMINADO'
                         ]); 
        }
    }
}
