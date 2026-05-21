<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Client;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $data = $request->validate([
            'type' => 'required|in:note,call,email,invoice_sent,payment_received,hosting_renewed,domain_renewed,project_update,whatsapp,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $client->activities()->create($data);

        return redirect()->route('clients.show', $client)->with('success', 'Activity logged.');
    }

    public function destroy(Activity $activity)
    {
        $client = $activity->client;
        $activity->delete();

        return redirect()->route('clients.show', $client)->with('success', 'Activity deleted.');
    }
}
