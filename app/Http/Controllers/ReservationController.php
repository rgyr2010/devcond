<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Reservation;
use App\Models\Area;
use App\Models\AreaDisableDay;
use App\Models\Unit;


class ReservationController extends Controller
{
    public function getReservation()
    {
        $array  = ['error' => '', 'list' => []];
        $areas  = Area::where('allowed', 1)->get();
        $daysHelper  = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        foreach ($areas as $area) {
            $dayList = explode(',', $area['day']);

            $dayGroup = [];

            //Adicionando o primeiro dia
            $lastDay = intval(current($dayList));
            $dayGroups[] = $daysHelper[$lastDay];
            array_shift($dayList);
            //adicionando dias relevantes
            foreach ($dayList as $day) {
                if (intval($day) != $lastDay + 1) {
                    $dayGroups[] = $daysHelper[$lastDay];
                    $dayGroups[] = $daysHelper[$day];
                }
                $lastDay = intval($day);
            }

            //adicionando o ultimo dia
            $dayGroups[] = $daysHelper[end($dayList)];

            //juntando as datas
            $dates = '';
            $close = 0;
            foreach ($dayGroups as $group) {
                if ($close === 0) {
                    $dates .= $group;
                } else {
                    $dates .= '-' . $group . ' , ';
                }
                $close = 1 - $close;
            }


            $dates = explode(',', $dates);
            array_pop($dates);


            //adicionando o time

            $start = date('H:i', strtotime($area['start_time']));
            $end   = date('H:i', strtotime($area['end_time']));

            foreach ($dates as $dKey => $value) {
                $dates[$dKey] .= '' . $start . ' ás ' . $end;
            }

            $array['list'][] = [
                'id' => $area['id'],
                'cover' => asset('storage/' . $area['cover']),
                'title' => $area['title'],
                'dates' => $dates
            ];
        }


        return $array;
    }

