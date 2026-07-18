<?php

/*
|--------------------------------------------------------------------------
| V5 Realtime Tuning Flags
|--------------------------------------------------------------------------
|
| Staged rollout knobs for the V5 realtime path. Defaults are off so
| flipping a flag is an explicit boss decision in .env (no surprise
| behaviour change on deploy).
|
| mute_via_rtc
|   When true, the server stops broadcasting the streaming
|   `room.cohost.mute_changed` envelope. RTC (Agora onUserMuteAudio /
|   LiveKit onTrackMuted) becomes the single source of truth for the
|   live mute delta. The DB write still happens and the per-cohost
|   `mute` flag is still hydrated through the `room.snapshot` envelope
|   on connect/reconnect, so late joiners stay correct without the
|   dual-signal race window.
|
*/

return [
    'mute_via_rtc' => env('REALTIME_MUTE_VIA_RTC', false),
];
