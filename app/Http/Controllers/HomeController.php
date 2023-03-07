<?php

namespace App\Http\Controllers;

use App\DataTables\ReservationDataTable;
use App\DataTables\ReservationUserDataTable;
use App\DataTables\ReservationWaitingDataTable;
use App\Helpers\AuthHelper;
use App\Models\Agenda;
use App\Models\GroupLocal;
use App\Models\LineTypeAccessoire;
use App\Models\Periode;
use App\Models\Personnel;
use App\Models\Reservation;
use App\Models\TypeAccessoire;
use App\Models\TypeJour;
use App\Models\TypeSalle;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /*
     * Dashboard Pages Routs
     */
    public function index(Request $request)
    {
        $assets = ['chart', 'animation', 'calender'];
        if ($request->ajax()) {
            $data = Reservation::query()->whereDate('start', '>=', $request->start)
                ->whereDate('end', '>=', $request->end)
                ->get(['id', 'start', 'end', 'libelle']);
            return response()->json($data);
        }
        return view('dashboards.dashboard', compact('assets'));
    }

    public function calendarevent(Request $request)
    {
        $events = [];
        $reservations = Reservation::all();
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
            $events[] = [
                'title' => $reservation->libelle,
                'start' => $reservation->date_reservation,
                'textColor'=>$text_color,
                'backgroundColor' => $color,
                'borderColor'=>$text_color
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

    public function startreservation(Request $request)
    {
        $date = $request->post('date');
        return response()->json($date);
    }

    public function ajaxgetsalle(Request $request)
    {
        $salle = $request->get('typesalle');
        $jour = $request->get('typejour');
        if ($request->get('mode') == "getlocal") {
            $horaire = $request->get('horaire_reservation');
            $group = GroupLocal::query()->firstWhere('type_salle_id', '=', $salle)
                ->where('type_jour_id', '=', $jour)
                ->where('horaire_reservation', '=', $horaire)->getModel();
            $locals = $group->locals;
            return response()->json([
                'locals'=>$locals,
                'group_id'=>$group->id,
            ]);
        } else {
            $groups = GroupLocal::query()->where('type_salle_id', '=', $salle)
                ->where('type_jour_id', '=', $jour)->get();
            return response()->json($groups);
        }

    }

    public function ajaxpostreservation(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user_id = User::query()->firstWhere('account_id', '=', $request->user()->id)->id;
        $ob = $data['ob'];
        $reservation = new Reservation();
        $reservation->local_id = $data['local'];
        $reservation->start = $data['start'];
        $reservation->group_local_id = $data['group_local'];
        $reservation->user_id = $user_id;
        //$periode = Periode::query()->findOrFail($data['periode']);

        $reservation->periode_id = $data['periode'];
        $date_ = new \DateTime($data['date_reservation']);
        $reservation->end = $data['end'];
        $reservation->libelle = "RESERV " . $data['start'] . "-" .$data['end'];
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
/*        $user_id = Personnel::query()->firstWhere('account_id', '=', $request->user()->id)->id;
        $reservation->update([
            'status' => Reservation::DENIED,
            'personnel_id' => $user_id
        ]);
        $agenda=Agenda::query()->firstWhere('date_jour','=',$reservation->date_reservation)
        ->where('heure_debut','','')
        ->where('heure_fin','','');
        if (is_null($agenda)){
            $agenda=Agenda::create([
                "date_jour"=>'',
                "libelle_jour"=>'',
                "heure_debut"=>'',
                "heure_fin"=>'',
                "type_jour"=>'',
                "reservations"=>[]
            ]);
        }
        //$agenda->reservations=array_push($agenda->reservations,$reservation->id);
        $agenda->update([
           'reservations' =>array_push($agenda->reservations,$reservation->id)
        ]);*/
        return redirect()->route('listreservation')->withSuccess('Update successful!');
    }
    public function annulerreservation($id,Request $request)
    {
        $reservation = Reservation::query()->find($id);
                $user_id = Personnel::query()->firstWhere('account_id', '=', $request->user()->id)->id;
                $reservation->update([
                    'status' => Reservation::DENIED,
                    'personnel_id' => $user_id
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

}
