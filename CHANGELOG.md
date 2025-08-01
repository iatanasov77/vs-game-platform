0.15.5	|	Release date: **02.08.2025**
============================================
* New Features:
  - Update AdminPanel Theme Webpack Config About New Path Aliases.


0.15.4	|	Release date: **26.07.2025**
============================================
* New Features:
  - Create a QueryParamsService in Frontend to Store and Retrieve Query Params.
* Bug-Fixes and Improvements:
  - Improve Game Start Process.
  - Fix HintMovesActionDto and DoublingActionDto .
  - Fix DoublingAction .


0.15.3	|	Release date: **25.07.2025**
============================================
* Bug-Fixes and Improvements:
  - Improve Ending a Game.
  - Add a Feature to Remove a Game When Total Think Time Elapsed.
  - Fixing New Game Action
  - Make Start and Accept Invited Game.
  - Fixing and Improving Backgammon Board Styles for Flipped and Rotated Boards.
  - Some Fixes on Backgammon AI Engine When Generationg Moves.
  - Add More Fixes for Backgammon AI Engine and Fix Setting Opponent Winner.
  - Improve Status Message Styling and add Some Debug Logs For Game Moves and Points.
  - Fix Create and Accept Invite Games.


0.15.2	|	Release date: **19.07.2025**
============================================
* Bug-Fixes and Improvements:
  - Move Backgammon Menu and Invite Game Buttons into BoardButtonsComponent .
  - Fix Game Restore From Cookie.
  - Try to Create an Exit Game Action.
  - Fix and Improve Backgammon Board Actions.
  - Fix and Improve Generationg Engine Moves Sequences.
  - Make AI Engine Websocket Messages Async.
  - Fixing Ending a Game.


0.15.1	|	Release date: **15.07.2025**
============================================
* New Features:
  - Create an Angular Login Dialog.
* Bug-Fixes and Improvements:
  - Fix Calculating Game Player Points Left Value.
  - Fire Resize Event on Backgammon Lobby.
  - Improve Backgammon Variants.
  - Allow API Routes for Registered Users.


0.15.0	|	Release date: **10.07.2025**
============================================
* Bug-Fixes and Improvements:
  - Fix and Improve GameManager::DoMoves .
  - Improve Backgammon Board 'drawHomes' Method.
  - Improve GamePlayer Entity.
  - Many Fixes and Inprovements on Backgammon Game Playing.


0.14.3	|	Release date: **08.07.2025**
============================================
* Bug-Fixes and Improvements:
  - Fix Sending Moves by Black Player in Playing with AI.
  - Fixing Backgammon Engine.
  - Add More Fixes FOR Backgammon AI.
  - Refactoring of Initializing Backgammon AI.
  - Refactoring of All Backgammon Game CSS Styles.
  - Fix GamePlatform Theme CSS Styles.


0.14.2	|	Release date: **29.06.2025**
============================================
* New Features:
  - Update Readme File.


0.14.1	|	Release date: **28.06.2025**
============================================
* New Features:
  - Refactoring Angular Websocket Services.
  - Update Readme File.


0.14.0	|	Release date: **27.06.2025**
============================================
* New Features, Fixes and Improvements:
  - Add a fix and Some Improvements.
  - Start Debugging Backgammon AI Engine.
  - Refactoring of Backgammon Game Rules
  - Improve Backgammon Board Buttons.
  - Create a Helper Class for Some Heper Methods that Used in Game Rules.
  - Import zone-error ZoneJs Plugin For easier debugging in development mode.
  - Change Angular Translations from API Call to Json Files.


0.13.1	|	Release date: **14.06.2025**
============================================
* Bug-Fixes, Improvements and Refactoring:
  - Improve Jenkinsfile
  - Move AI Engines at Different Namespace.
  - Add another option for debugging GameManager .
  - Create Serializer Denormalizers for DTO Objects.
  - Create a Custom Logger for Games.
  - Fix Initializing Current Player on Game Creating.
  - Fix IsBearingOff Method of Game.
  - Improve Configuration of Game Logger.
  - Fixing Generate Moves on First Throw.
  - Refactoring of Backgammon Rules Classes.
  - Make Custom Serializer Denormalizers to works.


0.13.0	|	Release date: **10.06.2025**
============================================
* New Features:
  - Fix all Entities and Doctrine Configs about Doctrine ORM 3.0
  - Update to Vankosoft Core 1.13


