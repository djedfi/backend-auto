<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Style;

class StyleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        try
        {
            if(count(Style::all()) > 0)
            {
                return \response()->json(['res'=>true,'data'=>Style::all()],200);
            }
            else
            {
                return \response()->json(['res'=>false,'data'=>[],'message'=>config('constants.msg_empty')],200);
            }
        }
        catch(\Exception $e)
        {
            return \response()->json(['res'=>false,'data'=>[],'message'=>config('constants.msg_error_srv')],200);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $inputs_f       = array();
        $rules = [
            'txt_name_st'     =>       'required|string|max:45'
        ];

        try
        {
            $inputs              =   $request->all();
            $obj_validacion     = Validator::make($inputs,$rules);

            if(!$obj_validacion->fails())
            {
                $inputs_f['name']       =   $inputs['txt_name_st'];
                $style       =   Style::create($inputs_f);

                if($style->id > 0)
                {
                    return \response()->json(['res'=>true,'message'=>config('constants.msg_new_srv')],200);
                }
                else
                {
                    return \response()->json(['res'=>false,'message'=>config('constants.msg_error_operacion_srv')],200);
                }
            }
            else
            {
                return \response()->json(['res'=>false,'message'=>$obj_validacion->errors()],200);
            }
        }
        catch(\Exception $e)
        {
            return \response()->json(['res'=>false,'message'=>config('constants.msg_error_srv')],200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        try
        {
            if(Style::where('id',$id)->count())
            {
                $style = Style::get()->find($id);
                return \response()->json(['res'=>true,'datos'=>$style],200);
            }
            else
            {
                return \response()->json(['res'=>false,'datos'=>[],'message'=>config('constants.msg_no_existe_srv')],200);
            }
        }
        catch(\Exception $e)
        {
            return \response()->json(['res'=>false,'datos'=>[],'message'=>config('constants.msg_error_srv')],200);
        }
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
        $inputs_f       = array();
        $rules = [
            'txt_name_st'     =>       'required|string|max:45'
        ];

        $inputs              =   $request->all();
        try
        {
            $obj_validacion     = Validator::make($inputs,$rules);

            if(!$obj_validacion->fails())
            {
                $style   =   Style::find($id);
                if($style->id == $id)
                {
                    $inputs_f['name']       =   $inputs['txt_name_st'];
                    $upd_style               =   $style->update($inputs_f);
                    if($upd_style)
                    {
                        return \response()->json(['res'=>true,'message'=>config('constants.msg_ok_srv')],200);
                    }
                    else
                    {
                        return \response()->json(['res'=>true,'message'=>config('constants.msg_error_operacion_srv')],200);
                    }
                }
                else
                {
                    return \response()->json(['res'=>true,'message'=>config('constants.msg_error_existe_srv')],200);
                }
            }
            else
            {
                return \response()->json(['res'=>true,'message'=>$obj_validacion->errors()],200);
            }
        }
        catch(\Exception $e)
        {
            return \response()->json(['res'=>false,'message'=>config('constants.msg_error_srv')],200);
        }
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
         try
         {
             $count_style     =   Style::where('id',$id)->count();

             if($count_style > 0)
             {
                Style::destroy($id);
                 return \response()->json(['res'=>true,'message'=>config('constants.msg_ok_srv')],200);
             }
             else
             {
                 return \response()->json(['res'=>true,'message'=>config('constants.msg_error_existe_srv')],200);
             }
         }
         catch(\Exception $e)
         {
             return \response()->json(['res'=>false,'message'=>config('constants.msg_error_srv')],200);
         }
    }
}
