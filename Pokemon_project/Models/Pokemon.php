<?php
    namespace Pokemon_project\Models;

    class Pokemon
    {
        private static $pokemon_table = 'pokemon';
        private static $types_table = 'types';
        private static $pokemon_types_table = 'pokemon_types';
        private static $abilities_table = 'abilities';
        private static $pokemon_abilities_table = 'pokemon_abilities';
        private static $images_path = ''; //link do diretório das imagens

        public static function get_pokemon_by_dex_number($dex_number)
        {
            $sql_query = "SELECT ".self::$pokemon_table.".id AS `id`, "
            .self::$pokemon_table.".species_id AS `dex_number`, "
            .self::$pokemon_table.".identifier AS `name` FROM "
            .self::$pokemon_table." WHERE "
            .self::$pokemon_table.".species_id = ?";

            $sql_prepare = \Pokemon_project\MySql::connect()->prepare($sql_query);
			$sql_prepare->execute(array($dex_number));
            
            if ($sql_prepare->rowCount() > 0)
            {
                $pokemon_returned = $sql_prepare->fetch(\PDO::FETCH_ASSOC);
                $abilities = self::get_ability_by_pokemon_number($dex_number);
                $types = self::get_type_by_pokemon_number($dex_number);
                $url_image = self::get_url_image_by_pokemon_number($dex_number);

                return array_merge($pokemon_returned, $abilities, $types, $url_image);
            } else {
                throw new \Exception("Nenhum pokemon com este numero...");
            }
        }

        public static function get_pokemon_by_name($name)
        {
            $sql_query = "SELECT ".self::$pokemon_table.".id AS `id`, "
            .self::$pokemon_table.".species_id AS `dex_number`, "
            .self::$pokemon_table.".identifier AS `name` FROM "
            .self::$pokemon_table." WHERE "
            .self::$pokemon_table.".identifier like :name";
            
            $sql_prepare = \Pokemon_project\MySql::connect()->prepare($sql_query);
			$sql_prepare->bindValue(':name', "%{$name}%");
            $sql_prepare->execute();
            
            if ($sql_prepare->rowCount() > 0)
            {
                $pokemon_returned = $sql_prepare->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($pokemon_returned as $pokemon => $data) {
                    $abilities = self::get_ability_by_pokemon_number($data['id']);
                    $types = self::get_type_by_pokemon_number($data['id']);
                    $url_image = self::get_url_image_by_pokemon_number($data['id']);
                    $pokemon_returned[$pokemon] = array_merge($data, $abilities, $types, $url_image);
                }
                return $pokemon_returned;
            } else {
                throw new \Exception("Nenhum pokemon com este nome...");
            }
        }

        public static function get_pokemon_by_types($type_one, $type_two = null)
        {
            $sql_query = "SELECT ".self::$pokemon_table.".id AS `id`, "
            .self::$pokemon_table.".species_id AS `dex_number`, "
            .self::$pokemon_table.".identifier AS `name` FROM "
            .self::$pokemon_table." INNER JOIN "
            .self::$pokemon_types_table." INNER JOIN "
            .self::$types_table." WHERE "
            .self::$pokemon_types_table.".type_id = ".self::$types_table.".id AND "
            .self::$pokemon_types_table.".pokemon_id = ".self::$pokemon_table.".id AND "
            .self::$types_table.".identifier = ?";

            $sql_prepare = \Pokemon_project\MySql::connect()->prepare($sql_query);
			$sql_prepare->execute(array($type_one));
            
            if ($sql_prepare->rowCount() > 0)
            {
                $pokemon_returned = $sql_prepare->fetchAll(\PDO::FETCH_ASSOC);

                if ($type_two != null)
                {
                    $pokemon_with_two_types = array();
                    foreach ($pokemon_returned as $pokemon => $data) {
                        $secondary_type = self::check_secondary_type($data['id'], $type_two);
                        if ($secondary_type['status'] === 'exist')
                        {
                            $pokemon_with_two_types[$pokemon] = $pokemon_returned[$pokemon];
                            $abilities = self::get_ability_by_pokemon_number($data['id']);
                            $url_image = self::get_url_image_by_pokemon_number($data['id']);
                            $types = self::get_type_by_pokemon_number($data['id']);
                            $pokemon_with_two_types[$pokemon] = array_merge($data, $abilities, $types, $url_image);
                        }
                    }
                    return $pokemon_with_two_types;
                } else {

                    $pokemon_with_first_type = array();
                    foreach ($pokemon_returned as $pokemon => $data) {

                        $pokemon_with_first_type[$pokemon] = $pokemon_returned[$pokemon];
                        $abilities = self::get_ability_by_pokemon_number($data['id']);
                        $url_image = self::get_url_image_by_pokemon_number($data['id']);
                        $types = self::get_type_by_pokemon_number($data['id']);
                        $pokemon_with_first_type[$pokemon] = array_merge($data, $abilities, $types, $url_image);
                    }

                    return $pokemon_with_first_type;
                }

            } else {
                throw new \Exception("Tipo inexistente");
            }
        }

        public static function get_pokemon_by_ability($ability)
        {
            $sql_query = "SELECT ".self::$pokemon_table.".id AS `id`, "
            .self::$pokemon_table.".species_id AS `dex_number`, "
            .self::$pokemon_table.".identifier AS `name` "
            ."FROM ".self::$pokemon_table." INNER JOIN "
            .self::$abilities_table." INNER JOIN "
            .self::$pokemon_abilities_table." WHERE "
            .self::$abilities_table.".id = ".self::$pokemon_abilities_table.".ability_id"
            ." AND ".self::$pokemon_abilities_table.".pokemon_id = ".self::$pokemon_table.".id"
            ." AND ".self::$abilities_table.".identifier = ?";

            $sql_prepare = \Pokemon_project\MySql::connect()->prepare($sql_query);
			$sql_prepare->execute(array($ability));
            
            if ($sql_prepare->rowCount() > 0)
            {
                $pokemon_returned = $sql_prepare->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($pokemon_returned as $pokemon => $data) {
                    $abilities = self::get_ability_by_pokemon_number($data['id']);
                    $types = self::get_type_by_pokemon_number($data['id']);
                    $url_image = self::get_url_image_by_pokemon_number($data['id']);
                    $pokemon_returned[$pokemon] = array_merge($data, $abilities, $types, $url_image);
                }
                return $pokemon_returned;
            } else {
                throw new \Exception("Nenhum pokemon com esta habilidade...");
            }
        }

        public static function get_ability_by_pokemon_number($pokemon_id)
        {
            $sql_query = "SELECT ".self::$abilities_table.".identifier AS `ability`, "
            .self::$pokemon_abilities_table.".is_hidden AS `hiden`"
            ."FROM ".self::$abilities_table." INNER JOIN "
            .self::$pokemon_abilities_table." WHERE "
            .self::$pokemon_abilities_table.".ability_id = ".self::$abilities_table.".id"
            ." AND ".self::$pokemon_abilities_table.".pokemon_id = ?";

            $sql_prepare = \Pokemon_project\MySql::connect()->prepare($sql_query);
			$sql_prepare->execute(array($pokemon_id));
            $abilities_returned = $sql_prepare->fetchAll(\PDO::FETCH_ASSOC);

            return array('abilities' => $abilities_returned);
        }

        public static function get_type_by_pokemon_number($pokemon_id)
        {
            $sql_query = "SELECT ".self::$types_table.".identifier AS `type` "
            ."FROM ".self::$types_table." INNER JOIN "
            .self::$pokemon_types_table." WHERE "
            .self::$pokemon_types_table.".type_id = ".self::$types_table.".id"
            ." AND ".self::$pokemon_types_table.".pokemon_id = ? order by "
            .self::$pokemon_types_table.".slot";

            $sql_prepare = \Pokemon_project\MySql::connect()->prepare($sql_query);
			$sql_prepare->execute(array($pokemon_id));
            $types_returned = $sql_prepare->fetchAll(\PDO::FETCH_ASSOC);

            $type_and_url = array();
            foreach ($types_returned as $type) {
                $current_type = array();
                $current_type['type'] = $type['type'];
                $current_type['url'] = self::get_url_img_of_type($type['type']);
                array_push($type_and_url, $current_type);
            }

            return array('types' => $type_and_url);
        }

        public static function get_url_image_by_pokemon_number($pokemon_id)
        {
            $fixed_number = '';
            if ( strlen( (string) $pokemon_id ) == 1)
            {
                $fixed_number .= '00'.(string) $pokemon_id;
            } else if ( strlen( (string) $pokemon_id ) == 2)
            {
                $fixed_number .= '0'.(string) $pokemon_id;
            } else
            {
                $fixed_number .= (string) $pokemon_id;
            }
            
            return array('url' => self::$images_path.$fixed_number.'.png');
        }

        public static function get_url_img_of_type($type)
        {   
            return self::$images_path.$type.'.png';
        }

        public static function check_secondary_type($pokemon_id, $first_type)
        {
            $sql_query = "SELECT ".self::$types_table.".identifier AS `type` "
            ."FROM ".self::$pokemon_types_table." INNER JOIN "
            .self::$types_table." WHERE "
            .self::$types_table.".id = ".self::$pokemon_types_table.".type_id"
            ." AND ".self::$types_table.".identifier = ? AND ".self::$pokemon_types_table.".pokemon_id = ?";

            $sql_prepare = \Pokemon_project\MySql::connect()->prepare($sql_query);
			$sql_prepare->execute(array($first_type, $pokemon_id));

            if ($sql_prepare->rowCount() > 0)
            {
                $abilities_returned = $sql_prepare->fetch(\PDO::FETCH_ASSOC);

                return array('status' => 'exist', 'type' => $abilities_returned);
            } else
            {
                return array('status' => 'dont_exist');
            }
        }
    }
    

?>