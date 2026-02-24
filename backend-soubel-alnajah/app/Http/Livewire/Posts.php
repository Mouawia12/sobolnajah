<?php

namespace App\Http\Livewire;
use App\Models\AgendaScolaire\Publication;
use App\Models\AgendaScolaire\Agenda;
use App\Models\AgendaScolaire\Grade;



use Livewire\Component;

class Posts extends Component
{

    public $id_agenda=0;
    public $id_grade=0;

    Public $counter=2;


    // public $search = '';

    public function load()
    {
        $this->counter++;
     
    }
    
    public function agenda($agenda_id)
    {
        $this->id_agenda=$agenda_id;
        $this->id_grade=0;
        $this->counter=2;

    }

    public function grade($grade_id)
    {
        $this->id_agenda=0;
        $this->id_grade=$grade_id;
        $this->counter=2;


    }
    public function allpub()
    {
        $this->id_agenda=0;
        $this->id_grade=0;
        $this->counter=2;

    }
    
    
    public function render()
    {
        $Grade = Grade::all();
        $Agenda = Agenda::all();

        if ($this->id_agenda==0 & $this->id_grade==0) {

            $Publication = Publication::orderBy('id', 'desc')
            ->take($this->counter)->get();
            return view('livewire.posts',compact('Publication','Grade','Agenda'));

        } else {
            if ($this->id_agenda != 0) {
                $Publication = Publication::orderBy('id', 'desc')->where('agenda_id', $this->id_agenda) 
                ->take($this->counter)->get();

                return view('livewire.posts',compact('Publication','Grade','Agenda'));

            } else {
                $Publication = Publication::orderBy('id', 'desc')->where('grade_id', $this->id_grade) 
                ->take($this->counter)->get();
                return view('livewire.posts',compact('Publication','Grade','Agenda'));
            }
            
        }
        

    }
    
}
