<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChunkRequest;
use App\Http\Requests\UpdateChunkRequest;
use App\Models\Chunk;

class ChunkController extends Controller
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
     * @param  \App\Http\Requests\StoreChunkRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreChunkRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Chunk  $chunk
     * @return \Illuminate\Http\Response
     */
    public function show(Chunk $chunk)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Chunk  $chunk
     * @return \Illuminate\Http\Response
     */
    public function edit(Chunk $chunk)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateChunkRequest  $request
     * @param  \App\Models\Chunk  $chunk
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateChunkRequest $request, Chunk $chunk)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chunk  $chunk
     * @return \Illuminate\Http\Response
     */
    public function destroy(Chunk $chunk)
    {
        //
    }
}
