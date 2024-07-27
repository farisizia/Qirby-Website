<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Property;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MatanYadaev\EloquentSpatial\Objects\Point;
use App\Models\Jadwal;

;
class PropertyController extends Controller
{
    public function index()
    {

        $property = Property::all();
        $images = Image::all();
        if (request()->segment(1) == 'api')
            return response()->json([
                'error' => false,
                'data' => $property
            ]);
        return view('pages.management', ['property' => $property, 'images' => $images]);
    }


    public function store(Request $request)
    {
        $data = $request->except(['_token', 'koordinat-x', 'koordinat-y']);

        $data['koordinat'] = new Point($request->input('koordinat-x'), $request->input('koordinat-y'));

        $request->validate([
            'name' => 'required|string',
            'price' => 'required',
            'status' => 'required',
            'address' => 'required',
            'description' => 'required',
            'sqft' => 'required|integer',
            'bath' => 'required|integer',
            'garage' => 'required|integer',
            'floor' => 'required|integer',
            'bed' => 'required|integer',
        ]);

        $new_property = Property::create($data);
        if ($request->has('images')) {
            foreach ($request->file('images') as $image) {
                // $image_property = $request->image;
                $original_image_property = Str::random(10) . $image->getClientOriginalName();
                $image->storeAs('public/images_property', $original_image_property);
                Image::create([
                    'property_id' => $new_property->id,
                    'image' => $original_image_property
                ]);
            }
        }
        return redirect()->route('property.view')->with('success', 'Property added');
    }

    public function update(Request $request, $id)
    {
        $data = $request->except('_token');

        $request->validate([
            'image.*' => 'mimes:jpeg,jpg,png',
            'name' => 'required|string',
            'price' => 'required',
            'status' => 'required',
            'description' => 'required',
            'sqft' => 'required|integer',
            'bath' => 'required|integer',
            'garage' => 'required|integer',
            'floor' => 'required|integer',
            'bed' => 'required|integer',
        ]);

        $properties = Property::find($id);

        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $image) {
                $original_image_property = Str::random(10) . $image->getClientOriginalName();
                $image->storeAs('public/images_property', $original_image_property);
                Image::create([
                    'property_id' => $properties->id,
                    'image' => $original_image_property
                ]);
            }
        }

        $properties->update($data);

        return redirect()->route('property.view')->with('success', 'Property updated');
    }

    public function deleteImage($imageId)
    {
        $image = Image::findOrFail($imageId);
        Storage::disk('public')->delete('images_property/' . $image->image);
        $image->delete();

        return redirect()->back()->with('success', 'Image deleted.');
    }

    // public function deleted($id)
    // {
    //     $property = Property::findOrFail($id);

    //     // Ensure images are retrieved as a collection
    //     $images = $property->images;

    //     if ($images->isNotEmpty()) {
    //         foreach ($images as $image) {
    //             Storage::disk('public')->delete('images_property/' . $image->image);
    //             $image->delete();
    //         }
    //     }


    //     $property->delete();

    //     return redirect()->route('property.view')->with('success', 'Property deleted');
    // }


    public function deleted($id)
    {
        try {
            $property = Property::findOrFail($id);

            // Retrieve images associated with the property
            $images = $property->images;

            // Check if there are existing schedules (Jadwal) associated with the property
            if (Jadwal::where('id_properti', $id)->exists()) {
                return response()->json(['error' => 'Cannot delete property with existing schedules.'], 422);
            }

            // Delete the property, but keep images if no schedules exist
            $property->delete();

            if ($images->isNotEmpty()) {
                foreach ($images as $image) {
                    Storage::disk('public')->delete('images_property/' . $image->image);
                    $image->delete();
                }
            }

            return response()->json(['success' => 'Property deleted successfully.']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Property not found.'], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => 'An error occurred while deleting the property.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }



}


