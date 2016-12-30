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
                    'status' => true,
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
        if (!$request->body) {
            return \Response::json([
                        'status' => false,
                        'error' => ['message' => 'Please Provide both body']
                            ], 422);
        }
        $userId = JWTAuth::parseToken()->toUser()->id;
        $data = $request->all();
        $data['user_id'] = $userId;
        $joke = Joke::create($data);
        return \Response::json([
                    'status' => true,
                    'message' => 'Joke Created Successfully',
                    'data' => $this->transform($joke)
        ]);
    }

    public function update(Request $request, $id) {
        if (!$request->body) {
            return \Response::json([
                        'status' => false,
                        'error' => ['message' => 'Please provide both body and user_id']
                            ], 422);
        }
        $userId = JWTAuth::parseToken()->toUser()->id;
        $joke = Joke::find($id);
        $joke->body = $request->body;
        $joke->user_id = $userId;
        $joke->save();
    }

    public function destroy($id) {
        if (!Joke::destroy($id)) {
            return \Response::json([
                        'status' => false,
                        'error' => ['message' => 'id not found']
                            ], 422);
        }
        return \Response::json([
                    'status' => false,
                    'message' => 'Joke Created Successfully',
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
