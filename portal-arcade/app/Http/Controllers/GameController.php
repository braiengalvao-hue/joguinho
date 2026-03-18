<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller {
    public function index() {
        return view('friv');
    }

    public function reward(Request $request) {
        $nickname = $request->input('nickname', 'Jogador1');
        $xp = $request->input('xp', 0);
        $moedas = $request->input('moedas', 0);

        $jogador = DB::table('jogadores')->where('nickname', $nickname)->first();

        if ($jogador) {
            DB::table('jogadores')->where('id', $jogador->id)->update([
                'xp' => $jogador->xp + $xp,
                'moedas' => $jogador->moedas + $moedas,
                'updated_at' => now()
            ]);
        } else {
            DB::table('jogadores')->insert([
                'nickname' => $nickname,
                'xp' => $xp,
                'moedas' => $moedas,
                'conquistas' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        $current = DB::table('jogadores')->where('nickname', $nickname)->first();
        return response()->json(['success' => true, 'xp' => $current->xp, 'moedas' => $current->moedas]);
    }
}
