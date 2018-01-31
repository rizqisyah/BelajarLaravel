<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Post;
use DB;
class PostsController extends Controller
{

    //Untuk Load Di Conttoler Post harus login
    public function __construct()
    {
        $this->middleware('auth',['except' => ['index', 'show']]);
    }/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Menampilan data dri tabel post

        //$posts = Post::all();
        //$posts = Post::where('title', 'Post Two')->get();
        //$posts = DB::select('SELECT * From posts');
        //$posts = Post::orderBy('title','desc')->take(1)->get(); //Memngambil 1 post
        // $posts = Post::orderBy('title','desc')->get();
        $posts = Post::orderBy('created_at','time')->paginate(10); //Membuat PageNation
        return view('posts.index')->with('posts', $posts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Membuat halaman post
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Untuk Validasi Posting
        $this->validate($request, [
          'title' => 'required',
          'body' => 'required',
          'cover_image' => 'image|nullable|max:1999'
        ]);

        // Handle File upload
        if($request->hasFile('cover_image')){
          //Get filename with the extension
          $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
          //Get Just filename
          $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
          // Get Just ext
          $extension = $request->file('cover_image')->getClientOriginalExtension();
          // FIlename to Store
          $fileNameToStore = $filename.'_'.time().'.'.$extension;
          // Upload image
            $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);
        }else{
          $fileNameToStore = 'noimage.jpg';
        }
        // Create post

        $post = new Post;
        $post->title= $request->input('title'); //Menyimpan Title Ke Database
        $post->body= $request->input('body');   //Menyimpan Body Ke Database
        $post->user_id = auth()->user()->id; //Membuat Authentikasi User Id
        $post->cover_image = $fileNameToStore;
        $post->save();

        return redirect('/posts')->with('success', 'Post Created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //Membuat Show setelah post
        $post = Post::find($id);
        return view('posts.show')->with('post', $post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $post = Post::find($id);

        // Check for correct user Jika yang ngepost sesuai id make bisa edit
        if(auth()->user()->id !==$post->user_id){
          return redirect('/posts')->with('error', 'Unauthorized Page');
        }
        return view('posts.edit')->with('post', $post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Fungsi Valdasi Submit Di Edit
        $this->validate($request, [
          'title' => 'required',
          'body' => 'required'
        ]);

        // Handle File upload
        if($request->hasFile('cover_image')){
          //Get filename with the extension
          $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
          //Get Just filename
          $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
          // Get Just ext
          $extension = $request->file('cover_image')->getClientOriginalExtension();
          // FIlename to Store
          $fileNameToStore = $filename.'_'.time().'.'.$extension;
          // Upload image
            $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);
        }


        // Create post
        $post = Post::find($id);
        $post->title= $request->input('title'); //Menyimpan Title Ke Database
        $post->body= $request->input('body');   //Menyimpan Body Ke Database
        if($request->hasFile('cover_image')){
          $post->cover_image = $fileNameToStore;
        }
        $post->save();

        return redirect('/posts')->with('success', 'Post Updated'); //WIth = Session Jika
        //suuces
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
          $post = Post::find($id);
          //Check For Correct
          if(auth()->user()->id !==$post->user_id){
            return redirect('/posts')->with('error', 'Unauthorized Page');
          }
          if($post->cover_image != 'noimage.jpg'){
            // Delete image
            Storage::delete('public/cover_images/'.$post->cover_image);
          }

          $post->delete();
          return redirect('/posts')->with('success', 'Post Deleted'); //WIth = Session Jika
    }
}
