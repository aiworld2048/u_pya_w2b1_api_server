<?php

namespace App\Http\Controllers\Api\V1\Game\Buffalo;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use App\Services\BuffaloGameService;
use App\Models\LogBuffaloBet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BuffaloGameController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Buffalo Game - Get User Balance
     * Endpoint: POST /api/buffalo/get-user-balance
     */
    public function getUserBalance(Request $request)
    {
        Log::info('W2B1 Buffalo getUserBalance - Request received', [
            'request' => $request->all(),
            'ip' => $request->ip()
        ]);

        $request->validate([
            'uid' => 'required|string|max:50',
            'token' => 'required|string',
        ]);

        $uid = $request->uid;
        $token = $request->token;

        // Log received payload for debugging
        Log::info('W2B1 Buffalo getUserBalance - Payload received', [
            'received_uid' => $uid,
            'received_token' => substr($token, 0, 20) . '...' . substr($token, -10), // Show first 20 and last 10 chars
            'token_length' => strlen($token),
            'ip' => $request->ip(),
        ]);

        // Extract username from UID first to generate expected values
        $userName = BuffaloGameService::extractUserNameFromUid($uid);
        
        if ($userName) {
            // Generate expected UID and token for comparison
            $expectedUid = BuffaloGameService::generateUid($userName);
            $expectedToken = BuffaloGameService::generatePersistentToken($userName);
            
            // Log expected values for debugging
            Log::info('W2B1 Buffalo getUserBalance - Expected values', [
                'extracted_username' => $userName,
                'expected_uid' => $expectedUid,
                'expected_token' => substr($expectedToken, 0, 20) . '...' . substr($expectedToken, -10),
                'uid_match' => $uid === $expectedUid,
                'token_match' => hash_equals($expectedToken, $token),
            ]);
        } else {
            Log::warning('W2B1 Buffalo getUserBalance - Could not extract username from UID', [
                'received_uid' => $uid,
            ]);
        }

        // Verify token
        Log::info('W2B1 Buffalo - Token verification attempt', [
            'uid' => $uid,
            'token_preview' => substr($token, 0, 20) . '...'
        ]);
        
        if (!BuffaloGameService::verifyToken($uid, $token)) {
            Log::warning('W2B1 Buffalo - Token verification failed', [
                'uid' => $uid,
                'received_token' => substr($token, 0, 20) . '...' . substr($token, -10),
                'extracted_username' => $userName ?? 'N/A',
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid token',
            ]);
        }
        
        Log::info('W2B1 Buffalo - Token verification successful', [
            'uid' => $uid,
            'username' => $userName
        ]);

        if (!$userName) {
            Log::warning('W2B1 Buffalo - Could not extract username', [
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid UID format',
            ]);
        }

        // Find user by username
        $user = User::where('user_name', $userName)->first();
        
        if (!$user) {
            Log::warning('W2B1 Buffalo - User not found', [
                'userName' => $userName,
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'User not found',
            ]);
        }

        // Get balance (assuming you use bavix/laravel-wallet)
        $balance = $user->balanceFloat;

        Log::info('W2B1 Buffalo - Balance retrieved successfully', [
            'user' => $userName,
            'balance' => $balance
        ]);

        // Return balance as integer (Buffalo provider expects integer only)
        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'balance' => (int) $balance,
        ]);
    }

    /**
     * Buffalo Game - Change Balance (Bet/Win)
     * Endpoint: POST /api/buffalo/change-balance
     */
    public function changeBalance(Request $request)
    {
        Log::info('W2B1 Buffalo changeBalance - Request received', [
            'request' => $request->all(),
            'ip' => $request->ip()
        ]);

        // Handle both form data and JSON (API docs specify form, but support both)
        $request->validate([
            'uid' => 'required|string|max:50',
            'bet_uid' => 'required|string', // Unique bet identifier for idempotency
            'token' => 'required|string',
            'changemoney' => 'required|integer',
            'bet' => 'required|integer',
            'win' => 'required|integer',
            'gameId' => 'nullable|integer', // Support both gameId and gameld
            'gameld' => 'nullable|integer', // API docs typo, but handle it
            'roomId' => 'nullable|integer', // Support both roomId and roomld
            'roomld' => 'nullable|integer', // API docs typo, but handle it
        ]);

        $uid = $request->uid;
        $token = $request->token;
        $betUid = $request->bet_uid;
        
        // Handle parameter name variations (gameld/gameId, roomld/roomId)
        $gameId = $request->gameId ?? $request->gameld ?? null;
        $roomId = $request->roomId ?? $request->roomld ?? null;

        // Log received payload for debugging
        Log::info('W2B1 Buffalo changeBalance - Payload received', [
            'received_uid' => $uid,
            'received_token' => substr($token, 0, 20) . '...' . substr($token, -10), // Show first 20 and last 10 chars
            'token_length' => strlen($token),
            'bet_uid' => $betUid,
            'changemoney' => $request->changemoney,
            'bet' => $request->bet,
            'win' => $request->win,
            'game_id' => $gameId,
            'room_id' => $roomId,
            'ip' => $request->ip(),
        ]);

        // Extract username from UID first to generate expected values
        $userName = BuffaloGameService::extractUserNameFromUid($uid);
        
        if ($userName) {
            // Generate expected UID and token for comparison
            $expectedUid = BuffaloGameService::generateUid($userName);
            $expectedToken = BuffaloGameService::generatePersistentToken($userName);
            
            // Log expected values for debugging
            Log::info('W2B1 Buffalo changeBalance - Expected values', [
                'extracted_username' => $userName,
                'expected_uid' => $expectedUid,
                'expected_token' => substr($expectedToken, 0, 20) . '...' . substr($expectedToken, -10),
                'uid_match' => $uid === $expectedUid,
                'token_match' => hash_equals($expectedToken, $token),
            ]);
        } else {
            Log::warning('W2B1 Buffalo changeBalance - Could not extract username from UID', [
                'received_uid' => $uid,
            ]);
        }

        // Verify token
        Log::info('W2B1 Buffalo - Token verification attempt', [
            'uid' => $uid,
            'token_preview' => substr($token, 0, 20) . '...',
            'bet_uid' => $betUid
        ]);
        
        if (!BuffaloGameService::verifyToken($uid, $token)) {
            Log::warning('W2B1 Buffalo - Token verification failed', [
                'uid' => $uid,
                'received_token' => substr($token, 0, 20) . '...' . substr($token, -10),
                'extracted_username' => $userName ?? 'N/A',
                'bet_uid' => $betUid,
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid token',
            ]);
        }
        
        Log::info('W2B1 Buffalo - Token verification successful', [
            'uid' => $uid,
            'username' => $userName,
            'bet_uid' => $betUid
        ]);

        if (!$userName) {
            Log::warning('W2B1 Buffalo - Could not extract username', [
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid UID format',
            ]);
        }

        // Find user
        $user = User::where('user_name', $userName)->first();
        
        if (!$user) {
            Log::warning('6TriBet Buffalo - User not found', [
                'userName' => $userName,
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'User not found',
            ]);
        }

        // Idempotency Check: Prevent duplicate processing using bet_uid
        $existingBet = LogBuffaloBet::where('bet_uid', $betUid)->first();
        if ($existingBet) {
            Log::info('W2B1 Buffalo - Duplicate bet_uid detected, returning existing result', [
                'bet_uid' => $betUid,
                'user' => $user->user_name
            ]);
            
            // Return success with current balance in cents (as per API docs)
            $user->refresh();
            $balanceInCents = (int) ($user->balanceFloat * 100);
            
            return response()->json([
                'code' => 1,
                'msg' => (string) $balanceInCents, // API docs: msg contains "User balance in cents"
            ]);
        }

        // Get amounts
        $changeAmount = (int) $request->changemoney;
        $betAmount = abs((int) $request->bet);
        $winAmount = (int) $request->win;

        Log::info('W2B1 Buffalo - Processing transaction', [
            'user_name' => $user->user_name,
            'user_id' => $user->id,
            'bet_uid' => $betUid,
            'change_amount' => $changeAmount,
            'bet_amount' => $betAmount,
            'win_amount' => $winAmount,
            'game_id' => $gameId,
            'room_id' => $roomId
        ]);

        try {
            DB::beginTransaction();

            // Handle transaction
            if ($changeAmount > 0) {
                // Win/Deposit transaction
                $success = $this->walletService->deposit(
                    $user,
                    $changeAmount,
                    TransactionName::GameWin,
                    [
                        'buffalo_game_id' => $gameId,
                        'bet_amount' => $betAmount,
                        'win_amount' => $winAmount,
                        'bet_uid' => $betUid,
                        'room_id' => $roomId,
                        'provider' => 'buffalo',
                        'transaction_type' => 'game_win'
                    ]
                );
            } else {
                // Loss/Withdraw transaction
                $success = $this->walletService->withdraw(
                    $user,
                    abs($changeAmount),
                    TransactionName::GameLoss,
                    [
                        'buffalo_game_id' => $gameId,
                        'bet_amount' => $betAmount,
                        'win_amount' => $winAmount,
                        'bet_uid' => $betUid,
                        'room_id' => $roomId,
                        'provider' => 'buffalo',
                        'transaction_type' => 'game_loss'
                    ]
                );
            }

            if (!$success) {
                DB::rollBack();
                
                Log::error('W2B1 Buffalo - Wallet transaction failed', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                    'change_amount' => $changeAmount
                ]);
                
                return response()->json([
                    'code' => 0,
                    'msg' => 'Transaction failed',
                ]);
            }

            // Refresh user model
            $user->refresh();

            Log::info('W2B1 Buffalo - Transaction successful', [
                'user_id' => $user->id,
                'user_name' => $user->user_name,
                'bet_uid' => $betUid,
                'change_amount' => $changeAmount,
                'new_balance' => $user->balanceFloat
            ]);

            // Log the bet
            $this->logBuffaloBet($user, $request->all(), $betUid, $gameId, $roomId);

            DB::commit();

            // API docs specify: msg should contain "User balance in cents"
            $balanceInCents = (int) ($user->balanceFloat * 100);

            return response()->json([
                'code' => 1,
                'msg' => (string) $balanceInCents, // User balance in cents as per API docs
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('W2B1 Buffalo - Transaction error', [
                'user_name' => $user->user_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'code' => 0,
                'msg' => 'Transaction failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Log Buffalo bet for reporting
     */
    private function logBuffaloBet(User $user, array $requestData, string $betUid, ?int $gameId, ?int $roomId): void
    {
        try {
            LogBuffaloBet::create([
                'member_account' => $user->user_name,
                'bet_uid' => $betUid,
                'player_id' => $user->id,
                'player_agent_id' => $user->agent_id,
                'buffalo_game_id' => $gameId ?? $requestData['gameId'] ?? $requestData['gameld'] ?? null,
                'room_id' => $roomId ?? $requestData['roomId'] ?? $requestData['roomld'] ?? null,
                'request_time' => now(),
                'bet_amount' => abs((int) $requestData['bet']),
                'win_amount' => (int) $requestData['win'],
                'payload' => $requestData,
                'game_name' => 'Buffalo Game',
                'status' => 'completed',
                'before_balance' => $user->balanceFloat - ($requestData['changemoney'] ?? 0),
                'balance' => $user->balanceFloat,
            ]);

            Log::info('W2B1 Buffalo - Bet logged successfully', [
                'user' => $user->user_name,
                'bet_uid' => $betUid,
                'game_id' => $gameId
            ]);

        } catch (\Exception $e) {
            Log::error('W2B1 Buffalo - Failed to log bet', [
                'error' => $e->getMessage(),
                'user' => $user->user_name,
                'bet_uid' => $betUid
            ]);
        }
    }

    /**
     * Generate Buffalo game authentication data for frontend
     */
    public function generateGameAuth(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ]);
        }

        $sitePrefix = $this->resolveSitePrefix($request->input('site_prefix'));

        $auth = BuffaloGameService::generateBuffaloAuth($user, $sitePrefix);
        $availableRooms = BuffaloGameService::getAvailableRooms($user);
        $roomConfig = BuffaloGameService::getRoomConfig();

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'auth' => $auth,
                'available_rooms' => $availableRooms,
                'all_rooms' => $roomConfig,
                'user_balance' => $user->balanceFloat,
                'site_prefix' => $sitePrefix,
            ],
        ]);
    }

    /**
     * Resolve the requested site prefix to a configured value.
     */
    private function resolveSitePrefix(?string $sitePrefix): string
    {
        $defaultPrefix = Config::get('buffalo.site.prefix', 'W2B1');

        if (!$sitePrefix) {
            return $defaultPrefix;
        }

        $sites = Config::get('buffalo.sites', []);

        foreach ($sites as $key => $config) {
            $configuredPrefix = $config['prefix'] ?? $key;

            if (strtolower((string) $key) === strtolower($sitePrefix)) {
                return $configuredPrefix;
            }

            if (strtolower($configuredPrefix) === strtolower($sitePrefix)) {
                return $configuredPrefix;
            }
        }

        return $defaultPrefix;
    }

    /**
     * Generate Buffalo game URL for direct launch
     */
    public function generateGameUrl(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer|min:1|max:4',
            'lobby_url' => 'nullable|url',
            'game_id' => 'nullable|integer|in:23,42', // 23 = normal buffalo, 42 = scatter buffalo
            'type_id' => 'nullable|integer',
            'provider_id' => 'nullable|integer',
            'site_prefix' => 'nullable|string|max:10',
        ]);

        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ]);
        }

        $sitePrefix = $this->resolveSitePrefix($request->input('site_prefix'));
        $roomId = (int) $request->room_id;
        $gameId = (int) ($request->game_id ?? Config::get('buffalo.api.game_id', 23));
        $typeId = (int) ($request->input('type_id') ?? Config::get('buffalo.provider_launch.type_id', 1));
        $providerId = (int) ($request->input('provider_id') ?? Config::get('buffalo.provider_launch.provider_id', 23));
        //$lobbyUrl = $request->input('lobby_url') ?? BuffaloGameService::getLobbyUrl($sitePrefix);
        $lobbyUrl = Config::get('buffalo.site.lobby_url', ''); // https://online.W2B1.com

        // Check if user has sufficient balance for the room
        $availableRooms = BuffaloGameService::getAvailableRooms($user);
        $allRooms = BuffaloGameService::getRoomConfig();
        
        if (!isset($availableRooms[$roomId])) {
            return response()->json([
                'code' => 0,
                'msg' => 'Insufficient balance for selected room',
            ]);
        }

        try {
            $providerResponse = BuffaloGameService::requestExternalLaunch($user, [
                'type_id' => $typeId,
                'provider_id' => $providerId,
                'game_id' => $gameId,
                'room_id' => $roomId,
                'site_prefix' => $sitePrefix,
                'lobby_url' => $lobbyUrl,
            ]);

            if (($providerResponse['code'] ?? 0) != 1) {
                return response()->json([
                    'code' => $providerResponse['code'] ?? 0,
                    'msg' => $providerResponse['msg'] ?? $providerResponse['message'] ?? 'Failed to generate game URL',
                    'data' => $providerResponse,
                ]);
            }

            $gameUrl = $providerResponse['game_url']
                ?? $providerResponse['Url']
                ?? $providerResponse['url']
                ?? null;

            if (!$gameUrl) {
                return response()->json([
                    'code' => 0,
                    'msg' => 'Provider did not return a game URL',
                    'data' => $providerResponse,
                ]);
            }

            return response()->json([
                'code' => 1,
                'msg' => 'Success',
                'data' => [
                    'game_url' => $gameUrl,
                    'room_info' => $availableRooms[$roomId],
                    'site_prefix' => $sitePrefix,
                    'lobby_url' => $lobbyUrl,
                    'all_rooms' => $allRooms,
                    'provider_payload' => $providerResponse,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Buffalo generateGameUrl - Provider error', [
                'user_id' => $user->id,
                'user_name' => $user->user_name,
                'room_id' => $roomId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'code' => 0,
                'msg' => 'Failed to generate game URL: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Buffalo Game - Launch Game (Frontend Integration)
     * Compatible with existing frontend LaunchGame hook
     */
    public function launchGame(Request $request)
    {
        $request->validate([
            'type_id' => 'required|integer',
            'provider_id' => 'required|integer',
            'game_id' => 'required|integer',
            'room_id' => 'nullable|integer|min:1|max:4', // Optional room selection
            'site_prefix' => 'nullable|string|max:10',
        ]);

        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ], 401);
        }

        try {
            // Check if this is a Buffalo game request
            if ((int) $request->provider_id === 23) { // Assuming 23 is Buffalo provider ID
                $sitePrefix = $this->resolveSitePrefix($request->input('site_prefix'));

                // Get room configuration
                $roomId = $request->room_id ?? 1; // Default to room 1
                $availableRooms = BuffaloGameService::getAvailableRooms($user);
                
                // Check if requested room is available for user's balance
                if (!isset($availableRooms[$roomId])) {
                    return response()->json([
                        'code' => 0,
                        'msg' => 'Room not available for your balance level',
                    ]);
                }
                
                $roomConfig = $availableRooms[$roomId];
                $allRooms = BuffaloGameService::getRoomConfig();
                
                // Determine game type: use game_id from request or default to normal buffalo (23)
                $gameId = $request->game_id ?? Config::get('buffalo.api.game_id', 23);
                $typeId = (int) $request->type_id;

                //$lobbyUrl = BuffaloGameService::getLobbyUrl($sitePrefix);
                $lobbyUrl = Config::get('buffalo.site.lobby_url', ''); // https://online.W2B1.com
                $returnGameUrl = $lobbyUrl;
                

                // Log request payload for debugging
                Log::info('W2B1 Buffalo Game Launch - Request payload', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                    'type_id' => $typeId,
                    'provider_id' => $request->provider_id,
                    'game_id' => $gameId,
                    'room_id' => $roomId,
                    'site_prefix' => $sitePrefix,
                    'lobby_url' => $lobbyUrl,
                    'user_balance' => $user->balanceFloat ?? 0,
                ]);
                
                // Request launch URL from provider endpoint (Maxwin Myanmar)
                $providerResponse = BuffaloGameService::requestExternalLaunch($user, [
                    'type_id' => $typeId,
                    'provider_id' => (int) $request->provider_id,
                    'game_id' => $gameId,
                    'room_id' => $roomId,
                    'site_prefix' => $sitePrefix,
                    'lobby_url' => $lobbyUrl,
                ]);

                // if (($providerResponse['code'] ?? 0) != 1) {
                //     return response()->json([
                //         'code' => $providerResponse['code'] ?? 0,
                //         'msg' => $providerResponse['msg'] ?? $providerResponse['message'] ?? 'Game provider rejected request',
                //         'provider_response' => $providerResponse,
                //     ]);
                // }

                $gameUrl = $providerResponse['game_url']
                    ?? $providerResponse['Url']
                    ?? $providerResponse['url']
                    ?? null;

                if (!$gameUrl) {
                    return response()->json([
                        'code' => 0,
                        'msg' => 'Game provider did not return a URL',
                        'provider_response' => $providerResponse,
                    ]);
                }
                
                // Log::info('W2B1 Buffalo Game Launch - Success', [
                //     'user_id' => $user->id,
                //     'user_name' => $user->user_name,
                //     'room_id' => $roomId,
                //     'game_id' => $gameId,
                //     'game_url' => $gameUrl,
                //     'site_prefix' => $sitePrefix,
                //     'lobby_url' => $lobbyUrl,
                //     'return_game_url' => $returnGameUrl,
                // ]);
                
                return response()->json([
                    'code' => 1,
                    'msg' => 'Game launched successfully',
                    'Url' => $gameUrl, // Compatible with existing frontend
                    'game_url' => $gameUrl, // Game URL from provider API
                    'return_game_url' => $returnGameUrl,
                    'room_info' => $roomConfig,
                    'user_balance' => $user->balanceFloat ?? 0,
                    'site_prefix' => $sitePrefix,
                    'lobby_url' => $lobbyUrl,
                    'all_rooms' => $allRooms,
                    'provider_response' => $providerResponse,
                ]);
            }
            
            // For non-Buffalo games, you can add other provider logic here
            return response()->json([
                'code' => 0,
                'msg' => 'Game provider not supported',
            ]);
            
        } catch (\Exception $e) {
            Log::error('W2B1 Buffalo Game Launch Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Failed to launch game: ' . $e->getMessage(),
            ]);
        }
    }

    /**
 * Proxy Game Content and Resources - Complete HTTPS Solution
 */


}