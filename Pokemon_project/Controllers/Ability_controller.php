<?php
    namespace Pokemon_project\Controllers;
    use Pokemon_project\Models\Pokemon;

    class Ability_controller
    {
        public function get($ability = null)
        {
            if ($ability != null) 
            {
                $pokemon = Pokemon::get_pokemon_by_ability($ability);
                return $pokemon;
            } else {
                throw new \Exception("Nenhuma abilidade digitada...");
            }
        }
    }
?>