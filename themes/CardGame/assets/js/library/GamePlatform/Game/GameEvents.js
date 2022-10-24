/**
 * Great Tutorial: https://blog.logrocket.com/custom-events-in-javascript-a-complete-guide/
 */

export const PLAYER_ANNOUNCE_EVENT_NAME = "player-announce";

export const playerAnnounce = new CustomEvent( PLAYER_ANNOUNCE_EVENT_NAME, {
    detail: {},
    bubbles: true,
    cancelable: true,
    composed: false,
});
