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

## Debug Websocket Communication
```
sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket_game.log
sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket_chat.log
```
