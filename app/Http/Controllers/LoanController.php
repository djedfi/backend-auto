<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use \App\Models\Loan;
use \App\Models\Car;
use \App\Models\PaymentLoan;
use \App\Models\SchedulePayment;
use \App\Custom\CuotaClass;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use \App\Http\Traits\GFunctionsTrait;


class LoanController extends Controller
{
    use GFunctionsTrait;
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
            if(count(Loan::all()) > 0)
            {
                return \response()->json(['res'=>true,'data'=>Loan::all()],200);
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
        $rules = [
            'hid_id_user_loan'         =>        'required|exists:App\Models\User,id',
            'hid_car_id_loan'          =>        'required|exists:App\Models\Car,id',
            'hid_customer_id_loan'     =>        'required|exists:App\Models\Customer,id',
            'txt_price_car_loan'       =>        'required|regex:/^\d+(\.\d{1,2})?$/',
            'txt_ttfinance_loan'        =>        'required|regex:/^\d+(\.\d{1,2})?$/',
            'txt_dpayment_loan'        =>        'required|regex:/^\d+(\.\d{1,2})?$/',
            'txt_mpayment_loan'        =>        'required|regex:/^\d+(\.\d{1,2})?$/',
            // 'txt_costo_plate_loan'     =>        'required|regex:/^\d+(\.\d{1,2})?$/',
            // 'txt_costo_doc_loan'       =>        'required|regex:/^\d+(\.\d{1,2})?$/',
            'txt_interest_loan'        =>        'nullable',
            'txt_taxes_loan'           =>        'nullable',
            'txt_long_term_loan'       =>        'required|integer',
            'date_open_loan'           =>        'required|date_format:m/d/Y',
            'date_startpay_loan'       =>        'required|date_format:m/d/Y',
            'txt_late_days_fee_loan'   =>        'required|regex:/^\d+(\.\d{1,2})?$/',
            'txt_late_days_loan'       =>        'required|integer'
        ];

        DB::beginTransaction();

        try
        {
            $inputs                             =   $request->all();
            $inputs['txt_price_car_loan']       =   str_replace(array('US$ ',','),array('',''),$inputs['txt_price_car_loan']);
            $inputs['txt_ttfinance_loan']       =   str_replace(array('US$ ',','),array('',''),$inputs['txt_ttfinance_loan']);
            $inputs['txt_dpayment_loan']        =   str_replace(array('US$ ',','),array('',''),$inputs['txt_dpayment_loan']);
            $inputs['txt_mpayment_loan']        =   str_replace(array('US$ ',','),array('',''),$inputs['txt_mpayment_loan']);
            // $inputs['txt_costo_plate_loan']     =   str_replace(array('US$ ',','),array('',''),$inputs['txt_costo_plate_loan']);
            // $inputs['txt_costo_doc_loan']       =   str_replace(array('US$ ',','),array('',''),$inputs['txt_costo_doc_loan']);
            $inputs['txt_costo_plate_loan']     =   0;
            $inputs['txt_costo_doc_loan']       =   0;
            $inputs['txt_interest_loan']        =   str_replace(array(' %',','),array('',''),$inputs['txt_interest_loan']);
            $inputs['txt_taxes_loan']           =   str_replace(array(' %',','),array('',''),$inputs['txt_taxes_loan']);
            $inputs['txt_late_days_fee_loan']   =   str_replace(array('US$ ',','),array('',''),$inputs['txt_late_days_fee_loan']);

            $obj_validacion     = Validator::make($inputs,$rules);

            if(!$obj_validacion->fails())
            {
                list($m_spay,$d_spay,$Y_spay)      =   explode('/',$inputs['date_startpay_loan']);
                list($m_opay,$d_opay,$Y_opay)      =   explode('/',$inputs['date_open_loan']);

                if($inputs['txt_interest_loan'] == '')
                {
                    $inputs['txt_interest_loan']       =   0;
                }

                if($inputs['txt_taxes_loan'] == '')
                {
                    $inputs['txt_taxes_loan']       =   0;
                }


                // $new_cuota  =   new CuotaClass;
                // $new_cuota->setTasaInteres($inputs['txt_interest_loan']);
                // $new_cuota->setTasaTaxes($inputs['txt_taxes_loan']);
                // $new_cuota->setCostoPlaca($inputs['txt_costo_plate_loan']);
                // $new_cuota->setCostoDocs($inputs['txt_costo_doc_loan']);
                // $new_cuota->setPrecio($inputs['txt_price_car_loan']);
                // $new_cuota->setDownPayment($inputs['txt_dpayment_loan']);
                // $new_cuota->setPeriodos($inputs['txt_long_term_loan']);


                // $costo_sdownpayment =   $new_cuota->calcularFinanceND();
                // $valor_financiar    =   $new_cuota->calcularFinance();
                // $pago_mensual       =   $new_cuota->getPagoMensual();


                $loan           =   Loan::create
                ([
                    'customer_id'       => $inputs['hid_customer_id_loan'],
                    'car_id'            => $inputs['hid_car_id_loan'],
                    'user_id'           => $inputs['hid_id_user_loan'],
                    'price'             => $inputs['txt_price_car_loan'],
                    'downpayment'       => $inputs['txt_dpayment_loan'],
                    'long_term'         => $inputs['txt_long_term_loan'],
                    'interest_rate'     => $inputs['txt_interest_loan'],
                    'taxes_rate'        => $inputs['txt_taxes_loan'],
                    'minimun_payment'   =>  $inputs['txt_mpayment_loan'] ,
                    'loan_date'         => $Y_opay.'-'.$m_opay.'-'.$d_opay,
                    'start_payment'     => $Y_spay.'-'.$m_spay.'-'.$d_spay,
                    'late_fee'          => $inputs['txt_late_days_fee_loan'],
                    'days_late'         => $inputs['txt_late_days_loan'],
                    'pago_automatico'   => isset($inputs['chk_auto_payment_loan']) ? 1:0,
                    'pay_documentation' => $inputs['txt_costo_doc_loan'],
                    'pay_placa'         => $inputs['txt_costo_plate_loan'],
                    'total_financed'    => $inputs['txt_ttfinance_loan'] ,
                    'balance'           => number_format(floatval($inputs['txt_ttfinance_loan']) - floatval($inputs['txt_dpayment_loan']),2,'.','')
                ]);
                $loan_id        =   $loan->id;

                if($loan_id > 0)
                {
                    //actualizar el estado del carro
                    $car            =   Car::where('id',$inputs['hid_car_id_loan'])->update(array('estado'=>2));

                    $data_payment_multi     =
                    [
                        [
                            'loan_id'               => $loan_id,
                            'user_id'               => $inputs['hid_id_user_loan'],
                            'description'           => 'Total to finance without down payment',
                            'concepto'              => 1,
                            'monto'                 => 0,
                            'date_doit'             => $Y_opay.'-'.$m_opay.'-'.$d_opay,
                            'forma_pago'            => 4,
                            'balance'               => $inputs['txt_ttfinance_loan']
                        ],
                        [
                            'loan_id'               => $loan_id,
                            'user_id'               => $inputs['hid_id_user_loan'],
                            'description'           => 'Pay down payment',
                            'concepto'              => 2,
                            'monto'                 => $inputs['txt_dpayment_loan'],
                            'date_doit'             => $Y_opay.'-'.$m_opay.'-'.$d_opay,
                            'forma_pago'            => 4,
                            'balance'               => number_format(floatval($inputs['txt_ttfinance_loan']) - floatval($inputs['txt_dpayment_loan']),2,'.','')
                        ]
                    ];
                    //registrar costo financiar sin down payment
                    //registrar pago downpayment
                    PaymentLoan::insert($data_payment_multi);

                    //registrar los pagos en el futuro.
                    $numeros_pagos  =   $inputs['txt_long_term_loan'] + 24;


                    for($i=1;$i<=$numeros_pagos;$i++)
                    {
                        if($i == 1)
                        {
                            $date_probramable       =   $Y_spay.'-'.$m_spay.'-'.$d_spay;
                            unset($dt);
                            $dt                     =    Carbon::create($Y_spay, $m_spay, $d_spay, 0);
                            $date_ultimo            =    $dt->addDays($inputs['txt_late_days_loan']);

                        }
                        else
                        {
                            unset($dts);
                            $dts                                    =    CarbonImmutable::create($Y_spay, $m_spay, $d_spay, 0);
                            $dts->settings([
                                'monthOverflow' => false,
                            ]);
                            $date_probramable       =   $dts->add(($i-1),'month');

                            $fecha_programable      =   explode(' ',$date_probramable);

                            list($Y_dp,$m_dp,$d_dp)      =   explode('-',$fecha_programable[0]);
                            $dt3                     =    Carbon::create($Y_dp, $m_dp, $d_dp, 0);
                            $date_ultimo             =    $dt3->addDays($inputs['txt_late_days_loan']);
                        }

                        $schedule_pay                =   SchedulePayment::create
                        ([
                            'loan_id'               => $loan_id,
                            'user_id'               => $inputs['hid_id_user_loan'],
                            'date_programable'      => $date_probramable,
                            'date_end'              => $date_ultimo
                        ]);

                        if($schedule_pay->id < 0)
                        {
                            DB::rollback();
                            return \response()->json(['res'=>false,'message'=>config('constants.msg_error_operacion_srv')],200);
                        }
                    }

                    if($car)
                    {
                        DB::commit();
                        return \response()->json(['res'=>true,'message'=>config('constants.msg_new_srv'),'loan_id'=>$loan_id],200);
                    }
                    else
                    {
                        DB::rollback();
                        return \response()->json(['res'=>false,'message'=>config('constants.msg_error_operacion_srv')],200);
                    }
                }
                else
                {
                    DB::rollback();
                    return \response()->json(['res'=>false,'message'=>config('constants.msg_error_operacion_srv')],200);
                }
            }
            else
            {
                DB::rollback();
                return \response()->json(['res'=>false,'message'=>$obj_validacion->errors()],200);
            }
        }
        //catch(\Exception $e)
        catch(\Illuminate\Database\QueryException $ex)
        {
            DB::rollback();
            return \response()->json(['res'=>false,'message'=>$ex->getMessage()],200);
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
            if(Loan::where('id',$id)->count())
            {
                $loan = Loan::with(['customer:id','car:id'])->get()->find($id);
                return \response()->json(['res'=>true,'datos'=>$loan],200);
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
        //
        $rules = [
            'txt_fee_late_tab_loan'       =>        'required|regex:/^\d+(\.\d{1,2})?$/',
        ];
        try
        {
            $inputs                 =   $request->all();
            $inputs['txt_fee_late_tab_loan']         =   str_replace(array('US$ ',','),array('',''),$inputs['txt_fee_late_tab_loan']);
            $obj_validacion         = Validator::make($inputs,$rules);

            if(!$obj_validacion->fails())
            {

                if($inputs['txt_interest_tab_loan'] == '')
                {
                    $inputs['txt_interest_tab_loan']       =   0;
                }

                if($inputs['txt_taxes_tab_loan'] == '')
                {
                    $inputs['txt_taxes_tab_loan']       =   0;
                }

                $loan                =   Loan::where('id',$id)
                                        ->update(
                                            array(
                                                'late_fee'          =>$inputs['txt_fee_late_tab_loan'],
                                                'interest_rate'     => $inputs['txt_interest_tab_loan'],
                                                'taxes_rate'        => $inputs['txt_taxes_tab_loan'],
                                                'pago_automatico'   => isset($inputs['chk_auto_payment_tab_loan']) ? 1:0,
                                            )
                                        );

                if($loan)
                {
                    DB::commit();
                    return \response()->json(['res'=>true,'message'=>config('constants.msg_ok_srv'),'fee'=>$inputs['txt_fee_late_tab_loan'],'pago_automatico'   => isset($inputs['chk_auto_payment_tab_loan']) ? 1:0],200);
                }
                else
                {
                    DB::rollback();
                    return \response()->json(['res'=>false,'message'=>config('constants.msg_error_operacion_srv')],200);
                }
            }
            else
            {
                DB::rollback();
                return \response()->json(['res'=>false,'message'=>$obj_validacion->errors()],200);
            }
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return \response()->json(['res'=>false,'message'=>$e],200);
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
            $count_loan     =   Loan::where('id',$id)->count();

            if($count_loan > 0)
            {
                Loan::destroy($id);
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


    public function calculoPagoMensual(Request $request)
    {

        $capital        =   $request->valor_capital;
        $interes        =   $request->valor_interes;
        $tiempo         =   $request->valor_meses;
        $check_taxes    =   $request->valor_ctaxes;
        $taxes_rate     =   $request->valor_taxes;
        $array_long_term = array(1=>24,2=>36,3=>48,4=>60,5=>72,6=>84);

        try
        {
            $new_cuota  =   new CuotaClass;

            $new_cuota->setTasaInteres($interes);
            $new_cuota->setCapital($capital);
            $new_cuota->setPeriodos($array_long_term[$tiempo]);

            $pago_mensual = $new_cuota->getPagoMensual();


            return \response()->json(['res'=>true,'pago_mensual'=>number_format($pago_mensual,2,".","")],200);
        }
        catch(\Exception $e)
        {
            return \response()->json(['res'=>false,'message'=>$e],200);
        }
    }


    public function getReporteLoan($tipo)
    {
        try
        {
            //reporte general de los loans
            if($tipo == 1)
            {
                $loan  =   DB::table('loans as l')
                        ->join('cars as cr','l.car_id', '=', 'cr.id')
                        ->join('customers as cust', 'l.customer_id', '=', 'cust.id')
                        ->join('trims as tr', 'tr.id', '=', 'cr.trim_id')
                        ->join('modelos as md', 'md.id', '=', 'tr.modelo_id')
                        ->join('makes as mk', 'mk.id', '=', 'make_id')
                        ->select('l.id as loan_id',DB::raw("CONCAT(cust.last_name,', ',cust.first_name) as full_name"),'cust.email',DB::raw("CONCAT(mk.name,' ',md.name,' ',tr.name) as modelo_car"),'cr.stock_number','cr.vin','l.total_financed','l.loan_date','l.pago_automatico','l.balance')
                        ->get();
            }
            //reporte de los loans que se deben presentar a pagar en la fecha que se consulte a pagar
            else if($tipo == 2)
            {
                $loan  =   DB::table('loans as l')
                        ->join('cars as cr','l.car_id', '=', 'cr.id')
                        ->join('customers as cust', 'l.customer_id', '=', 'cust.id')
                        ->join('trims as tr', 'tr.id', '=', 'cr.trim_id')
                        ->join('modelos as md', 'md.id', '=', 'tr.modelo_id')
                        ->join('makes as mk', 'mk.id', '=', 'make_id')
                        ->join('schedule_payments as sc', 'sc.loan_id', '=', 'l.id')
                        ->select('l.id as loan_id',DB::raw("CONCAT(cust.last_name,', ',cust.first_name) as full_name"),'cust.email',DB::raw("CONCAT(mk.name,' ',md.name,' ',tr.name) as modelo_car"),'cr.stock_number','cr.vin','l.minimun_payment','l.loan_date','sc.id','l.pago_automatico','sc.date_programable')
                        ->where('sc.date_programable', '=', Carbon::now()->format('Y-m-d'))
                        ->get();

                if($loan->count())
                {
                    $data= json_decode($loan, true);
                    $array_id_loan =array();
                    foreach($data as $key => $qs)
                    {
                        $date_programable      = Carbon::create($qs['date_programable'])->subDay(env('DIAS_GRACIAS_BEFORE'));
                        if(!$this->checkpayment($qs['loan_id'],$date_programable,$qs['date_programable']))
                        {
                            array_push($array_id_loan,$qs);
                        }
                    }

                    if(count($array_id_loan) > 0)
                    {
                        return \response()->json(['res'=>true,'data'=>$array_id_loan],200);
                    }
                    else
                    {
                        return \response()->json(['res'=>true,'data'=>[]],200);
                    }
                }
                else
                {
                    return \response()->json(['res'=>true,'data'=>[]],200);
                }

            }
            //reporte de los lons que despues del plazo de gracia para pagar, aun no se han presentado
            else if($tipo == 3)
            {
                $loan  =   DB::table('loans as l')
                        ->join('cars as cr','l.car_id', '=', 'cr.id')
                        ->join('customers as cust', 'l.customer_id', '=', 'cust.id')
                        ->join('trims as tr', 'tr.id', '=', 'cr.trim_id')
                        ->join('modelos as md', 'md.id', '=', 'tr.modelo_id')
                        ->join('makes as mk', 'mk.id', '=', 'make_id')
                        ->join('schedule_payments as sc', 'sc.loan_id', '=', 'l.id')
                        ->select('l.id as loan_id',DB::raw("CONCAT(cust.last_name,', ',cust.first_name) as full_name"),'cust.email',DB::raw("CONCAT(mk.name,' ',md.name,' ',tr.name) as modelo_car"),'cr.stock_number','cr.vin','l.minimun_payment','l.loan_date','sc.id','l.pago_automatico','sc.date_programable','sc.date_end')
                        ->where('sc.date_end', '=', Carbon::now()->format('Y-m-d'))
                        ->get();
                if($loan->count())
                {
                    $data= json_decode($loan, true);
                    $array_id_loan =array();
                    foreach($data as $key => $qs)
                    {
                        $date_programable      = Carbon::create($qs['date_programable'])->subDay(env('DIAS_GRACIAS_BEFORE'));
                        if(!$this->checkpayment($qs['loan_id'],$date_programable,$qs['date_end']))
                        {
                            array_push($array_id_loan,$qs);
                        }
                    }
                    if(count($array_id_loan) > 0)
                    {
                        return \response()->json(['res'=>true,'data'=>$array_id_loan],200);
                    }
                    else
                    {
                        return \response()->json(['res'=>true,'data'=>[]],200);
                    }

                }
                else
                {
                    return \response()->json(['res'=>true,'data'=>[]],200);
                }
            }
            else
            {
                return \response()->json(['res'=>true,'message'=>config('constants.msg_no_existe_srv'),'data'=>[]],200);
            }

            if($loan->count())
            {
                return \response()->json(['res'=>true,'data'=>$loan],200);
            }
            else
            {
                return \response()->json(['res'=>true,'data'=>[]],200);
            }
        }
        catch(\Exception $e)
        {
            return \response()->json(['res'=>false,'message'=>$e,'data'=>[]],200);
        }
    }

    public function getSchedule($id)
    {
        $numeros_pagos = $id;

        list($m_spay,$d_spay,$Y_spay)      =   explode('/','06/03/2019');
        $array_datos        = [];
        $array_datos_final  = [];
        $numeros_pagos = $numeros_pagos + 24;

        for($i=1;$i<=$numeros_pagos;$i++)
        {
            if($i == 1)
            {
                $date_probramable       =   $Y_spay.'-'.$m_spay.'-'.$d_spay;
                unset($dt);
                $dt                     =    Carbon::create($Y_spay, $m_spay, $d_spay, 0);
                $date_ultimo            =    $dt->addDays(10);
                $array_datos            =   array('date_programable'=>$date_probramable,'date_ultimo'=>$date_ultimo);
                array_push($array_datos_final,$array_datos);

            }
            else
            {
                unset($dts);
                $dts                                    =    CarbonImmutable::create($Y_spay, $m_spay, $d_spay, 0);
                $dts->settings([
                    'monthOverflow' => false,
                ]);
                $date_probramable       =   $dts->add(($i-1),'month');

                $fecha_programable      =   explode(' ',$date_probramable);

                list($Y_dp,$m_dp,$d_dp)      =   explode('-',$fecha_programable[0]);
                $dt3                     =    Carbon::create($Y_dp, $m_dp, $d_dp, 0);
                $date_ultimo             =    $dt3->addDays(10);
                $array_datos            =   array('date_programable'=>$date_probramable,'date_ultimo'=>$date_ultimo);
                array_push($array_datos_final,$array_datos);
            }
        }

        return \response()->json($array_datos_final,200);

    }


}
