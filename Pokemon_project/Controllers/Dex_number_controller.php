<?php
    namespace Pokemon_project\Controllers;
    use Pokemon_project\Models\Pokemon;

    class Dex_number_controller
    {
        public function get($dex_id = null)
        {
            if ($dex_id != null) 
            {
                $pokemon = Pokemon::get_pokemon_by_dex_number($dex_id);
                return array($pokemon);
            } else {
                throw new \Exception("Nenhum numero digitado...");
            }
        }
    }
?>