<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use DB;
use PDF;
use Image;
use File;
class ExportPurchase_ApiController extends Controller
{
  public $successStatus = "1";
  public $successMessage = "Success";
  public $successCode = "200";

  public $errorcode = "400";
  public $errorStatus ="0";
  public $errorMessage = "error";

  public $successCreatedCode="201";

   public function __construct()
   {
          $this->middleware('jwt');
   }

   public function Export_Purchase_file(Request $request)
   {

      if($request->isMethod('post')){

           $date =  $request->input('date');
           $user_id =  $request->input('user_id');

                if($date==null&&$user_id==null){
                     $purchase_result =  DB::table('purchase')->get();
                }

                elseif($date==null&&$user_id!==null){
                    $purchase_result =  DB::table('purchase')->where('user_id', $user_id)->get();
                }

                elseif($date!==null&&$user_id==null){
                  $validator = Validator::make($request->all(), [
                           'date'=>'date_format:Y-m-d'
                         ]);
                   if ($validator->fails()) {
                          return response()->json(['error'=>$validator->errors()], 401);
                        }
                   else{
                     $purchase_result =  DB::table('purchase')->where('date', $date)->get();
                        }

                }

                elseif($date!==null&&$user_id!==null){
                  $validator = Validator::make($request->all(), [
                           'date'=>'date_format:Y-m-d'
                         ]);
                     if ($validator->fails()) {
                          return response()->json(['error'=>$validator->errors()], 401);
                        }
                    else{
                    $purchase_result =  DB::table('purchase')->where('date', $date)->where('user_id', $user_id)->get();
                        }
                }

                else{
                  return response()->json([
                                          'status'=>$this->errorStatus,
                                          'code'=>$this->errorcode,
                                          'message'=>"error"]);
                }


                  if($purchase_result){
                    if(count($purchase_result)<=0){
                       return response()->json([
                                                'status'=>$this->successStatus,
                                                'code'=>$this->successCode,
                                                'message'=>"Product Purchase is empty"
                                               ], $this->successCode);
                      }else{

                         $purchase_record = [];

                            for($i=0;$i<count($purchase_result);$i++){

                              $user_name = DB::table('user')->where('id', $purchase_result[$i]->user_id)->value('username');
                              $product_name = DB::table('product')->where('id', $purchase_result[$i]->product_id)->value('product_name');
                              array_push($purchase_record,
                                           [
                                            "user_name"=>$user_name,
                                            "product_name"=>$product_name,
                                            "total_weight"=>$purchase_result[$i]->total_weight,
                                            "total_amount"=>$purchase_result[$i]->total_amount,
                                            "weight_labour"=>$purchase_result[$i]->weight_labour,
                                            "transport_labour"=>$purchase_result[$i]->transport_labour,
                                            "shop_number"=>$purchase_result[$i]->shop_number,
                                            "date"=>$purchase_result[$i]->date,
                                            "profit_range"=>$purchase_result[$i]->profit_range,
                                            "kg_price"=>$purchase_result[$i]->kg_price,
                                           ]
                               );
                            }

                          // return $purchase_record;
                              view()->share('data',$purchase_record);

                              // return view('purchase_pdfview');

                               $pdf = PDF::loadView('purchase_pdfview');
                              // return $pdf->download('purchase.pdf'); //............*direct download file
                               $destinationPath = "pdf";

                               //check folder is or not
                               File::isDirectory($destinationPath) or File::makeDirectory($destinationPath, 0777, true, true);

                             //make filename
                                $ldate = date('d-m-Y');
                                $t=time();
                                $filename = "p".$purchase_result[0]->product_id."-".$ldate."-".$t.".pdf";

                            //save file
                                $pdf->save('pdf/'.$filename);
                               if( $pdf->save('pdf/'.$filename)){
                                 $file_storage_path = asset("pdf/".$filename);
                                 return response()->json([
                                                          "file"=>$file_storage_path,
                                                          'status'=>$this->successStatus,
                                                          'code'=>$this->successCode,
                                                          'message'=>$this->successMessage
                                                        ],$this->successCode);
                               }
                               else{
                                 return response()->json([
                                                         'status'=>$this->errorStatus,
                                                         'code'=>$this->errorcode,
                                                         'message'=>"Failed to generate pdf file"]);
                               }

                             // $pdf_storage_path = assets('pdf/order_email.pdf');


                      }
                    }
                  else{
                      return response()->json([
                                              'status'=>$this->errorStatus,
                                              'code'=>$this->errorcode,
                                              'message'=>"Failed to Find Product Purchase"]);

                  }




           }
       else{
           return response()->json("Method not Allow", 405);
       }

   }



