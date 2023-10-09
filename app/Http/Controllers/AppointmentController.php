<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller as BaseController;

class AppointmentController extends BaseController
{
    protected $client = null;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->client = new Client();
    }

    public function saveAppointment(Request $request)
    {
        $apiUrl = env('APPOINTMENT_SERVICE', '') . "/users/appointment";
        $input = $request->all();
        $input['user_id'] = auth()->user()->id;
        $input['user_name'] = auth()->user()->first_name;
        $input['user_email'] = auth()->user()->email;
        try {
            $res = $this->client->request(
                'POST',
                $apiUrl,
                [
                    'form_params' => $input
                ]
            );
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => $e->getMessage(),
                ]
            ], 404);
        }
        return json_decode($res->getBody(), true);
    }

    public function newAppointment()
    {
        $vaccineListUrl = env('APPOINTMENT_SERVICE', '') . "/vaccines";
        $hospitalListUrl = env('APPOINTMENT_SERVICE', '') . "/hospitals";

        try {
            $vaccineList = $this->client->request('GET', $vaccineListUrl);
            $hospitalList = $this->client->request('GET', $hospitalListUrl);

            $response = [
                'user' => auth()->user(),
                'vaccines' => json_decode($vaccineList->getBody(), true),
                'hospitals' => json_decode($hospitalList->getBody(), true)
            ];
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => $e->getMessage(),
                ]
            ], 404);
        }

        return response()->json($response, 200);
    }

    public function appointments()
    {
        $authUser = auth()->user();
        $apiUrl = env('APPOINTMENT_SERVICE', '') . "/users/" . $authUser->id . "/appointments";
        try {
            $appointment = $this->client->request('GET', $apiUrl);
            $response = [
                'user' => $authUser,
                'appointments' => json_decode($appointment->getBody(), true),
            ];
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => $e->getMessage(),
                ]
            ], 404);
        }
        return response()->json($response, 200);
    }

    public function getAppointment($appointmentId)
    {
        $authUser = auth()->user();
        $vaccineListUrl = env('APPOINTMENT_SERVICE', '') . "/vaccines";
        $hospitalListUrl = env('APPOINTMENT_SERVICE', '') . "/hospitals";
        $apiUrl = env('APPOINTMENT_SERVICE', '') . "/appointments/$appointmentId";
        try {
            $appointment = $this->client->request('GET', $apiUrl);
            $vaccineList = $this->client->request('GET', $vaccineListUrl);
            $hospitalList = $this->client->request('GET', $hospitalListUrl);
            $response = [
                'user' => $authUser,
                'appointment' => json_decode($appointment->getBody(), true),
                'vaccines' => json_decode($vaccineList->getBody(), true),
                'hospitals' => json_decode($hospitalList->getBody(), true)
            ];
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => $e->getMessage(),
                ]
            ], 404);
        }
        return response()->json($response, 200);
    }

    public function updateAppointment($appointmentId, Request $request)
    {
        $apiUrl = env('APPOINTMENT_SERVICE', '') . "/users/appointment/$appointmentId";
        $input = $request->all();
        $input['user_id'] = auth()->user()->id;
        $input['user_name'] = auth()->user()->first_name;
        $input['user_email'] = auth()->user()->email;
        try {
            $res = $this->client->request(
                'POST',
                $apiUrl,
                [
                    'form_params' => $input
                ]
            );
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => $e->getMessage(),
                ]
            ], 404);
        }
        return json_decode($res->getBody(), true);
    }
}
