<?php
    namespace Pokemon_project\Controllers;
    use Pokemon_project\Models\Pokemon;

    class Name_controller
    {
        public function get($name = null)
        {
            if ($name != null) 
            {
                $pokemon = Pokemon::get_pokemon_by_name($name);
                return $pokemon;
            } else {
                throw new \Exception("Nenhum numero digitado...");
            }
        }
    }
?>