   public function list_Purchase_on_date(Request $request)
   {
     if($request->isMethod('post')){

          $date =  $request->input('date');
          $user_id =  $request->input('user_id');

          if($date==null&&$user_id==null){
               $purchase_result =  DB::table('purchase')->get();
          }

          elseif($date==null&&$user_id!==null){
              $purchase_result =  DB::table('purchase')->where('user_id', $user_id)->get();
          }

          elseif($date!==null&&$user_id==null){
            $validator = Validator::make($request->all(), [
                     'date'=>'date_format:Y-m-d'
                   ]);
             if ($validator->fails()) {
                    return response()->json(['error'=>$validator->errors()], 401);
                  }
             else{
               $purchase_result =  DB::table('purchase')->where('date', $date)->get();
                  }

          }

          elseif($date!==null&&$user_id!==null){
            $validator = Validator::make($request->all(), [
                     'date'=>'date_format:Y-m-d'
                   ]);
               if ($validator->fails()) {
                    return response()->json(['error'=>$validator->errors()], 401);
                  }
              else{
              $purchase_result =  DB::table('purchase')->where('date', $date)->where('user_id', $user_id)->get();
                  }
          }

          else{
            return response()->json([
                                    'status'=>$this->errorStatus,
                                    'code'=>$this->errorcode,
                                    'message'=>"error"]);
          }


                //    $purchase_result =  DB::table('purchase')->where('date', $date)->get();

                 if($purchase_result){
                   if(count($purchase_result)<=0){
                      return response()->json([
                                               'status'=>$this->successStatus,
                                               'code'=>$this->successCode,
                                               'message'=>"Product Purchase is empty"
                                              ], $this->successCode);
                     }else{

                        $purchase_record = [];

                           for($i=0;$i<count($purchase_result);$i++){

                             $user_name = DB::table('user')->where('id', $purchase_result[$i]->user_id)->value('username');
                             $product_name = DB::table('product')->where('id', $purchase_result[$i]->product_id)->value('product_name');
                             $product_img = DB::table('product')->where('id', $purchase_result[$i]->product_id)->value('product_image');
                             array_push($purchase_record,
                                          [
                                           "id"=>$purchase_result[$i]->id,
                                           "user_id"=>$purchase_result[$i]->user_id,
                                           "user_name"=>$user_name,
                                           "product_id"=>$purchase_result[$i]->product_id,
                                           "product_name"=>$product_name,
                                           "product_image"=>$product_img,
                                           "total_weight"=>$purchase_result[$i]->total_weight,
                                           "total_amount"=>$purchase_result[$i]->total_amount,
                                           "weight_labour"=>$purchase_result[$i]->weight_labour,
                                           "transport_labour"=>$purchase_result[$i]->transport_labour,
                                           "shop_number"=>$purchase_result[$i]->shop_number,
                                           "date"=>$purchase_result[$i]->date,
                                           "profit_range"=>$purchase_result[$i]->profit_range,
                                           "wastage"=>$purchase_result[$i]->wastage,
                                           "description"=>$purchase_result[$i]->description,
                                           "kg_price"=>$purchase_result[$i]->kg_price,
                                           'created_at'=>$purchase_result[$i]->created_at,
                                           'updated_at'=>$purchase_result[$i]->created_at
                                          ]
                              );
                           }

                            return response()->json([
                                                     "data"=>$purchase_record,
                                                     'status'=>$this->successStatus,
                                                     'code'=>$this->successCode,
                                                     'message'=>$this->successMessage
                                                   ],$this->successCode);

                     }
                   }
                 else{
                     return response()->json([
                                             'status'=>$this->errorStatus,
                                             'code'=>$this->errorcode,
                                             'message'=>"Failed to Find Product Purchase"]);

                 }


          }
      else{
          return response()->json("Method not Allow", 405);
      }

   }




