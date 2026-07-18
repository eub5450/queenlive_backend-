<?php
namespace App\Services;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\RoomCreateOptions;
use Agence104\LiveKit\RoomService;
use Agence104\LiveKit\RoomServiceClient;
use Agence104\LiveKit\VideoGrant;

class LiveKitService
{

    protected $host;
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        $this->host = config('services.livekit.url');
        $this->apiKey = config('services.livekit.key');
        $this->apiSecret = config('services.livekit.secret');
    }

    public function createAccessToken(
        string $roomName,
        string $identity,
        int $ttl = 21600
    ): string {
        // Define token options
        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity($identity)
            ->setTtl($ttl);

        // Define video grants
        $videoGrant = (new VideoGrant())
            ->setRoomJoin()
            ->setRoomName($roomName);

        // Initialize and fetch JWT Token
        return (new AccessToken($this->apiKey, $this->apiSecret))
            ->init($tokenOptions)
            ->setGrant($videoGrant)
            ->toJwt();
    }

    public function createRoom(
        string $roomName,
        int $emptyTimeout = 10,
        int $maxParticipants = 20
    ) {
        // Initialize Room Service Client
        $roomService = new RoomServiceClient(
            $this->host,
            $this->apiKey,
            $this->apiSecret
        );

        // Create room options
        $roomOptions = (new RoomCreateOptions())
            ->setName($roomName)
            ->setEmptyTimeout($emptyTimeout)
            ->setMaxParticipants($maxParticipants);

        // Create and return the room
        return $roomService->createRoom($roomOptions);
    }

    public function listRooms()
    {
        $roomService = new RoomServiceClient(
            $this->host,
            $this->apiKey,
            $this->apiSecret
        );

        return $roomService->listRooms();
    }

    public function deleteRoom(string $roomName)
    {
        $roomService = new RoomServiceClient(
            $this->host,
            $this->apiKey,
            $this->apiSecret
        );

        $roomService->deleteRoom($roomName);
    }

    public function createRestrictedAccessToken(
        string $roomName,
        string $identity,
        bool $canPublish = true,
        bool $canSubscribe = true
    ): string {
        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity($identity);

        $videoGrant = (new VideoGrant())
            ->setRoomJoin()
            ->setRoomName($roomName)
            ->setCanPublish($canPublish)
            ->setCanSubscribe($canSubscribe);

        return (new AccessToken($this->apiKey, $this->apiSecret))
            ->init($tokenOptions)
            ->setGrant($videoGrant)
            ->toJwt();
    }
}
