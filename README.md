# VS Game Platform

## Build
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
yarn install --no-bin-links
yarn run dev
```

## Debug
```
sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket_game.log
sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket_chat.log
```
