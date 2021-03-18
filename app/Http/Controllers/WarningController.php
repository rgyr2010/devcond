<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Warning;
use App\Models\Unit;
class WarningController extends Controller
{
    public function getMyWarning(Request $request){
        $array = ['array'=>''];

        $property = $request->input('property');
        if($property){

            $user = auth()->user();

            $unit  = Unit::where('id',$property)
            ->where('id_owner',$user['id'])
            ->count();
             
                if($unit > 0){
                    $warning = Warning::where('id_unit',$property)
                    ->orderBy('datecreated','DESC')
                    ->get();

                    foreach($warning as $warnKey => $warnValue){
                        $warning[$warnKey]['datereated'] = date('d/m/y',strtotime($warnValue['datecreated']));
                        $photoList = [];                     
                        $photos = explode(',',$warnValue['photos']);
                        foreach($photos as $photo){
                        if(!empty($photo)){
                            $photoList[] = asset('storage/'.$photo);
                        }
                    }
                    $warning[$warnKey]['photos'] = $photoList;
                }
                    $array['list'] = $warning;
            

                }else{
                    $array['error'] = 'Esta unidade não é sua';
                }
        
        }else{

            $array['error'] = "A propriedade é necessaria";

        }
        return $array;

    }

    public function addWarningFile(Request $request){
        $array = ['array'=>''];
        $validator  = Validator::make($request->all(),[
            'photo'=>'required|file|mimes:jpg,png'
        ]);
        if(!$validator->fails()){
            $file = $request->file('photo')->store('public');
            
            $array['photo'] = asset(Storage::url($file));
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }
        return $array;
    }


    public function setMyWarning(Request $request){
        $array = ['error'=>''];
        $validator  = Validator::make($request->all(),[
            'title'   =>'required',
            'property'=>'required'
        ]);

        if(!$validator->fails()){
           
            $title    = $request->input('title');
            $property = $request->input('property');
            $list     = $request->input('list');

            $newWarn = new Warning();
            $newWarn->id_unit = $property;
            $newWarn->title= $title;
            $newWarn->status = "IN_REVIEW";
            $newWarn->datecreated = date('Y-m-d');

            if($list && is_array($list)){
                $photos = [];                
                foreach($list as $listeItem){
                    $url = explode('/',$listeItem);
                    $photos[] = end($url);
            }
                $newWarn->photos = implode(',',$photos);
            } else{
                $newWarn->photos = "";
            }
                $newWarn->save();
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }            
        return $array;
    }
}
