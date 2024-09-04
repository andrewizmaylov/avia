<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AircraftController extends Controller
{
	/**
	 * @param  Request  $request
	 * @return Collection
	 */
	public function index(Request $request): \Illuminate\Support\Collection
	{
		return DB::table('flights')
			->join('airports', 'airports.id', '=', 'flights.airport_id1')
			->join('aircrafts', 'aircrafts.id', '=', 'flights.aircraft_id')
			->select([
				'airports.id as airport_id',
				'aircrafts.tail',
				'airports.code_iata',
				'airports.code_icao',
				'flights.id as flight_id',
				'flights.cargo_offload',
				'flights.cargo_load',
				DB::raw("(SELECT MAX(f.landing) FROM flights f WHERE f.aircraft_id = flights.aircraft_id AND f.airport_id2 = flights.airport_id1 AND f.landing < flights.takeoff) as landing_at"),
				'flights.takeoff',
			])
			->where("aircrafts.tail", $request->tail)
			->where(function ($query) use ($request) {
				$query->whereBetween('flights.landing', [$request->date_from, $request->date_to])
					->orWhereBetween('flights.takeoff', [$request->date_from, $request->date_to]);
			})
			->orderBy('flights.takeoff')
			->orderBy('flights.landing')
			->get();

	}
}
