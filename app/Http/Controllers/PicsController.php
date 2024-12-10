<?php

namespace App\Http\Controllers;

use App\Models\Pics;
use Illuminate\Http\Request;

class PicsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    return view('browse_pics', [
        'pics' => Pics::all() // Fetch all records
    ]);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'filename' => 'required|string|max:255',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate file type
    ]);

    try {
        // Store the uploaded image in the 'uploads' directory inside 'public/storage'
        $imagePath = $request->file('image')->store('uploads', 'public');

           // Construct the full URL
        $imageUrl = asset('storage/' . $imagePath);

        $pics = new Pics();
        $pics->filename = $validated['filename'];
        $pics->image = $imageUrl; // Save the file path

        \Log::info('Saving pick:', ['filename' => $pics->filename, 'image' => $pics->image]);

        $pics->save();

        \Log::info('Pick saved successfully:', ['id' => $pics->_id]);

        return response()->json(['message' => 'Pick created successfully!', 'data' => $pics], 201);
    } catch (\Exception $e) {
        \Log::error('Error saving pick:', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'An error occurred while saving the pick.', 'error' => $e->getMessage()], 500);
    }
}





    /**
     * Display the specified resource.
     */
  public function show(Pics $pics)
{
    return view('browse_pics', [
        'pics' => Pics::all()
    ]);
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pics $pics)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pics $pics)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pics $pics)
    {
        //
    }
}