    public function setReservation($id,  Request $request)
    {
        $array  = ['error' => ''];

        $validator  = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i:s',
            'property' => 'required'
        ]);

        if (!$validator->fails()) {
            $date     = $request->input('date');
            $time     = $request->input('time');
            $property = $request->input('property');



            $unit =  Unit::find($property);
            $area =  Area::find($id);

            if ($unit && $area) {
                $can = true;

                $weekday = date('w', strtotime($date));

                // verificar se está dentro da disponibilidade padrão
                $allowedDays = explode(',', $area['day']);

                if (!in_array($weekday, $allowedDays)) {
                    $can = false;
                } else {
                    $start   = strtotime($area['start_time']);
                    $end     = strtotime('-1 hour', strtotime($area['end_time']));
                    $revTime = strtotime($time);

                    if ($revTime < $start || $revTime > $end) {
                        $can = false;
                    }
                }



                //Verificar se está fora DisabledDays
                $existingDisableDay = AreaDisableDay::where('id_area', $id)->count();
                if ($existingDisableDay > 0) {
                    $can = false;
                }

                $existingReservation = Reservation::where('id_area', $id)
                    ->where('reservation_date', $date . '' . $time)
                    ->count();

                if ($existingReservation > 0) {
                    $can = false;
                }

                //Verificar se não existe outra reserva no mesmo dia/hora
                if ($can) {
                    $newReservation = new Reservation();
                    $newReservation->id_unit = $property;
                    $newReservation->id_area = $id;
                    $newReservation->reservation_date = $date . ' ' . $time;

                    $newReservation->save();
                } else {
                    $array['error'] = "reserva não permitida nesse dia ou horario";
                    return $array;
                }
            } else {
                $array['error'] = "Dados incorretos";
                return $array;
            }
        } else {
            $array['error'] = $validator->errors()->first();
            return $array;
        }

        return $array;
    }

    public function getDisabledDates($id)
    {
        $array = ['error' => '', 'list' => []];
        //Dias disabled padrão

        $area = Area::find($id);

        if ($area) {
            $disableDays = AreaDisableDay::where("id_area", $id)->get();
            foreach ($disableDays as $disabledDay) {
                $array['list'][] = $disabledDay['day'];
            }

            // Dias disabled atráves do allowed
            $allowedDays = explode(',', $area['days']);
            $offDays = [];
            for ($q = 0; $q < 7; $q++) {
                if (!in_array($q, $allowedDays)) {
                    $offDays[] = $q;
                }
            }

            //Listar od Dias proibidos + 3 meses pra frente

            $start = time();
            $end   = strtotime('+3 months');
            $current = $start;




            for ($current = $start; $current < $end; $current = strtotime('+1 day', $current)) {
                $wd = date('w', $current);
                if (in_array($wd, $offDays)) {
                    $array['list'][] = date('Y-m-d', $current);
                }
            }
        } else {
            $array['error'] = "Area não existe";
            return $array;
        }
        return $array;
    }

    public function getTimes($id, Request $request)
    {
        $array = ['error' => '', 'list' => []];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d'
        ]);

        if (!$validator->fails()) {
            $date = $request->input('date');
            $area = Area::find($id);

            if ($area) {

                $can = true;

                //Verificar se é dia disabled
                $existingDisableDay = AreaDisableDay::where('id_area', $id)
                    ->where('day', $date)
                    ->count();

                if ($existingDisableDay > 0) {
                    $can = false;
                }

                //verificar se é dia permitido
                $allowedDays = explode(',', $area['day']);
                $weekday = date('w', strtotime($date));
                if (!in_array($weekday, $allowedDays)) {
                    $can = false;
                }

                if ($can) {
                    $start = strtotime($area['start_time']);
                    $end   = strtotime($area['end_time']);
                    $times = [];

                    for (
                        $lastTime = $start;
                        $lastTime < $end;
                        $lastTime = strtotime('+1 hour', $lastTime)
                    ) {
                        $times[] = $lastTime;
                    }

                    $timeList = [];

                    foreach ($times as $time) {
                        $timeList[] = [
                            'id'    => date('H:i:s', $time),
                            'title' => date('H:i', $time).' - '.date('H:i', strtotime('+1 hour', $time))
                        ];
                    }

                    //removendo quando tiver alguma reserva
                    $reservations  = Reservation::where('id_area', $id)
                        ->whereBetWeen('reservation_date', [
                            $date .' 00:00:00',
                            $date .' 23:59:00'
                        ])
                        ->get();

                    $toRemove = [];
                    foreach ($reservations as $reservatio) {
                        $time  = date('H:i:s', strtotime($reservatio['reservation_date']));
                        $toRemove[] = $time;
                    }

                    foreach ($timeList as $timeItem) {
                        if (!in_array($timeItem['id'], $toRemove)) {
                            $array['list'][] = $timeItem;
                        }
                    }
                }
            } else {
                $array['error'] = "Area inexistente ";
                return $array;
            }
        } else {
            $array['error'] = $validator->errors()->first();
            return $array;
        }

        return $array;
    }

    public function getMyReservation(Request $request ){
        $array  = ['error'=>'' , 'list'=>[]];

        $property = $request->input('property');
        if($property) {
            $unit = Unit::find($property);
            if($unit) {

                $reservations = Reservation::where('id_unit',$property)
                ->orderBy('reservation_date','DESC')
                ->get();

                foreach($reservations as $reservation){
                    $area = Area::find($reservation['id_area']);

                    $daterev   = date('d/m/Y H:i',strtotime($reservation['reservation_date']));
                    $afterTime = date('H:i',strtotime('+1 hour',strtotime($reservation['reservation_date'])));
                    $daterev  .=  ' à '. $afterTime;

                    $array['list'][] = [
                        'id'=>$reservation['id'],
                        'id_area'=>$reservation['id_area'],
                        'title'=>$area['title'],
                        'cover'=>asset('storage/'.$area['cover']),
                        'datereserved'=>$daterev
                    ];


                }


            } else {
                $array['error'] = 'propriedade necessária';
                return $array;

            }
        }else{
            $array['error'] = 'propriedade necessária';
            return $array;
        }

        return $array;

    }

    public function delMyreservation($id){
        $array  = ['error'=>'' , 'list'=>[]];

        $user  =auth()->user();
        $reservation = Reservation::find($id);
        if($reservation){
            $unit = Unit::where('id',$reservation['id_unit'])
            ->where('id_owner',$user['id'])
            ->count();

            if($unit > 0){
                Reservation::find($id)->delete();
            } else {
                $array['error'] = 'Esta reserva não é sua';
                return $array;
            }


        }else{
            $array['error'] = 'Reservar inexistente';
            return $array;
        }
    return $array;
 }
}
