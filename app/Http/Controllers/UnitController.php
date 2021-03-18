<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Unit;
use App\Models\UnitPeople;
use App\Models\UnitVehicle;
use App\Models\UnitPet;
class UnitController extends Controller
{
 public function getInfo($id){
    $array = ['error' => ''];
    
    $unit = Unit::find($id);

    if($unit){
        $peoples = UnitPeople::where('id_unit',$id)->get();
        $vehicles = UnitVehicle::where('id_unit',$id)->get();
        $pets = UnitPet::where('id_unit',$id)->get();

        foreach($peoples as $pKey => $pValue){
            $peoples[$pKey]['birthdate'] = date('d/m/Y',strtotime($pValue['birthdate']));


        }

        $array['peopes'] = $peoples;
        $array['vehicles'] = $vehicles;
        $array['pets']=$pets;
      


    }else{
     $array['error'] = "Propiedade inexistente";
    }
    
    return $array;

 }

 public function AddPerson($id , Request $request){
     $array = ['error'=>''];

     $validator = Validator::make($request->all(),[
    'name' => 'required',
    'birthdate'=>'required|date'
     ]);

     if(!$validator->fails()){
         $name      = $request->input('name');
         $birthdate = $request->input('birthdate');

         $newPerson = new UnitPeople;
         $newPerson->id_unit = $id;
         $newPerson->name = $name;
         $newPerson->birthdate = $birthdate;
         $newPerson->save();


        
     }else{
        $array['error'] = $validator->errors()->first();
     }
    

     return $array;
 }


 public function AddVehicle($id , Request $request){
    $array = ['error'=>''];

    $validator = Validator::make($request->all(),[
   'title' => 'required',
   'color'=>'required',
   'plate'=>'required'
    ]);

    if(!$validator->fails()){
        $title      = $request->input('title');
        $color = $request->input('color');
        $plate = $request->input('plate');

        $newVehicle= new UnitVehicle();
        $newVehicle->id_unit = $id;
        $newVehicle->title = $title;
        $newVehicle->color = $color;
        $newVehicle->plate = $plate;
        $newVehicle->save();


       
    }else{
       $array['error'] = $validator->errors()->first();
    }
   

    return $array;
}


public function AddPet($id , Request $request){
    $array = ['error'=>''];

    $validator = Validator::make($request->all(),[
   'name' => 'required',
   'race'=>'required'

    ]);

    if(!$validator->fails()){
        $name      = $request->input('name');
        $race = $request->input('race');
     

        $newVPet= new UnitPet();
        $newVPet->id_unit = $id;
        $newVPet->name = $name;
        $newVPet->race = $race;      
        $newVPet->save();


       
    }else{
       $array['error'] = $validator->errors()->first();
    }
   

    return $array;
}


public function removePerson($id , Request $request){
    $array = ['error'=>''];
    $idItem = $request->input('id');
    if($idItem){
        UnitPeople::where('id',$idItem)
        ->where('id_unit',$id)
        ->delete();

    }else{
        $array['error'] = 'id inexistente';
        return $array;
    }
}


public function removeVehicle($id , Request $request){
    $array = ['error'=>''];
    $idItem = $request->input('id');
    if($idItem){
        UnitVehicle::where('id',$idItem)
        ->where('id_unit',$id)
        ->delete();

    }else{
        $array['error'] = 'id inexistente';
        return $array;
    }
}


public function removePet($id , Request $request){
    $array = ['error'=>''];
    $idItem = $request->input('id');
    if($idItem){
        UnitPet::where('id',$idItem)
        ->where('id_unit',$id)
        ->delete();

    }else{
        $array['error'] = 'id inexistente';
        return $array;
    }
}
}
