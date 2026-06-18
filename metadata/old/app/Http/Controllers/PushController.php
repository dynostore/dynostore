<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePushRequest;
use App\Http\Requests\UpdatePushRequest;
use App\Models\Push;

class PushController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePushRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePushRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Push  $push
     * @return \Illuminate\Http\Response
     */
    public function show(Push $push)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Push  $push
     * @return \Illuminate\Http\Response
     */
    public function edit(Push $push)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePushRequest  $request
     * @param  \App\Models\Push  $push
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePushRequest $request, Push $push)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Push  $push
     * @return \Illuminate\Http\Response
     */
    public function destroy(Push $push)
    {
        //
    }
}
