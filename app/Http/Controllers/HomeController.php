<?php

namespace App\Http\Controllers;

use App\DataTables\ReservationDataTable;
use App\DataTables\ReservationUserDataTable;
use App\DataTables\ReservationWaitingDataTable;
use App\Helpers\AuthHelper;
use App\Helpers\DateTimeHelper;
use App\Helpers\DurationHelper;
use App\Models\CaseAgenda;
use App\Models\Commentaire;
use App\Models\Gestionnaire;
use App\Models\GroupLocal;
use App\Models\LineTypeAccessoire;
use App\Models\Periode;
use App\Models\Reservation;
use App\Models\TypeAccessoire;
use App\Models\TypeJour;
use App\Models\TypeSalle;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /*
 * Dashboard Pages Routs
 */
    public function index(Request $request)
    {
        $assets = ['chart', 'animation', 'calender'];
        if (!is_null($request->get('date'))){
            $date_start=$request->get('date');
        }else{
            $date_start=date("Y-m-d");
        }
        $day = new \DateTime($date_start);
        $month = $day->format('m');
        $number = cal_days_in_month(CAL_GREGORIAN, $month, $day->format('y'));
        $headers = [];
        $bodys = [];
        for ($i = 1; $i <= $number; $i++) {
            $id_var = getdate(mktime(1, 1, 1, $month, $i, $day->format('y')));
            $headers[] = [
                'day' => DateTimeHelper::getDayByNumber($id_var['wday']),
                'number' => $i,
            ];
        }
        $groupes = GroupLocal::all();
        $salles = TypeSalle::all();
        foreach ($salles as $salle) {
            $line_reservation = [];
            for ($i = 1; $i <= $number; $i++) {
                $date_array = getdate(mktime(1, 1, 1, $month, $i, $day->format('y')));
                $date_jour_end = date('Y-m-d', mktime(23, 0, 0, $month, $i, $day->format('y')));
                $date_jour = date('Y-m-d', mktime(0, 0, 0, $month, $i, $day->format('y')));
                $reservations = Reservation::query()->where('status','=',Reservation::ACCEPTED)->where('local_id', '=', $salle->id)->where('date_reservation', '>=', $date_jour)
                    ->where('date_reservation', '<', $date_jour . ' 23:00:00')->get();
                $line_reservation[] = [
                    'day' => $date_jour_end,
                    'date_jour' => $date_jour,
                    'agenda' => $reservations,
                ];
            }
            $bodys[] = [

                'line' => $salle->type,
                'line_id' => $salle->id,
                'occupations' => $line_reservation
            ];
        }

        return view('index', ["date_start"=>$date_start,"headers" => $headers, "bodys" => $bodys, "groupes" => $groupes, "salles" => $salles, compact('assets'), 'month' => $number]);
    }

    public function agenda_month(Request $request)
    {
        $assets = ['chart', 'animation', 'calender'];
        $salle = $request->request->get('local', 1);
        $groupes = GroupLocal::all();
        $salles = TypeSalle::all();
        $day = new \DateTime($request->request->get('date_start'));
        $month = $day->format('m');
        return view('agenda_month', [ "date_start"=>$request->request->get('date_start'),"groupes" => $groupes, "salle" => $salle,'assets'=>$assets, compact('assets'), 'month' => $month]);
    }
    public function agenda_week(Request $request)
    {
        $assets = ['chart', 'animation', 'calender'];
        $salle = $request->request->get('local', 1);
        $groupes = GroupLocal::all();
        $salles = TypeSalle::all();
        $day = new \DateTime($request->request->get('date_start'));
        $month = $day->format('m');
        return view('agenda_week', [ "date_start"=>$request->request->get('date_start'),"groupes" => $groupes, "salle" => $salle,'assets'=>$assets, compact('assets'), 'month' => $month]);
    }
    public function agenda_day(Request $request)
    {
        $assets = ['chart', 'animation', 'calender'];
        $salle = $request->request->get('local', 1);
        $groupes = GroupLocal::all();
        $salles = TypeSalle::all();
        $day = new \DateTime($request->request->get('date_start'));
        $month = $day->format('m');
        return view('agenda_day', [ "date_start"=>$request->request->get('date_start'),"groupes" => $groupes, "salle" => $salle,'assets'=>$assets, compact('assets'), 'month' => $month]);
    }
    /*
     * Dashboard Pages Routs
     */
    public function dashboard(Request $request)
    {
        $assets = ['chart', 'animation', 'calender'];
        if ($request->ajax()) {
            $data = Reservation::query()->where('status', '=', Reservation::ACCEPTED)
                ->whereDate('start', '>=', $request->start)
                ->whereDate('end', '>=', $request->end)
                ->get(['id', 'start', 'end', 'libelle']);
            return response()->json($data);
        }
        return view('dashboards.dashboard', compact('assets'));
    }

    public function calendarevent(Request $request)
    {
        $events = [];
        $reservations = Reservation::query()
            ->where('local_id', '=', $request->request->get('local'))
            ->where('status', '=', Reservation::ACCEPTED)
            ->get();
        foreach ($reservations as $reservation) {
            if ($reservation->status == "PENDING") {
                $color = 'rgba(235,153,27,0.2)';
                $text_color = 'rgba(235,153,27,1)';
            } elseif ($reservation->status == "ACCEPTED") {
                $color = 'rgba(8,130,12,0.2)';
                $text_color = 'rgba(8,130,12,1)';
            } else {
                $color = 'rgba(235,153,27,0.2)';
                $text_color = 'rgba(235,153,27,1)';
            }
            $dateime = new \DateTime($reservation->date_reservation);

            $events[] = [
                'title' => "R_ ".$request->request->get('local') . $reservation->local->libelle,
                'start' => $dateime->format('Y-m-d') . ' ' . $reservation->start,
                'end' => $dateime->format('Y-m-d') . ' ' . $reservation->end,
                'textColor' => $text_color,
                'backgroundColor' => $color,
                'borderColor' => $text_color
            ];
        }
        return response()->json($events);
    }

    /*
     * Pages Routs
     */
    public function addreservation(Request $request)
    {
        $accessoires = TypeAccessoire::all();
        $typesalles = TypeSalle::all();
        $typejours = TypeJour::all();
        $periodes = Periode::all();
        return view('my.addreservation', ["periodes" => $periodes, "typesalles" => $typesalles, "accessoires" => $accessoires, "typejours" => $typejours]);
    }

    public function addreservation_home(Request $request)
    {
        $accessoires = TypeAccessoire::all();
        $typesalles = TypeSalle::all();
        $typejours = TypeJour::all();
        $periodes = Periode::all();
        return view('my.addreservation_home', ["date" => $request->get('date'), "periodes" => $periodes, "typesalles" => $typesalles, "accessoires" => $accessoires, "typejours" => $typejours]);
    }

    public function startreservation(Request $request)
    {
        $date = $request->get('date');
        $accessoires = TypeAccessoire::all();
        $typesalles = TypeSalle::all();
        $typejours = TypeJour::all();
        $periodes = Periode::all();
        return view('my.addreservation', ["periodes" => $periodes, "typesalles" => $typesalles, "accessoires" => $accessoires, "typejours" => $typejours]);

    }

    public function ajaxgetsalle(Request $request)
    {
        $salle = $request->get('typesalle');
        $jour = $request->get('typejour');
        if ($request->get('mode') == "getlocal") {
            $horaire = $request->get('horaire_reservation');
            $group = GroupLocal::query()->firstWhere('type_salle_id', '=', $salle)
                ->where('type_jour_id', '=', $jour)->getModel();
            $locals = $group->locals;
            return response()->json([
                'locals' => $locals,
                'group_id' => $group->id,
            ]);
        }elseif ($request->get('mode') == "getsalle"){
            $group = GroupLocal::query()->find($request->get('groupe'))->getModel();
            $locals = $group->locals;
            return response()->json([
                'locals' => $locals,
            ]);
        } else {

            return response()->json([
                'begins' => $this->getCrenneaux($request->get('horaire_reservation'),$request->get('typejour')),
                'ends' => $this->getCrenneauxEnd($request->get('horaire_reservation'),$request->get('typejour')),
            ]);
        }

    }

    public function getCrenneaux($crenau,$typejour)
    {
        if ($typejour==1){
            return [
                '08:25', '09:15', '10:05', '10:55',
                '11:45', '12:35', '13:25','14:15','15:05',
                '------------ Fin des cours en matinée ------------',
                '16:30', '17:30', '18:30', '19:30', '20:30', '21:30',
            ];
        }else{
            return [
                '08:25', '09:25', '10:25', '11:25',
                '12:25', '13:25', '14:25','15:25','16:25','17:25','18:25',
                '19:25','20:25','21:25','22:25',
            ];
        }

       /* if ($crenau == "08h25-15h45") {
            return [
                '08:25', '09:15', '10:05', '11:55',
                '12:45', '13:35', '14:25',
            ];
        } else {
            return [
                '16:00', '17:00', '18:00', '19:00',
                '20:00', '21:00',
            ];
        }*/
    }

    public function getCrenneauxEnd($crenau,$typejour)
    {
        if ($typejour==1){
            return [
                 '09:15', '10:05', '10:55',
                '11:45', '12:35', '13:25','14:15','15:05',
                '------------ Fin des cours en matinée ------------',
               '17:30', '18:30', '19:30', '20:30', '21:30','22:30',
            ];
        }else{
            return [
                 '09:25', '10:25', '11:25',
                '12:25', '13:25', '14:25','15:25','16:25','17:25','18:25',
                '19:25','20:25','21:25','22:25',
            ];
        }
      /*  if ($crenau == "08h25-15h45") {
            return [
                '09:15', '10:05', '11:55',
                '12:45', '13:35', '14:25', '15:15',
            ];
        } else {
            return [
                '17:00', '18:00', '19:00',
                '20:00', '21:00', '22:00',
            ];
        }*/

    }

    public function ajaxpostreservation(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user_id = $request->user()->id;
        $ob = $data['ob'];
        $reservation = new Reservation();
        $reservation->local_id = $data['local'];
        $reservation->start = $data['start'];
        $reservation->group_local_id = $data['group_local'];
        $reservation->user_id = $user_id;

        $reservation->periode_id = $data['periode'];
        $date_ = new \DateTime($data['date_reservation']);
        $reservation->end = $data['end'];
        $reservation->libelle = "RESERV " . $data['start'] . "-" . $data['end'];
        $reservation->status = "PENDING";
        $reservation->date_reservation = $date_;
        $reservation->save();
        for ($i = 0; $i < sizeof($ob); ++$i) {
            $line_accessoire = new LineTypeAccessoire();
            $quantity = $ob[$i]['quantity'];
            $line_accessoire->reservation_id = $reservation->id;
            $line_accessoire->type_accessoire_id = $ob[$i]['id'];
            $line_accessoire->nombre = $quantity;
            $line_accessoire->save();
        }
        return response()->json([
            $date_, $data['date_reservation'],
        ]);
    }

    public function myreservation(ReservationUserDataTable $dataTable)
    {
        $pageTitle = "Mes reservations";
        $auth_user = AuthHelper::authSession();
        $assets = ['data-table'];
        $headerAction = '';
        return $dataTable->render('global.datatable', compact('pageTitle', 'auth_user', 'assets', 'headerAction'));

    }

    public function waitreservation(ReservationWaitingDataTable $dataTable)
    {
        $pageTitle = "Reservations en attente";
        $auth_user = AuthHelper::authSession();
        $assets = ['data-table'];
        $headerAction = '';
        return $dataTable->render('global.datatable', compact('pageTitle', 'auth_user', 'assets', 'headerAction'));

    }

    public function activatereservation($id, Request $request)
    {
        $reservation = Reservation::query()->find($id);
        $gestionnaire = Gestionnaire::query()->firstWhere('user_id', '=', $request->user()->id);
        $reservation->update([
            'status' => Reservation::ACCEPTED,
            'gestionnaire_id' => $gestionnaire->id
        ]);
        $int = DurationHelper::getCreanneauBetwennTimes($reservation->start, $reservation->end);
        for ($i = 1; $i < $int; $i++) {
            $begin = DurationHelper::addCrenneau($reservation->start, $i);
            $agenda = CaseAgenda::query()->where('date_jour', '=', $reservation->date_reservation)
                ->where('type_jour_id', '=', $reservation->local_group->type_jour_id)
                ->where('heure_debut', '=', $begin)->first();

            if (is_null($agenda)) {
                $agenda = CaseAgenda::create([
                    "date_jour" => $reservation->date_reservation,
                    "libelle_jour" => date('D', strtotime($reservation->date_reservation)),
                    "heure_debut" => $begin,
                    "created_at" => new \DateTime('now'),
                    "type_jour_id" => $reservation->local_group->type_jour_id,
                ]);
            }
            $reservation->agenda()->sync($agenda);
        }
        return redirect()->route('listreservation')->withSuccess('Update successful!');
    }

    public function annulerreservation(Request $request)
    {
        $reservation = Reservation::query()->find($request->get('reservation_id'));

        $user = Gestionnaire::query()->firstWhere('user_id', '=', $request->user()->id);
        $commentaire = Commentaire::create([
            'message' => $request->get('message'),
            'reservation_id' => $reservation->id,
            'gestionnaire_id' => $user->id
        ]);
        $reservation->update([
            'status' => Reservation::DENIED,
            'gestionnaire_id' => $user->id
        ]);
        return redirect()->route('listreservation')->withSuccess('Update successful!');
    }

    public function deletereservation($id, Request $request)
    {
        $reservation = Reservation::query()->find($id);
        $lines = LineTypeAccessoire::query()->where('reservation_id', '=', $id)->get();
        foreach ($lines as $line) {
            $line->deleteOrFail();
        }
        $reservation->deleteOrFail();

        return redirect()->route('myreservation')->withSuccess('Delete successful!');
    }

    public function listreservation(ReservationDataTable $dataTable)
    {
        $pageTitle = "Liste des reservations";
        $auth_user = AuthHelper::authSession();
        $assets = ['data-table'];
        $headerAction = '';
        return $dataTable->render('global.datatable', compact('pageTitle', 'auth_user', 'assets', 'headerAction'));

    }


    public function signup(Request $request)
    {
        return view('auth.register');
    }

    public function confirmmail(Request $request)
    {
        return view('auth.confirm-mail');
    }

    public function lockscreen(Request $request)
    {
        return view('auth.lockscreen');
    }

    public function recoverpw(Request $request)
    {
        return view('auth.recoverpw');
    }

    public function userprivacysetting(Request $request)
    {
        return view('auth.user-privacy-setting');
    }

    /*
        * Icons Page Routs
        */

    public function solid(Request $request)
    {
        return view('icons.solid');
    }

    public function outline(Request $request)
    {
        return view('icons.outline');
    }

    public function dualtone(Request $request)
    {
        return view('icons.dualtone');
    }

    public function colored(Request $request)
    {
        return view('icons.colored');
    }
}