0.12.4	|	Release date: **06.06.2025**
============================================
* New Features:
  - Improve Jenkinsfile .
  - Some Fixes on GameManager and Backgamon AiEngine.
  - Update Seome Composer Requirements.


0.12.3	|	Release date: **05.06.2025**
============================================
* Bug-Fixes and Improvements:
  - Make Adding Exception Trace in Websocket Server Logs Conditional.
  - Many Fixes on Backgammon Rules and DTO Classes to Achieve to Roll Dices.
  - Add Missing Method in GameManager .


0.12.2	|	Release date: **03.06.2025**
============================================
* Bug-Fixes:
  - Fix Websocket Servers.
  - Fix Monolog Configuration in API Application.


0.12.1	|	Release date: **03.06.2025**
============================================
* Bug-Fixes and Improvements:
  - Refactoring of Game Variants Components.
  - Improve Top of Application Layout.
  - Improve Board Games Message Component.
  - Fix Websocket Chat Server.
  - Fix GameManager When it Create and Start Games.
  - Fix Some Deprecations in Backgammon Rules Classes.
  - Create Games to Have Child Games (Variants)
  - Use Backgammon Game Variants in Websocket Game Service.
  - Fix Backgamon Game to Achieve Rolling Dices.
  - Add Some Buttons into Backgammon Board Buttons.
  - Improve Websocket Components to Add to Log Only in DEV Environement.


0.12.0	|	Release date: **01.06.2025**
============================================
* Bug-Fixes and Improvements:
  - Improve Logging of Websocket Communication.
  - Fixing GameManager::DoAction Method.
  - Hide Lobby Buttons on Play Game.
  - Set What to Show When is in Lobby of Backgammon.
  - Fix Display Backgamon Board Players.
  - Improve Backgammon Game CSS Styles.
  - Create Some More Improvements.


0.11.3	|	Release date: **27.05.2025**
============================================
* Bug-Fixes and Improvements:
  - Fix Many SASS Warnings.
  - Fix Display Play AI Question Dialog.


0.11.2	|	Release date: **25.05.2025**
============================================
* Bug-Fixes and Improvements:
  - Fix Jenkins file to create env files before to start phing build.
  - Fix ENV Files Api and Websocket Url's Vars.
  - Remove All Tests with Websockets.
  - Create Games to Use SSL Websockets.


0.11.1	|	Release date: **22.05.2025**
============================================
* New Features:
  - Create Binary Commands That Starts Websocket Servers for Production.


0.11.0	|	Release date: **21.05.2025**
============================================
* New Features:
  - Update Frontend WebPack Encore Version and Dependencies.
  - Update to VankoSoft Application 1.11
  - Remove All Tests with Thruway WAMP and ZMQ.
  - Add Catalog, Payment and Subscriptions Extensions.
  - Update to Vankosoft 1.12  Core and Extensions.


0.10.6	|	Release date: **07.11.2024**
============================================
* New Features and Improvements:
  - Refactoring of Backgammon Game Rules.
  - Start Backgammon Game From Invite a Friend Dialog.
  - Move Backgammon AI Classes into Rules Namespace.
  - Improve WebSocket Server and Client Services.
  - Refactoring of WebSocket Communication and Create StartGamePlay Actions.
  - Remove Old Theme From Docs.


0.10.5	|	Release date: **04.11.2024**
============================================
* Bug-Fixes and Improvements:
  - Move Backgammon Variants From Side Bar Component to Game Board Top Menu.
  - Fix Backgammon Dices Positioning and Styling.
  - Fixing Backend PlayerDto Class.
  - Resolving Players Avatars From Liip Imagine Cache in Angular Frontend.
  - Improve BoardPlayer Component Template.


0.10.4	|	Release date: **03.11.2024**
============================================
* New Features:
  - Create Different Backgammon Variants.
  - Hide Busy Spinner on Some Status Mesages.
  - Translate some Angular Status Mesages.
  - Select Game Room for Backgammon Game From Cookie.
  - Create a 'BoardPlayerComponent' to show at left of Backgammon GameBoard.
* Bug-Fixes and Improvements:
  - Fix Depend of FormsModule in GameDialogsModule.
  - Fix and Improve Backgammon Board.
  - Many Improvements.
  - Fix 'Invite a Friend' Dialog.
  - Fix Reconnecting from Cookie.
  - Fix BackGammon Board Canvas Size and Drawing.


