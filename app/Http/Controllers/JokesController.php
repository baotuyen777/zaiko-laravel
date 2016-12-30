<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Joke;
use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;

//use Illuminate\Auth\Access\Response;
class JokesController extends Controller {
    public function __construct() {
        $this->middleware('jwt.auth');
//        $payload = JWTAuth::parseToken()->getPayload();
        
    }

    public function index(Request $request) {
        $limit = $request->input('limit') ? $request->input('limit') : 5;
        $search = $request->input('search');

        if ($search) {
            $jokes = Joke::orderBy('id', 'DESC')->where('body', 'LIKE', "%$search%")->with(
                            array('User' => function($query) {
                                    $query->select('id', 'name');
                                }
                    ))->select('id', 'body', 'user_id')->paginate($limit);
            $jokes->appends(array('search' => $search, 'limit' => $limit));
        } else {
            $jokes = Joke::orderBy('id', 'DESC')->with(
                            array('User' => function($query) {
                                    $query->select('id', 'name');
                                })
                    )->select('id', 'body', 'user_id')->paginate($limit);
        }
        return \Response::json([
                    'data' => $this->transformCollection($jokes)
                        ], 400);
    }

    public function show($id) {
        $joke = Joke::with(array('User' => function($query) {
                        $query->select('id', 'name');
                    }))->find($id);
        if (!$joke) {
            return \Response::json([
                        'error' => ['message' => 'Jokes does not exist']
                            ], 404);
        }
        //get previous joke id
        $previous = Joke::where('id', '<', $joke->id)->max('id');
        //get next joke id
        $next = Joke::where('id', '>', $joke->id)->min('id');
        return \Response::json([
                    'previous_joke_id' => $previous,
                    'next_joke_id' => $next,
                    'data' => $this->transform($joke)
                        ], 200);
    }

    public function store(Request $request) {
        $userId=JWTAuth::parseToken()->toUser()->id;
        if (!$request->body) {
            return \Response::json([
                        'error' => ['message' => 'Please Provide both body']
                            ], 422);
        }
        $data=$request->all();
       
        $data['user_id']=$userId;
        $joke = Joke::create($data);
        return \Response::json([
                    'message' => 'Joke Created Successfully',
                    'data' => $this->transform($joke)
        ]);
    }

    public function update(Request $request, $id) {
        if (!$request->body or ! $request->user_id) {
            return \Response::json([
                        'error' => ['message' => 'Please provide both body and user_id']
                            ], 422);
        }
        $joke = Joke::find($id);
        $joke->body = $request->body;
        $joke->user_id = $request->user_id;
        $joke->save();
    }

    public function destroy($id) {
        if (!Joke::destroy($id)) {
            return \Response::json([
                        'error' => ['message' => 'id not found']
                            ], 422);
        }
        return \Response::json([
                    'message' => 'Joke Created Successfully',
                    'status' => true
        ]);
    }

    private function transformCollection($jokes) {
        $jokesArray = $jokes->toArray();

        return [
            'total' => $jokesArray['total'],
            'per_page' => intval($jokesArray['per_page']),
            'current_page' => $jokesArray['current_page'],
            'last_page' => $jokesArray['last_page'],
            'next_page_url' => $jokesArray['next_page_url'],
            'prev_page_url' => $jokesArray['prev_page_url'],
            'from' => $jokesArray['from'],
            'to' => $jokesArray['to'],
            'data' => array_map([$this, 'transform'], $jokesArray['data'])
        ];
    }

    private function transform($joke) {
        return [
            'joke_id' => $joke['id'],
            'joke' => $joke['body'],
            'submitted_by' => $joke['user']['name']
        ];
    }

}
