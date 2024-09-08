<?php

namespace App\Http\Controllers;

use App\Models\DiscountItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReviewRequiredNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ItemsNeedReview;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DiscountItemController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('Received request data:', $request->all());

            $validated = $request->validate([
                'date' => 'required|date',
                'supermarket' => 'required|string',
                'timeslot' => 'required|string',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'notes' => 'nullable|string',
            ]);

            Log::info('Validated data:', $validated);

            if ($request->hasFile('photo')) {
                $imagePath = $request->file('photo')->store('photos', 'public');
                $validated['photo'] = $imagePath;
                Log::info('Photo stored at:', ['path' => $imagePath]);
            }

            $itemDetails = $this->getItemDetailsFromImage($validated['photo']);
            Log::info('Item details from API:', $itemDetails);

            // Create discount items for each item returned by the API
            $createdItems = [];
            foreach ($itemDetails['items'] as $item) {
                $itemData = array_merge($validated, [
                    'item' => $item['item_name'],
                    'original_price' => $item['original_price'],
                    'discount_percentage' => $item['discount_percentage'] ?? 0, // Use 0 if null
                    'discounted_price' => $item['discounted_price'],
                ]);
                $createdItems[] = DiscountItem::create($itemData);
            }

            Log::info('Discount items created:', $createdItems);

            return response()->json([
                'message' => 'Discount items created successfully',
                'items' => $createdItems
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error in store method:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred while processing your request'], 500);
        }
    }

    private function getItemDetailsFromImage($imagePath)
    {
        // This method should call your actual API
        // For now, we'll use the data from the log
        return [
            'items' => [
                [
                    'item_name' => 'ツナマヨネーズ',
                    'original_price' => 298,
                    'discount_percentage' => 30,
                    'discounted_price' => 224.64
                ],
                [
                    'item_name' => '3種のおにぎりとおかずセット',
                    'original_price' => 298,
                    'discount_percentage' => 0, // Changed from null to 0
                    'discounted_price' => 32184
                ]
            ]
        ];
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'sometimes|required|date',
            'supermarket' => 'sometimes|required|string',
            'timeslot' => 'sometimes|required|string',
            'item' => 'sometimes|required|string',
            'original_price' => 'sometimes|required|numeric',
            'discount_percentage' => 'sometimes|required|numeric',
            'discounted_price' => 'sometimes|required|numeric',
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string',
            'sold_out' => 'sometimes|boolean',
        ]);

        $discountItem = DiscountItem::findOrFail($id);

        if ($request->hasFile('photo')) {
            $imagePath = $request->file('photo')->store('photos', 'public');
            $validated['photo'] = $imagePath;
        }

        $discountItem->update($validated);

        return response()->json([
            'message' => 'Discount item updated successfully',
            'item' => $discountItem
        ]);
    }

    public function destroy($id)
    {
        $discountItem = DiscountItem::findOrFail($id);
        $discountItem->delete();

        return response()->json([
            'message' => 'Discount item deleted successfully'
        ]);
    }

    public function index()
    {
        $discountItems = DiscountItem::latest()->get();
        return response()->json($discountItems);
    }
}