0.10.3	|	Release date: **31.10.2024**
============================================
* New Features , Improvements and Refactoring:
  - Send Query Params to Backgammon Game.
  - Create a Separate Angular Component for Backgammon Board Buttons.
  - Add Tutorial Service for Backgammon Game.
  - Create a Game Chat Component.
  - Move Some Angular Components From GameBoards to SideBars Module.
  - Create GameDialogsModule and Move Angular Components that are dialogs to this Module.
  - Create Invite Angular Component.


0.10.2	|	Release date: **24.10.2024**
============================================
* New Features and Improvements:
  - Add Play AI Question Dialog.
  - Finishing WebSocket Comunication.
  - Many WebSocket Comunnication Improvements.
  - Add Sound Mute Component.


0.10.1	|	Release date: **13.10.2024**
============================================
* New Features , Fixes and Improvements:
  - Create WebsocketServerCommand .
  - Replace Voryx ThruwayBundle with Vankosoft Thruway Bundle.
  - Fix All Packages about Sylius Resource Bundle 1.12
  - Create a WebSocket Server for Games.
  - Add Websocket Game Url into service configs.
  - Refactoring Game Service and Try to Get Game Cookie on WebSocket Open.
  - Fix Game Sound Service to Find Sound Files.
  - Finalize Backend Backgamon Game Manager.


0.10.0	|	Release date: **08.10.2024**
============================================
* New Features:
  - Many Big Refactoring.
  - Use Thruway Bundle to Create a Wamp Client Command.
  - Create Separate Websocket Test Pages Fifferent From WAMP/ZMQ Test With Autobahn.
  - Create Test Websocket Chat.


0.9.7	|	Release date: **05.10.2024**
============================================
* New Features, Fixes and Omprovements:
  - Improve BoardButtonsComponent .
  - Create an Angular Component 'CreateGameRoomDialogComponent'.
  - Porting Backgammon Backend from C# Code.
  - Optimize Doctrine Migrations.
  - Create Test WebSocket.
  - Separate Websocket and ZMQ Socket Services.
  - Improve WebsocketClientFactory .
  - Fix Backgamon Backend Code.
  - Migrate to Wamp V2.
  - Improve Test Websocket.


0.9.6	|	Release date: **14.09.2024**
============================================
* New Features:
  - Connect Angular socket service to WebSocket Server.
  - Add a WebSocket Backend Client.
  - Add Facebook Login.
  - Add Google Login.
  - Styling Game Boards and Backgammon Board Buttons.
  - Create a Select Game Room Dialog.
  - Refactoring of Backgammon Game Container.


0.9.5	|	Release date: **10.09.2024**
============================================
* New Features and Improvements:
  - Separate GameBoards Components in Another Module.
  - Create a Base Angular Component to Extend from All Games.
  - Refactoring of Games Controllers.
  - Upgrade to Angular 17.
  - Replace Restangular with Http Client.
  - Create a Chess Board for Chess Game.
  - Fix GamePlay Mapping on StartGame Angular Service.
  - Update Frontent Packages.
  - Set AdminPanel Menu Icons on Custom Resources.
  - Create Game 'Svara'.
  - Create a Backgammon Board.
  - Refactoring All Games Templates.
  - Create a Container Component for Backgammon Game.


0.9.4	|	Release date: **05.09.2024**
============================================
* New Features and Improvements:
  - Create API Endpoint for Finish Game Action.
  - Improve Sidebar Components.
  - Initialize Bridge Belote Players from Server API.
  - Improve GamePlatform Frontend Library.
  - Create a GamePlatformSettings Resource and use it in Game Initialization.


0.9.3	|	Release date: **03.09.2024**
============================================
* New Features and Improvements:
  - Improve Game Sidebar Components.
  - Add isPlaying Status on GameRoom Entity.
  - Move Angular Game Interfces into GamePlatform Library.


0.9.2	|	Release date: **30.08.2024**
============================================
* New Features and Improvements:
  - Centering Loader Component in Component that is Used.
  - Create a GamePlay Entity for Game Sessions and a Game Rooms Component in frontend to Show Available Rooms.
  - Refactoring Typescript Game Interfaces.
  - Create a New NgRx Store Action for Select Cureent Game Room.
  - Create User Activity Listener.
  - Create Clear Innactive Players Command.
  - Initialize Game with GameRoom on Start New Game.
  - Refactoring of Angular Services.


