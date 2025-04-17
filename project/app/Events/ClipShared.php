<?php

namespace App\Events;

use App\Models\Clip;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClipShared implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The clip that was shared.
     *
     * @var \App\Models\Clip
     */
    public $clip;

    /**
     * The user who shared the clip.
     *
     * @var \App\Models\User
     */
    public $sender;

    /**
     * The user who receives the clip.
     *
     * @var \App\Models\User
     */
    public $recipient;

    /**
     * Create a new event instance.
     */
    public function __construct(Clip $clip, User $sender, User $recipient)
    {
        $this->clip = $clip;
        $this->sender = $sender;
        $this->recipient = $recipient;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->recipient->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'clip.shared';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->clip->id,
            'title' => $this->clip->title,
            'game_id' => $this->clip->game_id,
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }
} 