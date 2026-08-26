<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppNotification extends Notification
{
    use Queueable;

    public $title;
    public $content;
    public $icon;
    public $color;
    public $url;
    public $category;

    /**
     * Create a new notification instance.
     *
     * @param string $title
     * @param string $content
     * @param string $icon
     * @param string $color
     * @param string $url
     * @param string $category (pesanan, pembayaran, produk, promo, chat, akun, sistem)
     */
    public function __construct($title, $content, $icon = 'bell', $color = 'orange', $url = '#', $category = 'sistem')
    {
        $this->title = $title;
        $this->content = $content;
        $this->icon = $icon;
        $this->color = $color;
        $this->url = $url;
        $this->category = $category;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'icon' => $this->icon,
            'color' => $this->color,
            'url' => $this->url,
            'category' => $this->category,
        ];
    }
}
