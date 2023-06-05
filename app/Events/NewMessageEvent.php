<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    private  $chatMessage;

    public function __construct(Message $chatmessage)
    {
        //
        $this->chatMessage = $chatmessage;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat'.$this->chatMessage->id);
    }


    /**
     * Broadcast's event name
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Data sending back to client
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chatMessage->id,
            'message' => $this->chatMessage->toArray(),
            'receivers' => $this->chatMessage->receiver_id !==null ?  User::where("id", $this->chatMessage->receiver_id)->get(): []
        ];
    }
}
