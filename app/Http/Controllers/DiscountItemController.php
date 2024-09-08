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
                $photo = $request->file('photo');
                Log::info('Photo details:', [
                    'name' => $photo->getClientOriginalName(),
                    'size' => $photo->getSize(),
                    'mime' => $photo->getMimeType(),
                ]);
            } else {
                Log::info('No photo file received');
                return response()->json(['error' => 'No photo file received'], 400);
            }
    
            // Step 1: Extract text from the image using Google Vision API
            $extractedText = $this->extractTextFromImage($validated['photo']);
    
            if (!$extractedText) {
                return response()->json(['error' => 'Failed to extract text from image'], 500);
            }
    
            Log::info('Extracted text:', ['text' => $extractedText]);
    
            // Step 2: Process extracted text with OpenAI GPT to parse item details
            try {
                $parsedItems = $this->processWithGPT($extractedText);
    
                // Ensure parsedItems is an array and properly formatted
                if (!is_array($parsedItems)) {
                    throw new \Exception('Invalid format from GPT: Parsed items should be an array.');
                }
    
                Log::info('Parsed items from GPT:', ['parsedItems' => $parsedItems]);
            } catch (\Exception $e) {
                Log::error('Error processing with GPT:', ['message' => $e->getMessage()]);
                return response()->json(['error' => 'Failed to process text with GPT'], 500);
            }
    
            // Step 3: Create discount items for each parsed item
            $createdItems = [];
            foreach ($parsedItems as $item) {
                $itemData = array_merge($validated, [
                    'item' => $item['item'],
                    'original_price' => $item['original_price'],
                    'discount_percentage' => $item['discount_percentage'] ?? 0,
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

            // Return the extracted text directly
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
    
            // Check if 'choices' and 'content' are available
            if (!isset($responseArray['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response format from GPT: Missing "content"');
            }
    
            $content = $responseArray['choices'][0]['message']['content'];
    
            // Ensure content is a JSON-formatted string
            if (is_string($content)) {
                $content = preg_replace('/```json\s*|\s*```/', '', $content); // Remove any JSON code block delimiters
                $decodedContent = json_decode($content, true);
            } else {
                throw new \Exception('Invalid response format from GPT: Expected JSON string, got ' . gettype($content));
            }
    
            // Verify JSON was correctly parsed
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Failed to parse JSON response from GPT: ' . json_last_error_msg());
            }
    
            Log::info('Decoded GPT Response:', $decodedContent);
    
            // Ensure 'items' is correctly parsed and is an array
            if (!isset($decodedContent['items']) || !is_array($decodedContent['items'])) {
                throw new \Exception('Invalid items format from GPT response');
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
            throw $e;
        }
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