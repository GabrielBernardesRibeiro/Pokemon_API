<?php
    namespace Pokemon_project\Controllers;
    use Pokemon_project\Models\Pokemon;

    class Type_controller
    {
        public function get($types = null)
        {
            if ($types != null) 
            {
                $type = explode(' ', $types);

                if (count($type) == 2)
                {
                    $pokemon = Pokemon::get_pokemon_by_types($type[0], $type[1]);
                } else{
                    $pokemon = Pokemon::get_pokemon_by_types($type[0]);
                }
                return $pokemon;
            } else {
                throw new \Exception("Tipo inexistente");
            }
        }
    }
?>