0.9.1	|	Release date: **27.08.2024**
============================================
* New Features and Improvements:
  - Create Types for Game Provider.
  - Move Creation of Api Verification Signature in Vankosoft Application Bundle.
  - Use FlatIcons Lib For Announce Symbols.
  - Create a Game Player Component to List Players on Side of Game Board.
  - Create a MercureConnection Entity to Store Active Players.
  - Create a EventSource Service to Receive Mercure Hub Messages and Subscribe to It.
  - Load Player For Current User or Show Link to Create it If Not Exists.
  - Show Connected Players in Players Side Bar.
  - Remove 'connections' From App State.
  - Improve Login / Logout Listeners that publish on Mercure.


0.9.0	|	Release date: **22.08.2024**
============================================
* New Features and Improvements:
  - Add Using Mercure for Server-Sent Events.
  - Refactoring BridgeBelote Game Initialization.
  - Load Game on Start Application and Improve App Authentication.
  - Improve Game Announce Component.


0.8.4	|	Release date: **19.08.2024**
============================================
* Bug-Fixes and Improvements:
  - Refactoring of All NgRx Store Classes.
  - Angular Application Login By Signature is Done.
  - Improve Founf Theme Build Path.
  - Add a a Feature State Store for Game Reducers.
  - Rename GamePlatform Application Controllers Namespace.
  - Improve Login Forms.


0.8.3	|	Release date: **04.08.2024**
============================================
* New Features:
  - Create Login/Logout Angular Application on Symfony Login/Logout Actions.
  - Make User Registration.
  - Create a GameRoom Resource.
  - Create a GamePlayer Resource.
  - Use VankoSoft API Credentials from ENV Variables.
  - Add GameRoom and GamePlayer API Resources.
* Bug-Fixes and Improvements:
  - Improve API Configuration.
  - Fix All AdminPanel Forms.
  - Refactoring of Angular Components.


0.8.2	|	Release date: **01.08.2024**
============================================
* New Features and Improvements:
  - Move Card Game Table into Shared Modules.
  - Improve User Entity With API Verify Signature.
  - Remove Loading API Bundles in Main Application.
  - Improve WebPackEncore Configs.
  - Add an Application Games Controller.
  - Improve Application Menu Configs.
  - Add Translation Feature in Angular.
  - Fix Application DefaultController Routes.
  - Improve Static Pages.
  - Remove Authentication Modules From Angular Application.
  - Translate All Strings in Angular Application.


0.8.1	|	Release date: **28.07.2024**
============================================
* New Features:
  - Add A Velzon Theme for Game Platform Application.
  - Load AdminPanel Assets From VelzonTheme Build.
* Bug-Fixes and Improvements:
  - Improve AdminPanel GameForm .
  - Fix Assets Path in SPA Theme.


0.8.0	|	Release date: **27.07.2024**
============================================
* New Features and Improvements:
  - Litle Improvements.
  - Imrove and RefactoringTheme.
  - Improve Footer.
  - Create New SPA Theme.
  - Move Admin Panel Custom Entries into AdminPanelVelzon Theme.
* Bug-Fixes:
  - Fix GameController.


0.7.2	|	Release date: **08.06.2024**
============================================
* Bug-Fixes:
  - Fix Doctrine Migrations For Production Server.


0.7.1	|	Release date: **08.06.2024**
============================================
* New Features and Improvements:
  - Update Deployment Scripts.


0.7.0	|	Release date: **08.06.2024**
============================================
* New Features:
* Bug-Fixes:
* Commits:


0.7.0	|	Release date: **08.06.2024**
============================================
* New Features:
  - Use Login By Signature in Games When is Needed.
  - Use Login By Signed Url in Angular Applications.
  - Add an AppConstants Class in Angular Application.
  - Add to AdminPanel Twig Globals.
  - Display Application Links into AdminPanel Side Menu.
  - Add Player Borders in Card Table Only in Developement Builds.
  - Render Login Form Modal Only If User is Not Logged In.
  - Add to .gitignore
  - Improve Phing Build Config.
  - Improve Doctrine Config for Production Environement.
  - Update Application Required Packages About New Versions of VankoSoft Bundles.
  - Separate API into Different Application.
  - Add Some Developement Routes.
  - Create an API Route to get User Signature.
  - Improve Bridge Belote Component View.
  - Refactoring of Game Controllers.
  - Improve Security Configs and GamePlatform Angular Context.


