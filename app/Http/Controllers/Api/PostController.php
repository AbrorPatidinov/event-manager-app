<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Resources\PostResource;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    /**
     * Display a listing of the posts.
     */
    public function index() : AnonymousResourceCollection
    {
        $posts = Post::all();
        return PostResource::collection($posts);
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request) : PostResource
    {
        // Validate the incoming request data
        $validated = $request->validate ([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
        // Create the post
        $post = Post::create($validated);

        return new PostResource($post);
    }

    /**
     * Display the specified post.
     */
    public function show($id) : \Illuminate\Http\JsonResponse|PostResource
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        return new PostResource($post);
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, $id) : \Illuminate\Http\JsonResponse|PostResource
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found.'
            ], JsonResponse::HTTP_NOT_FOUND); 
        }
        
        // Validate the incoming request data
        $validated = $request->validate([
        'title' => 'sometimes|required|string|max:255',
        'body' => 'sometimes|required|string',
        ]);

        // Update the post
        $post->update($validated);

        return new PostResource($post);
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy($id) : \Illuminate\Http\JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully.'
        ], JsonResponse::HTTP_NO_CONTENT);
    }
}
