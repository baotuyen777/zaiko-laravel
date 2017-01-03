<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrdersController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth');
    }

    public function index(Request $request) {
        $limit = $request->input('limit') ? $request->input('limit') : 5;
        $userId = trim($request->input('userId'));
        $date = trim($request->input('date'));
        $orders = Order::orderBy('id', 'DESC');
        if ($userId) {
            $orders = $orders->where('user_id', '=', $userId);
        }
        if ($date) {
            $orders = $orders->where('date', '=', $date);
        }
        $orders = $orders->select('id', 'user_id', 'product', 'note', 'date', 'status')
                ->paginate($limit);
        return \Response::json([
                    'status' => true,
                    'data' => $this->transformCollection($orders)
                        ], 400);
    }

    public function show($id) {
        $order = Order::with(array('User' => function($query) {
                        $query->select('id', 'user_id', 'product', 'note', 'date', 'status');
                    }))->find($id);
        if (!$order) {
            return \Response::json([
                        'message' => 'Orders does not exist'
                            ], 404);
        }
//get previous joke id
        $previous = Order::where('id', '<', $order->id)->max('id');
//get next joke id
        $next = Order::where('id', '>', $order->id)->min('id');
        return \Response::json([
                    'previous_joke_id' => $previous,
                    'next_joke_id' => $next,
                    'data' => $this->transform($order)
                        ], 200);
    }

    public function store(Request $request) {
        if (!$request->date || !$request->product || !$request->note) {
            return \Response::json([
                        'status' => false,
                        'message' => 'Please provide {product},{note},{date}'
                            ], 422);
        }
        $userId = JWTAuth::parseToken()->toUser()->id;
        $data = $request->all();
        $data['user_id'] = $userId;
        $data['status'] = "pending";
        $order = Order::create($data);
        return \Response::json([
                    'status' => true,
                    'message' => 'Order Created Successfully',
                    'data' => $this->transform($order)
        ]);
    }

    public function update(Request $request, $id) {
        $userId = JWTAuth::parseToken()->toUser()->id;
        $order = Order::find($id);
        if (!in_array($request->status, array('pending', 'processing', 'unpaid', 'cancelled', 'completed'))) {
            return \Response::json([
                        'status' => false,
                        'message' => 'Status wrong'
                            ], 422);
        }
        if ($order != null) {
            $order->fill($request->all());
            $tmpOrder = $order->toArray();
            $order->save($tmpOrder);
            return \Response::json([
                        'status' => true,
                        'message' => 'Order Update Successfully',
            ]);
        }
        return \Response::json([
                    'status' => false,
                    'message' => 'Orders does not exist'
                        ], 422);
    }

    public function destroy($id) {
        if (!Order::destroy($id)) {
            return \Response::json([
                        'status' => false,
                        'message' => 'Orders does not exist'
                            ], 422);
        }
        return \Response::json([
                    'status' => false,
                    'message' => 'Order Delete Successfully',
        ]);
    }

    private function transformCollection($orders) {
        $ordersArray = $orders->toArray();

        return [
            'total' => $ordersArray['total'],
            'perPage' => intval($ordersArray['per_page']),
            'currentPage' => $ordersArray['current_page'],
            'lastPage' => $ordersArray['last_page'],
            'next_page_url' => $ordersArray['next_page_url'],
            'prev_page_url' => $ordersArray['prev_page_url'],
            'from' => $ordersArray['from'],
            'to' => $ordersArray['to'],
            'data' => array_map([$this, 'transform'], $ordersArray['data'])
        ];
    }

    private function transform($order) {
        return [
            'orderId' => $order['id'],
            'userId' => $order['user_id'],
            'orderProduct' => $order['product'],
            'orderNote' => $order['note'],
            'orderDate' => $order['date'],
            'OrderStatus' => $order['status']
        ];
    }

}
