<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\Trim;



class TrimController extends Controller
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
            if(count(Trim::all()) > 0)
            {
                $trims  = Trim::with(['modelo:id,name'])->get();
                return \response()->json(['res'=>true,'data'=>$trims],200);
            }
            else
            {
                return \response()->json(['res'=>false,'message'=>config('constants.msg_empty')],200);
            }
        }
        catch(\Exception $e)
        {
            return \response()->json(['res'=>false,'message'=>config('constants.msg_error_srv')],200);
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
        $rules = [
            'slc_model_trim'    =>       'required|exists:App\Models\Modelo,id',
            'txt_name_trim'     =>       'required|string|max:45'
        ];

        try
        {
            $inputs              =   $request->all();

            $obj_validacion     = Validator::make($inputs,$rules);

            if(!$obj_validacion->fails())
            {
                $inputs_f['modelo_id']       =   $inputs['slc_model_trim'];
                $inputs_f['name']          =   trim($inputs['txt_name_trim']);
                $trim       =   Trim::create($inputs_f);

                if($trim->id > 0)
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
            if(Trim::where('id',$id)->count())
            {
                $trim = Trim::with(['modelo'])->find($id);
                return \response()->json(['res'=>true,'datos'=>$trim],200);
            }
            else
            {
                return \response()->json(['res'=>true,'message'=>config('constants.msg_no_existe_srv')],200);
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
    public function getTrimByModelo($id)
    {
        //
        try
        {
            if(Trim::where('modelo_id',$id)->count())
            {
                $trim = Trim::where('modelo_id',$id)->get();
                return \response()->json(['res'=>true,'datos'=>$trim],200);
            }
            else
            {
                return \response()->json(['res'=>false,'message'=>config('constants.msg_no_existe_srv')],200);
            }
        }
        catch(\Exception $e)
        {
            return \response()->json(['res'=>false,'message'=>config('constants.msg_error_srv')],200);
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
        $rules = [
            'slc_model_tr_up'    =>       'required|exists:App\Models\Modelo,id',
            'txt_name_trim'      =>       'required|string|max:45'
        ];

        $inputs              =   $request->all();

        try
        {
            $obj_validacion     = Validator::make($inputs,$rules);

            if(!$obj_validacion->fails())
            {
                $trim   =   Trim::find($id);
                if($trim->id == $id)
                {
                    $inputs_f['modelo_id']          =   $inputs['slc_model_tr_up'];
                    $inputs_f['name']               =   $inputs['txt_name_trim'];
                    $upd_trim                       =   $trim->update($inputs_f);
                    if($upd_trim)
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
            $count_trim     =   Trim::where('id',$id)->count();

            if($count_trim > 0)
            {
                Trim::destroy($id);
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


    public function getTrimFull($id)
    {
        try
        {
            if($id > 0)
            {
                $trims  =   DB::table('makes as mk')
                        ->join('modelos as md','md.make_id', '=', 'mk.id')
                        ->join('trims as tr', 'tr.modelo_id', '=', 'md.id')
                        ->select('mk.id as id_make','md.id as id_modelo','tr.id as id_trim',DB::raw('CONCAT(mk.name, \' \', md.name) as full_name'), 'tr.name as name_trim')
                        ->where('tr.id',$id)
                        ->get();
            }
            else
            {
                $trims  =   DB::table('makes as mk')
                        ->join('modelos as md','md.make_id', '=', 'mk.id')
                        ->join('trims as tr', 'tr.modelo_id', '=', 'md.id')
                        ->select('mk.id as id_make','md.id as id_modelo','tr.id as id_trim',DB::raw('CONCAT(mk.name, \' \', md.name) as full_name'), 'tr.name as name_trim')
                        ->get();
            }

            if($trims->count())
            {
                return \response()->json(['res'=>true,'data'=>$trims],200);
            }
            else
            {
                return \response()->json(['res'=>true,'message'=>config('constants.msg_no_existe_srv')],200);
            }
        }
        catch(\Exception $e)
        {
            return \response()->json(['res'=>false,'message'=>$e],200);
        }
    }
}
