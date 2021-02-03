<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Contact;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contacts = DB::table('contacts')->get();

        foreach ($contacts as $contact) {
            try {
                $response = file_get_contents(env('EMAIL_SUBSCRIPTION_API') . '/get_status?email=' . $contact->email);
                $data = json_decode($response);
                if ($data['status'] == 'subscribed') {
                    $contact->subscribed = true;
                } else {
                    $contact->subscribed = false;
                }
            } catch (\Exception $e) {
                Log::error($e);
                $contact->subscribed = false;
            }
        }

        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('contacts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $contact = new Contact([
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'job_title' => $_POST['job_title'],
            'city' => $_POST['city'],
            'country' => $_POST['country']
        ]);
        $contact->save();
        return redirect('http://127.0.0.1:8000/contacts')->with('success', 'Contact saved!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $contact = DB::table('contacts')->where('id', $id)->first();
        return view('contacts.edit', compact('contact'));
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
        $contact = Contact::where('id', $id)->first();
        $contact->first_name = $_POST['first_name'];
        $contact->last_name = $_POST['last_name'];
        $contact->email = $_POST['email'];
        $contact->job_title = $_POST['job_title'];
        $contact->city = $_POST['city'];
        $contact->country = $_POST['country'];
        $contact->save();

        return redirect('http://127.0.0.1:8000/contacts')->with('success', 'Contact updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table('contacts')->where('id', $id)->delete();
        return redirect('http://127.0.0.1:8000/contacts')->with('success', 'Contact deleted!');
    }
}
