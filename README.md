# VS Game Platform

## Build Backend
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Build Frontend
```
yarn install --no-bin-links
yarn run dev
```

## Websocket Servers

### Test Websocket Servers
```
sudo service websocket_test_websocket_ratchet_message restart
sudo service websocket_test_websocket_swoole_message restart
```

### Ratchet Websocket Game Servers
```
sudo service websocket_game_platform_game restart
sudo service websocket_game_platform_chat restart
```

### Swoole Websocket Game Servers
```
sudo service websocket_game_platform_swoole_game restart
sudo service websocket_game_platform_swoole_chat restart
```

## Check Websocket Connections

### Test Websocket Servers
```
telsocket -url wss://game-platform.lh/wss2/test-ratchet/:8191
telsocket -url wss://game-platform.lh/wss2/test-swoole/:8195
```
### Ratchet Websocket Game Servers
```
telsocket -url wss://game-platform.lh/wss2/game/:8092
telsocket -url wss://game-platform.lh/wss2/chat/:8091
```

### Swoole Websocket Game Servers
```
telsocket -url wss://game-platform.lh/wss2/game-swoole/:8094
telsocket -url wss://game-platform.lh/wss2/chat-swoole/:8093
```

## Debug Websocket Communication
```
sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket_game.log
sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket_chat.log

sudo tail -f /dev/shm/game-platform.lh/game-platform/log/test_websocket.log
```
