<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ItemsNeedReview extends Notification
{
    use Queueable;

    public $imagePath;
    public $items;

    public function __construct($imagePath, $items)
    {
        $this->imagePath = $imagePath;
        $this->items = $items;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'image_path' => $this->imagePath,
            'items_count' => count($this->items),
            'message' => 'New discount items need review',
        ];
    }
}
