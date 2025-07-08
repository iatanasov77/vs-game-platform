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
```
sudo service websocket_game_platform_game restart
sudo service websocket_game_platform_chat restart
```

## Check Websocket Connections
```
telsocket -url wss://game-platform.lh/wss2/game/:8092
telsocket -url wss://game-platform.lh/wss2/chat/:8091
```

## Debug Websocket Communication
```
sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket_game.log
sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket_chat.log
```
