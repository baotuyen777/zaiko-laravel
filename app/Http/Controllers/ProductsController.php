<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use Tymon\JWTAuth\Facades\JWTAuth;


class ProductsController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth');
    }

    public function index(Request $request) {
        $limit = $request->input('limit') ? $request->input('limit') : 5;
        $search = $request->input('search');

        if ($search) {
            $products = Product::orderBy('id', 'DESC')->where('name', 'LIKE', "%$search%")->select('id', 'name', 'price', 'img', 'description')->paginate($limit);
            $products->appends(array('search' => $search, 'limit' => $limit));
        } else {
            $products = Product::orderBy('id', 'DESC')->select('id', 'name', 'price', 'img', 'description')->paginate($limit);
        }
        return \Response::json([
                    'status' => true,
                    'data' => $this->transformCollection($products)
                        ], 400);
    }

    public function show($id) {
        $product = Product::with(array('User' => function($query) {
                        $query->select('id', 'name');
                    }))->find($id);
        if (!$product) {
            return \Response::json([
                        'message' => 'Products does not exist'
                            ], 404);
        }
        //get previous joke id
        $previous = Product::where('id', '<', $product->id)->max('id');
        //get next joke id
        $next = Product::where('id', '>', $product->id)->min('id');
        return \Response::json([
                    'previous_joke_id' => $previous,
                    'next_joke_id' => $next,
                    'data' => $this->transform($product)
                        ], 200);
    }

    public function store(Request $request) {
        if (!$request->name || !$request->price || !$request->category || !$request->description || !$request->img) {
            return \Response::json([
                        'status' => false,
                        'message' => 'Please provide {name},{price},{category},{description},{img}'
                            ], 422);
        }
        $userId = JWTAuth::parseToken()->toUser()->id;
        $data = $request->all();
        $data['user_id'] = $userId;
        $product = Product::create($data);
        return \Response::json([
                    'status' => true,
                    'message' => 'Product Created Successfully',
                    'data' => $this->transform($product)
        ]);
    }

    public function update(Request $request, $id) {
        $userId = JWTAuth::parseToken()->toUser()->id;
        $product = Product::find($id);

        if ($product != null) {
            $product->fill($request->all());
            $tmpProduct = $product->toArray();
            $product->save($tmpProduct);
            return \Response::json([
                        'status' => true,
                        'message' => 'Product Update Successfully',
            ]);
        }
        return \Response::json([
                    'status' => false,
                    'message' => 'Products does not exist'
                        ], 422);
    }

    public function destroy($id) {
        if (!Product::destroy($id)) {
            return \Response::json([
                        'status' => false,
                        'message' => 'Products does not exist'
                            ], 422);
        }
        return \Response::json([
                    'status' => false,
                    'message' => 'Product Delete Successfully',
        ]);
    }

    private function transformCollection($products) {
        $productsArray = $products->toArray();

        return [
            'total' => $productsArray['total'],
            'perPage' => intval($productsArray['per_page']),
            'currentPage' => $productsArray['current_page'],
            'lastPage' => $productsArray['last_page'],
            'next_page_url' => $productsArray['next_page_url'],
            'prev_page_url' => $productsArray['prev_page_url'],
            'from' => $productsArray['from'],
            'to' => $productsArray['to'],
            'data' => array_map([$this, 'transform'], $productsArray['data'])
        ];
    }

    private function transform($product) {
        return [
            'productId' => $product['id'],
            'productName' => $product['name'],
            'productPrice' => $product['price'],
            'productDescription' => $product['description'],
            'ProductImg' => $product['img']
        ];
    }

}
