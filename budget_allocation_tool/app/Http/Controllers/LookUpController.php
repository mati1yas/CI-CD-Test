<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\LookUpResource;
use App\Models\LookUp;

class LookUpController extends Controller
{
    /**
     * Create a new mapping.
     */
    public function createType(Request $request)
    {
        // $validated = $request->validate([
        //     'type' => 'required|string|max:50',
        //     'key' => 'required|string|max:50',
        //     'value' => 'required|string|max:255',
        // ]);

        // $lookup = LookUp::create($validated);

        $lookup = LookUp::updateOrCreate([
            'type' => $request['type'],
            'key' => $request['key'],
        ],
        [
            'value' => $request['value'],
        ]);

        return response()->json([
            'message' => 'Mapping created successfully',
            'data' => new LookUpResource($lookup),
        ], 201);
    }

    /**
     * Fetch all mappings for a specific type.
     */
    public function fetchAllMaps(Request $request)
    {
        $type = $request->query('type'); // Optional type filter

        $query = LookUp::query();

        if ($type) {
            $query->where('type', $type);
        }

        $lookups = $query->get();

        return LookUpResource::collection($lookups);
    }
}
