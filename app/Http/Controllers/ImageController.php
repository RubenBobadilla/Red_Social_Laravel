<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Image;
use App\Comment;
use App\Like;

class ImageController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function create() {
        return view('image.create');
    }

    public function save(Request $request) {

        // Validacion
        $validate = $this->validate($request, [
            'description' => 'required',
            'image_path' => 'required|image'
        ]);

        // Recoger datos 
        $image_path = $request->file('image_path');
        $description = $request->input('description');

        //  Asignar valores nuevo objeto
        $user = \Auth::user();  // traemos el id del modelo user
        $image = new Image();   // instanciamos el objeto que usamos arriba de app
        $image->user_id = $user->id;
        $image->description = $description;

        // Subir fichero
        if ($image_path) {
            $image_path_name = time() . $image_path->getClientOriginalName();
            Storage::disk('images')->put($image_path_name, File::get($image_path));
            $image->image_path = $image_path_name;
        }

        // Guardar en la DB
        $image->save();

        return redirect()->route('home')->with([
                    'message' => 'La foto ha sido subida correctamente'
        ]);
    }

    public function getImage($filename) {
        $file = Storage::disk('images')->get($filename);
        return new Response($file, 200);
    }

    public function detail($id) {
        $image = Image::find($id);

        return view('image.detail', [
            'image' => $image
        ]);
    }
    
    public function delete($id){
        $user = \Auth::user();        
        $image = Image::find($id);
        
        // Antes de elimiar la img, Se deben buscar los comments, likes y fecheros asociados para un eliminado exitoso!!
        $comments = Comment::where('image_id', $id)->get();
        $likes = Like::where('image_id', $id)->get();
        
        if($user && $image && $image->user->id == $user->id){
            
            // Eliminar Comentarios
            if($comments && count($comments)>=1){
                foreach ($comments as $comment){
                    $comment->delete();
                }
            }
            
            // Eliminar Likes
            if($likes && count($likes)>=1){
                foreach ($likes as $like){
                    $like->delete();
                }
            }
            
            // Eliminar ficheros de imagen
            Storage::disk('images')->delete($image->image_path);
            
            // Eliminar registro de la img
            $image->delete();

            $message = array('message' => 'La imagen se ha borrado correctamente.!!'); 
            
        }else{
            $message = array('message' => 'La imagen no se ha borrado.!!');            
        }

        return redirect()->route('home')->with($message);        
    }
    
    
    public function edit($id){
        $user = \Auth::user();
        $image = Image::find($id);
        
        if($user && $image && $user->id == $image->user->id){
            return view('image.edit', [
                'image' => $image
            ]);
        }else{
            return redirect()->route('home');
        }
    }
    
    public function update(Request $request) {
         // Validacion
        $validate = $this->validate($request, [
            'description' => 'required',
            'image_path' => 'image'
        ]);
        
        // Recoger datos
        $image_id = $request->input('image_id');
        $image_path = $request->file('image_path');
        $description = $request->input('description');
        
        // Conseguir objeto image
        $image = Image::find($image_id);
        $image->description = $description;
        
         // Subir fichero
        if ($image_path) {
            $image_path_name = time() . $image_path->getClientOriginalName();
            Storage::disk('images')->put($image_path_name, File::get($image_path));
            $image->image_path = $image_path_name;
        }
        
        // Actualizar registro
        $image->update();
        
        return redirect()->route('image.detail', ['id' => $image->id])->with([
                    'message' => 'Enhorabuenaa, Imagen actualizada con éxito!!'
            ]);        
    }
       
    

}
