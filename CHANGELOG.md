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


