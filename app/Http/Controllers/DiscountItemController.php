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

class DiscountItemController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('Received request:', $request->all());
            Log::info('Files:', $request->allFiles());
            Log::info('Received request data:', $request->all());
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                Log::info('Photo details:', [
                    'name' => $photo->getClientOriginalName(),
                    'size' => $photo->getSize(),
                    'mime' => $photo->getMimeType(),
                ]);
            } else {
                Log::info('No photo file received');
            }
    
            $validated = $request->validate([
                'date' => 'required|date',
                'supermarket' => 'required|string',
                'timeslot' => 'required|string',
                'notes' => 'nullable|string',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
    
            $imagePath = $request->file('photo')->store('photos', 'public');
        $extractedText = $this->extractTextFromImage(storage_path('app/public/' . $imagePath));
        
        try {
            $items = $this->processWithGPT($extractedText);
        } catch (\Exception $e) {
            Log::error('Error processing GPT response: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process the image. Please try again.'], 500);
        }

        if (empty($items)) {
            return response()->json(['error' => 'No items could be extracted from the image.'], 422);
        }
    
            $needsReview = count($items) > 1;
    
            $createdItems = [];
        $needsReview = count($items) > 1;

        foreach ($items as $item) {
            $discountItem = DiscountItem::create([
                'date' => $validated['date'],
                'supermarket' => $validated['supermarket'],
                'timeslot' => $validated['timeslot'],
                'notes' => $validated['notes'],
                'photo' => $imagePath,
                'item' => $item['item'],
                'original_price' => $item['original_price'],
                'discount_percentage' => $item['discount_percentage'],
                'discounted_price' => $item['discounted_price'],
                'needs_review' => $needsReview,
            ]);

            $createdItems[] = $discountItem;
        }

        if ($needsReview) {
            try {
                $this->notifyAdminForReview($imagePath, $createdItems);
            } catch (\Exception $e) {
                Log::error('Failed to notify admin for review: ' . $e->getMessage());
                // Continue execution even if notification fails
            }
        }

        return response()->json([
            'message' => 'Discount item(s) added successfully' . ($needsReview ? ' and flagged for review' : ''),
            'items' => $createdItems,
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation failed:', ['errors' => $e->errors()]);
        return response()->json(['errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Error in DiscountItemController@store: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while processing your request.'], 500);
    }
    
    }

private function extractTextFromImage($imagePath)
{
    $apiKey = config('services.google.vision.api_key');
    if (!$apiKey) {
        Log::error('Google Vision API key is not set');
        return null;
    }

    $url = "https://vision.googleapis.com/v1/images:annotate?key={$apiKey}";

    $image = file_get_contents($imagePath);
    $encodedImage = base64_encode($image);

    $payload = [
        'requests' => [
            [
                'image' => [
                    'content' => $encodedImage
                ],
                'features' => [
                    ['type' => 'TEXT_DETECTION']
                ]
            ]
        ]
    ];

    try {
        $response = Http::post($url, $payload);

        if ($response->successful()) {
            $result = $response->json();
            $text = $result['responses'][0]['textAnnotations'][0]['description'] ?? '';
            Log::info('Extracted text from image:', ['text' => $text]);
            return $text;
        } else {
            $error = $response->json();
            Log::error('Google Vision API error: ' . json_encode($error));
            return null;
        }
    } catch (\Exception $e) {
        Log::error('Exception when calling Google Vision API: ' . $e->getMessage());
        return null;
    }
}

private function notifyAdminForReview($imagePath, $items)
{
    // Log the items flagged for review
    Log::info('Items flagged for review', [
        'image_path' => $imagePath,
        'items' => $items,
    ]);

    // Temporarily comment out the email sending
    // $adminEmail = config('app.admin_email', 'admin@example.com');
    // Mail::to($adminEmail)->send(new ReviewRequiredNotification($imagePath, $items));

    // Temporarily comment out the notification
    // $adminUsers = \App\Models\User::where('is_admin', true)->get();
    // Notification::send($adminUsers, new ItemsNeedReview($imagePath, $items));

    // For now, just log that we would notify admins
    Log::info('Would notify admins about items needing review', [
        'image_path' => $imagePath,
        'items' => $items,
    ]);
}


private function processWithGPT($text)
{
    $apiKey = env('OPENAI_API_KEY');

    $prompt = "You are a helpful assistant that extracts product information from Japanese text. Extract the item name, original price, discount percentage, and discounted price from the following text. Please ignore any English text. Provide the information in a JSON format with an 'items' array, even if there's only one item:\n\n$text";

    $messages = [
        [
            'role' => 'user',
            'content' => $prompt,
        ]
    ];

    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->timeout(60)
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
            'max_tokens' => 300,
            'temperature' => 0.3,
        ]);

        $responseArray = $response->json();

        Log::info('OpenAI GPT Response: ' . json_encode($responseArray));

        if (isset($responseArray['error'])) {
            throw new \Exception($responseArray['error']['message']);
        }

        $content = $responseArray['choices'][0]['message']['content'] ?? '';
        $content = preg_replace('/```json\s*|\s*```/', '', $content);

        $decodedContent = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse JSON response from GPT: ' . json_last_error_msg());
        }

        Log::info('Decoded GPT Response:', $decodedContent);

        // If the response doesn't have an 'items' key, wrap the single item in an array
        if (!isset($decodedContent['items'])) {
            $decodedContent = ['items' => [$decodedContent]];
        }

        $items = $decodedContent['items'];

        if (empty($items)) {
            throw new \Exception('No items found in GPT response');
        }

        return array_map(function($item) {
            return [
                'item' => $item['item_name'] ?? null,
                'original_price' => $item['original_price'] ?? null,
                'discount_percentage' => $item['discount_percentage'] ?? null,
                'discounted_price' => $item['discounted_price'] ?? null,
            ];
        }, $items);

    } catch (\Exception $e) {
        Log::error('Error with OpenAI API request: ' . $e->getMessage());
        // Instead of returning an empty array, throw the exception
        throw $e;
    }
}

    public function index()
    {
        $discountItems = DiscountItem::latest()->get();
        return response()->json($discountItems);
    }
}