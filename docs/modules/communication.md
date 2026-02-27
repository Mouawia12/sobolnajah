# Communication Module

## Domain Model
- Notifications (`notifications` table, user-targeted records)
- `ChatRoom`, `ChatMessage`, `ChatParticipant`

## Main Flows
- Notification listing and mark-as-read for current user only.
- Direct/group chat room creation and messaging.
- Room read-state updates (`last_read_at`, `last_read_message_id`).

## Permissions
- Authenticated access only.
- Chat room access controlled by membership (`ChatRoomPolicy`).
- Cross-user notification mutation is forbidden.
