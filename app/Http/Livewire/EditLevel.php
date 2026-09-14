<?php

namespace App\Http\Livewire;

use App\Models\Level;
use Exception;
use Livewire\Component;

class EditLevel extends Component
{
    public $level;
    public $code;
    public $libelle;

    //Etape ou composante est monté

    public function mount()
    {
        $this->code = $this->level->code;
        $this->libelle = $this->level->libelle;
    }

    public function store()
    {
        $level = Level::find($this->level->id);

        $this->validate([
            'code' => 'string|required',
            'libelle' => 'string|required',
        ]);
        try {

            $level->code = $this->code;
            $level->libelle = $this->libelle;

            $level->save();
            return redirect()->route('niveaux')->with('success', 'Niveau mis à jour');
        } catch (Exception $e) {
            //Sera pris en compte si on a un problème


            dd($e);
        }
    }

    

    public function render()
    {

        return view('livewire.edit-level');
    }
}
