<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GameBalanceWithdraw;
use App\Models\Battle\Fortune\FortuneSetting;
use App\Models\Game\Fivestar\FivestarSetting;
use App\Models\Battle\TeenPattiSetting;
use App\Models\LuckyGiftSetting;
use App\Models\FruitsGamePattan;
use App\Models\FuritsPotsBackup;
use App\Models\Battle\Fortune\FortuneTray;
use App\Models\Battle\Fortune\FortunePots;
use App\Models\Battle\TeenPattiTray;
use App\Models\Battle\TeenPattiPots;
use App\Models\Game\Grady\GradySetting;
use App\Models\Game\Grady\GradyTray;
use App\Models\Game\Grady\GradyPots;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Contract\Database;
use Log;
class GameControll extends Controller
{
    
    private function updateBalance($modelClass, $balanceField, $gameName, Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'type'   => 'required|in:withdraw,deposit'
        ]);

        try {
            DB::transaction(function () use ($modelClass, $balanceField, $gameName, $request) {

                $game = $modelClass::lockForUpdate()->find(1);

                if (!$game) {
                    throw new \Exception("Game not found");
                }

                $amount = (float) $request->amount;

                // Withdraw / Deposit
                if ($request->type === 'withdraw') {

                    if ($game->$balanceField < $amount) {
                        throw new \Exception("Insufficient balance");
                    }

                    $game->$balanceField -= $amount;
                } else {
                    $game->$balanceField += $amount;
                }

                $game->save();

                // ============================
                // UPDATE OR INSERT DAILY RECORD
                // ============================

                $today = now()->toDateString();

                $existing = GameBalanceWithdraw::where('game_name', $gameName)
                    ->where('type', $request->type)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    // Add amount to existing
                    $existing->amount = bcadd($existing->amount, $amount, 2);
                    $existing->save();
                } else {
                    // Insert new
                    GameBalanceWithdraw::create([
                        'game_name' => $gameName,
                        'amount'    => $amount,
                        'type'      => $request->type,
                        'date'      => $today,
                    ]);
                }

                Log::info('Balance Updated', [
                    'game' => $gameName,
                    'type' => $request->type,
                    'amount' => $amount
                ]);
            });

            return redirect()->back()->with([
                'messege' => 'Game Balance Updated Successfully!!',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {

            Log::error('Balance Update Failed', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'messege' => $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    // =========================
    // FRUITS (friuts)
    // =========================
    public function Index(Request $request)
    {
        return $this->updateBalance(FortuneSetting::class, 'game_balance', 'friuts', $request);
    }

    public function FruitsSecIndex(Request $request)
    {
        return $this->updateBalance(FortuneSetting::class, 'second_balance', 'friuts', $request);
    }

    public function FruitsthirdIndex(Request $request)
    {
        return $this->updateBalance(FortuneSetting::class, 'third_balance', 'friuts', $request);
    }

    // =========================
    // TEEN PATTI
    // =========================
    public function TeenPattiIndex(Request $request)
    {
        return $this->updateBalance(TeenPattiSetting::class, 'game_balance', 'TeenPatti', $request);
    }

    public function TeenPattiSecIndex(Request $request)
    {
        return $this->updateBalance(TeenPattiSetting::class, 'second_balance', 'TeenPatti', $request);
    }

    public function TeenPattithirdIndex(Request $request)
    {
        return $this->updateBalance(TeenPattiSetting::class, 'third_balance', 'TeenPatti', $request);
    }

    // =========================
    // FIVE
    // =========================
    public function FiveIndex(Request $request)
    {
        return $this->updateBalance(FivestarSetting::class, 'game_balance', 'Five', $request);
    }

    // =========================
    // GREEDY
    // =========================
    public function GreedyIndex(Request $request)
    {
        return $this->updateBalance(GradySetting::class, 'game_balance', 'Greedy', $request);
    }

    public function GreedySecIndex(Request $request)
    {
        return $this->updateBalance(GradySetting::class, 'second_balance', 'Greedy', $request);
    }

    public function GreedythirdIndex(Request $request)
    {
        return $this->updateBalance(GradySetting::class, 'third_balance', 'Greedy', $request);
    }

    // =========================
    // LUCKY
    // =========================
    public function LuckyIndex(Request $request)
    {
        return $this->updateBalance(LuckyGiftSetting::class, 'balance', 'Lucky', $request);
    }
    // =========================
    // Toggle Minus Status
    // =========================
    public function GameMinusStatus()
    {
        $game = FortuneSetting::find(1);
        if ($game) {
            $game->game_minus_status = $game->game_minus_status == 1 ? 0 : 1;
            $game->save();
        }

        return redirect()->back()->with([
            'messege' => 'Game Balance Minus Status Update Success!!',
            'alert-type' => 'success'
        ]);
    }

    // =========================
    // Clean up old game records
    // =========================
    public function FruitsClear()
    {
        DB::transaction(function () {
            $this->cleanupFortuneGame();
            $this->cleanupGradyGame();
            $this->cleanupTeenPattiGame();
            $this->updateGamePattern();
        });

        return redirect()->back()->with([
            'messege' => 'Cleanup Completed Successfully!!',
            'alert-type' => 'success'
        ]);
    }

    private function cleanupFortuneGame()
    {
        $totalRecords = FortuneTray::count();
        $recordsToDelete = $totalRecords - 50;

        if ($recordsToDelete > 0) {
            $trays = FortuneTray::orderBy('created_at')->limit($recordsToDelete)->get();
            foreach ($trays as $tray) {
                $pots = FortunePots::where('tray_id', $tray->tray_id)->get();
                foreach ($pots as $pot) {
                    FuritsPotsBackup::create([
                        'tray_id' => $pot->tray_id,
                        'user_id' => $pot->user_id,
                        'amount' => $pot->amount,
                        'pot_no' => $pot->pot_no,
                        'status' => $pot->status,
                        'serve_balance' => $pot->serve_balance,
                        'date' => $pot->created_at,
                        'game_name' => 'firust'
                    ]);
                    $pot->delete();
                }
                $tray->delete();
            }
        }
    }

    private function cleanupGradyGame()
    {
        $totalRecords = GradyTray::count();
        $recordsToDelete = $totalRecords - 50;

        if ($recordsToDelete > 0) {
            $trays = GradyTray::orderBy('created_at')->limit($recordsToDelete)->get();
            foreach ($trays as $tray) {
                $pots = GradyPots::where('tray_id', $tray->tray_id)->get();
                foreach ($pots as $pot) {
                    FuritsPotsBackup::create([
                        'tray_id' => $pot->tray_id,
                        'user_id' => $pot->user_id,
                        'amount' => $pot->amount,
                        'pot_no' => $pot->pot_no,
                        'status' => $pot->status,
                        'serve_balance' => $pot->win_balance,
                        'date' => $pot->created_at,
                        'game_name' => 'greedy'
                    ]);
                    $pot->delete();
                }
                $tray->delete();
            }
        }
    }

    private function cleanupTeenPattiGame()
    {
        $totalRecords = TeenPattiTray::count();
        $recordsToDelete = $totalRecords - 50;

        if ($recordsToDelete > 0) {
            $trays = TeenPattiTray::orderBy('created_at')->limit($recordsToDelete)->get();
            foreach ($trays as $tray) {
                $pots = TeenPattiPots::where('tray_id', $tray->tray_id)->get();
                foreach ($pots as $pot) {
                    FuritsPotsBackup::create([
                        'tray_id' => $pot->tray_id,
                        'user_id' => $pot->user_id,
                        'amount' => $pot->amount,
                        'pot_no' => $pot->pot_no,
                        'status' => $pot->status,
                        'serve_balance' => $pot->serve_balance,
                        'date' => $pot->created_at,
                        'game_name' => 'TeenPatti'
                    ]);
                    $pot->delete();
                }
                $tray->delete();
            }
        }
    }

    // =========================
    // Update game pattern
    // =========================
    private function updateGamePattern()
    {
        $initialPots = ['watermelon', 'apple', 'watermelon', 'saven_win', 'apple', 'saven_win'];
        $numPots = 100;
        $result = [];
        $currentStreak = 0;

        for ($i = 0; $i < $numPots; $i++) {
            $randomPot = $initialPots[array_rand($initialPots)];
            if ($i > 0 && $randomPot == $result[$i - 1]) $currentStreak++; else $currentStreak = 0;

            while ($currentStreak >= 3) {
                $randomPot = $initialPots[array_rand($initialPots)];
                if ($i > 0 && $randomPot == $result[$i - 1]) $currentStreak++; else $currentStreak = 0;
            }

            $result[] = $randomPot;
        }

        foreach ($result as $key => $value) {
            $update_pattarn = FruitsGamePattan::find($key + 1);
            if ($update_pattarn) {
                $update_pattarn->pots = $value;
                $update_pattarn->save();
            }
        }
    }

    // =========================
    // Reverse and save game pattern safely
    // =========================
    public function reverseAndSaveData()
    {
        DB::transaction(function () {
            $fruitsGamePattan = FruitsGamePattan::orderBy('id')->get();
            $tempIdOffset = 10000;

            foreach ($fruitsGamePattan as $fruit) {
                $fruit->id = $fruit->id + $tempIdOffset;
                $fruit->save();
            }

            $reversedFruitsGamePattan = FruitsGamePattan::orderBy('id')->get()->reverse();
            $index = 1;
            foreach ($reversedFruitsGamePattan as $fruit) {
                $fruit->id = $index;
                $fruit->save();
                $index++;
            }
        });

        return redirect()->back()->with([
            'messege' => 'Pattern Reverse Successful!!',
            'alert-type' => 'success'
        ]);
    }
}