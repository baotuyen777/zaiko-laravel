<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CheckArticlesRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //thiet lap cac rule cho form
            'name'=>'required|min:6',
            'author'=>'required',
            'created_at'=>'required|date'
        ];
//        return [
//            //thiết lập các rule cho form
//            'name' => 'required|min:6', // field name bắt buộc nhập và phải có tổi thiểu 6 ký tự
//            'author' => 'required', // field author bắt buộc nhập
//            'created_at' => 'required|date' // field created_at bắt buộc nhập và phải có định dạng kiểu date
//        ];
    }
}
