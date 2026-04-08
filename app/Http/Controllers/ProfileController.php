<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Assinatura;
use App\Models\EmailAssinatura;
use App\Models\Layout;
use App\Models\TabelaOrigens;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $cidades = TabelaOrigens::orderBy('uf')->get()->groupBy('uf');

        $assinaturas = Assinatura::find(auth()->user()->assinaturas()->first()->id);
        $folder = "";
        if($assinaturas->folder) {
            $folder = $assinaturas->folder;
        }
        $layouts = Layout::all();



        return view('profile.edit', [
            'user' => $request->user(),
            'cidades' => $cidades,
            'folder' => $folder,
            'layouts' => $layouts,
        ]);
    }

    public function updateAvatarUser(Request $request)
    {
        $request->validate([
            'imagem' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $id = $request->id;
            $user = \App\Models\User::find($id);
            $imagemAntiga = $user->imagem;

            if ($request->hasFile('imagem')) {
                if (!empty($imagemAntiga) && Storage::disk('public')->exists($imagemAntiga)) {
                    Storage::disk('public')->delete($imagemAntiga);
                }
                $user->imagem = $request->file('imagem')->store('users', 'public');
            }
            $user->save();
            $assinatura = Assinatura::find(auth()->user()->assinaturas()->first()->id);
            $users = User::whereIn(
                'id',
                EmailAssinatura::where('assinatura_id', $assinatura->id)
                    ->pluck('user_id')
            )
                //->where('status', 1)
                ->orderBy("id","desc")
                ->get();


            $html = view('partials.user-table', compact('users'))->render();
            return response()->json(['message' => 'Imagem atualizada com sucesso!','html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar a imagem: ' . $e->getMessage()], 500);
        }
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'imagem' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $user = auth()->user();
            $imagemAntiga = $user->imagem;

            if ($request->hasFile('imagem')) {
                if (!empty($imagemAntiga) && Storage::disk('public')->exists($imagemAntiga)) {
                    Storage::disk('public')->delete($imagemAntiga);
                }
                $user->imagem = $request->file('imagem')->store('users', 'public');
            }

            $user->save();

            return response()->json(['message' => 'Imagem atualizada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar a imagem: ' . $e->getMessage()], 500);
        }
    }





    public function regiao(Request $request)
    {
        $user = Auth::user(); // ou auth()->user()

        // Valida se veio um UF válido ou vazio
        $uf = $request->input('regiao');

        // Se for vazio, zera (null)
        $user->uf_preferencia = $uf ?: null;

        $user->save();

        return back()->with('status', 'Região atualizada com sucesso!');
    }



    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request)
    {
        try {
            $user         = $request->user();
            $imagemAntiga = $user->imagem;
            $emailAntigo  = $user->email;

            $user->fill($request->validated());

            if ($request->hasFile('imagem')) {
                if (!empty($imagemAntiga) && Storage::disk('public')->exists($imagemAntiga)) {
                    Storage::disk('public')->delete($imagemAntiga);
                }
                $user->imagem = $request->file('imagem')->store('users', 'public');
            }

            // Se o email mudou, sincroniza emails_assinatura e reseta verificação
            if ($emailAntigo !== $user->email) {
                EmailAssinatura::where('user_id', $user->id)
                    ->update(['email' => $user->email]);
                $user->email_verified_at = null;
            }

            $user->save();

            return redirect()->back()->with('success', 'Perfil atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }
    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