0.6.5	|	Release date: **18.05.2023**
============================================
* New Features:
  - Update Vankosoft Api Bundle.
  - Add API Docs Assets.


0.6.4	|	Release date: **17.05.2023**
============================================
* Bug-Fixes and Improvements:
  - Update Package @types/node
  - Fix Frontend Library Types.
  - Fix Typescript Warnings.


0.6.3	|	Release date: **17.05.2023**
============================================
* New Features and Improvements:
  - Update Jenkinsfile
  - Game BridgeBelote Add Kontra Announces.
  - Add RXJS Store and Api Services.
  - Rafactoring of Application Theme Assets
  - Create an Authentication Module in Application Theme Scripts.
  - Add Config For Lexic JWT Authentication Bundle.
  - Add Config For JWT Refresh Token.
* Bug-Fixes:
  - Fix Open Every Game as Different Angular Application.
  - Angular Inject Auth Service.
  - Fix Angular Dependencie Injection.


0.6.2	|	Release date: **03.05.2023**
============================================
* Bug-Fixes and Improvements:
  - Add Compression Plugin for Webpack Encore..
  - Remove Compression Plugin for Webpack Encore.
  - Improve tsconfig
  - Update Frontend Packages.


0.6.1	|	Release date: **26.04.2023**
============================================
* New Features:
  - Copy Cards Images From library to public Shared Assets Dir.
  - Remove Bootstrap 4 Twgig Form Theme.
  - Remove Assets includes Folder From Themes.
  - Update Frontend Packages.
  - Remove Sensio Extra Bundle.


0.6.0	|	Release date: **22.01.2023**
============================================
* New Features:
  - Add Missing AdminPanel Translation.
  - Add Angular Dependencies.
  - Add Cookie Consent.
  - Add AngularCompiler into Webpack Config In AngularJs Theme.
  - Create Angular Application For AngularJs Theme.
  - Refactoring Angular Application.
  - Display Player Annonces.
  - Create a GamePlayersIterator
  - Test BeloteCardGame Events.


0.5.0	|	Release date: **08.01.2023**
============================================
* New Features:
  - Upgrade to Symfony 6 adn PHP 8
  - Load AdminPanel Assets From Vankosoft ApplicationBundle.
  -  Add an AngularJs Theme.
  - Add Admin Panel Custom Entries and Custom Pages.
* Bug-Fixes:
  - Fix a Doctrine Migration.


0.4.0	|	Release date: **27.11.2022**
============================================
* New Features:
  - Add ApiBundle.
* Bug-Fixes:
  - Fix security config.


0.3.0	|	Release date: **27.11.2022**
============================================
* New Features and Improvements:
  - Improvement
  - Player Announce Handler.
  - Improve Player Announce Handler.
  - Add JWT API Authentication into AdminPanel.
  - Prepare For Deployment.


0.2.0	|	Release date: **25.10.2022**
============================================
* New Features:
  - Separate Styles for Bridge Belote Game.
  - Separate CSS Styles for Card Games.
  - Add Delays For Announces.
  - Add Game URL Field in Game Entity.
  - Add Game Start Event and Use It.

* Bug-Fixes:
  - Fix Styling of CardGame Board.
  - Fix Player Click Handler.

* Remove Game Applications:
  - Move CardGame Applications to GamePlatform Application.
  - Fix Bottom Cards Position.
  - Improve Games Controllers.
  - Fix Game Layout Template.


0.1.0	|	Release date: **24.10.2022**
============================================
* New Features and Improvements:
  - Improve Card Game Board.
  - Allow Deployment of file VERSION.
  - Create Base JS Classes For a CardGame Algorithms.
  - Many Work For Beleote Card Game.
  - Create Bridge Belote as React Application.
  - Multiple Improvements.
  - Create Custom Event for Announce.
* Bug-Fixes:
  - Fix Admin Panel Resource Template.
  - Fix Announce Id.


0.0.1	|	Release date: **18.10.2022**
============================================
* New Features:
  - Initial First Version.