  public function purchase_orders_between_two_dates(Request $request){
    if($request->isMethod('post')){

         $from_date =  $request->input('from-date');
         $to_date =  $request->input('to-date');


                $validator = Validator::make($request->all(), [
                         'from-date'=>'required|date_format:Y-m-d',
                         'to-date'=>'required|date_format:Y-m-d'
                       ]);
                   if ($validator->fails()) {
                        return response()->json(['error'=>$validator->errors()], 401);
                      }
                  else{

                  $purchase_result =  DB::table('purchase')
                                        ->whereBetween('date', [$from_date, $to_date])
                                        ->orderBy('date', 'asc')
                                        ->get();

                if($purchase_result){
                  if(count($purchase_result)<=0){
                     return response()->json([
                                              'status'=>$this->successStatus,
                                              'code'=>$this->successCode,
                                              'message'=>"Product Purchase order is empty"
                                             ], $this->successCode);
                    }else{

                       $purchase_record = [];

                          for($i=0;$i<count($purchase_result);$i++){

                            $user_name = DB::table('user')->where('id', $purchase_result[$i]->user_id)->value('username');
                            $product_name = DB::table('product')->where('id', $purchase_result[$i]->product_id)->value('product_name');
                            array_push($purchase_record,
                                         [
                                          "user_name"=>$user_name,
                                          "product_name"=>$product_name,
                                          "total_weight"=>$purchase_result[$i]->total_weight,
                                          "total_amount"=>$purchase_result[$i]->total_amount,
                                          "weight_labour"=>$purchase_result[$i]->weight_labour,
                                          "transport_labour"=>$purchase_result[$i]->transport_labour,
                                          "shop_number"=>$purchase_result[$i]->shop_number,
                                          "date"=>$purchase_result[$i]->date,
                                          "profit_range"=>$purchase_result[$i]->profit_range,
                                          "kg_price"=>$purchase_result[$i]->kg_price,
                                         ]
                             );
                          }

                        // return $purchase_record;
                            view()->share('data',$purchase_record);
                            // return view('purchase_pdfview');

                                     $pdf = PDF::loadView('purchase_pdfview');
                                    // return $pdf->download('purchase.pdf');

                                     $destinationPath = "pdf";
                                     File::isDirectory($destinationPath) or File::makeDirectory($destinationPath, 0777, true, true);

                                      //make filename
                                         $ldate = date('d-m-Y');
                                         $t=time();
                                         $filename = "p".$purchase_result[0]->product_id."-".$ldate."-".$t.".pdf";

                                      $pdf->save('pdf/'.$filename);
                                     if( $pdf->save('pdf/'.$filename)){
                                       $file_storage_path = asset("pdf/".$filename);
                                       return response()->json([
                                                                "file"=>$file_storage_path,
                                                                'status'=>$this->successStatus,
                                                                'code'=>$this->successCode,
                                                                'message'=>$this->successMessage
                                                              ],$this->successCode);
                                     }
                                     else{
                                       return response()->json([
                                                               'status'=>$this->errorStatus,
                                                               'code'=>$this->errorcode,
                                                               'message'=>"Failed to generate pdf file"]);
                                     }

                    }
                  }
                else{
                    return response()->json([
                                            'status'=>$this->errorStatus,
                                            'code'=>$this->errorcode,
                                            'message'=>"Failed to Find Product Purchase order"]);

                }
              }



         }
     else{
         return response()->json("Method not Allow", 405);
     }
  }

}
